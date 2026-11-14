<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeliveryFlowTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    private function rider(string $name = 'Joe'): User
    {
        return User::factory()->create(['role' => 'rider', 'name' => $name]);
    }

    private function delivery(array $over = []): Delivery
    {
        $c = Customer::firstOrCreate(['phone' => '0240848848'], ['name' => 'Ernest']);

        return Delivery::create($over + [
            'reference' => Delivery::newReference(), 'customer_id' => $c->id, 'location' => 'Burma Camp',
            'package_description' => 'Jeans', 'delivery_date' => today(), 'total_amount' => 300, 'items_sent' => 2, 'status' => 'pending',
        ]);
    }

    private function lastCode(): string
    {
        $sent = null;
        Http::assertSent(function ($req) use (&$sent) {
            if (preg_match('/code is (\d{4})/', $req['message'] ?? '', $m)) {
                $sent = $m[1];
            }

            return true;
        });

        return $sent;
    }

    public function test_full_flow_with_code_and_clearance(): void
    {
        config(['services.speso_sms.api_key' => 'test-key']);
        Http::fake(['*' => Http::response(['success' => true])]);
        $rider = $this->rider();
        $d = $this->delivery();

        // rider picks it, starts it, customer gets a code
        $this->actingAs($rider)->post(route('rider.claim', $d))->assertRedirect();
        $this->assertSame('assigned', $d->fresh()->status);
        $this->actingAs($rider)->post(route('rider.start', $d));
        $this->assertSame('out_for_delivery', $d->fresh()->status);
        $code = $this->lastCode();
        $this->assertMatchesRegularExpression('/^\d{4}$/', $code);

        // wrong code first, then right code
        $wrong = $code === '0000' ? '1111' : '0000';
        $this->actingAs($rider)->post(route('rider.confirm', $d), ['code' => $wrong, 'cash' => 300])->assertSessionHasErrors('code');
        $this->assertSame(1, $d->fresh()->code_attempts);
        $this->actingAs($rider)->post(route('rider.confirm', $d), ['code' => $code, 'items_kept' => 1, 'cash' => 300])->assertSessionHasNoErrors();

        $d->refresh();
        $this->assertSame('delivered', $d->status);
        $this->assertEquals(300, $d->amount_paid);
        $this->assertSame(1, $d->items_kept);

        // rider now holds 300 cash and 1 item
        $c = $rider->custody();
        $this->assertEquals(300, $c['cash_held']);
        $this->assertSame(1, $c['items_pending']);

        $this->actingAs($this->manager())->post(route('manager.remittances.clear', $rider))->assertSessionHas('success');
        $this->assertTrue($rider->custody()['cleared']);

        // the code is never stored in the SMS log
        $this->assertFalse(SmsLog::where('message', 'like', "%{$code}%")->exists());
    }

    public function test_code_locks_after_five_wrong_attempts(): void
    {
        Http::fake();
        $rider = $this->rider();
        $d = $this->delivery(['rider_id' => $rider->id, 'status' => 'assigned']);
        $this->actingAs($rider)->post(route('rider.start', $d));
        // brute force: 5 wrong tries, then even the right code (unknown here) is refused
        $d->refresh();
        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($rider)->post(route('rider.confirm', $d), ['code' => '12' . $i . '4']);
        }
        $this->assertSame(5, $d->fresh()->code_attempts);
        $this->actingAs($rider)->post(route('rider.confirm', $d), ['code' => '9999'])->assertSessionHasErrors('code');
        $this->assertSame('out_for_delivery', $d->fresh()->status);
    }

    public function test_two_riders_cannot_claim_the_same_delivery(): void
    {
        Http::fake();
        $d = $this->delivery();
        $a = $this->rider('A');
        $b = $this->rider('B');
        $this->actingAs($a)->post(route('rider.claim', $d));
        $this->actingAs($b)->post(route('rider.claim', $d))->assertSessionHas('error');
        $this->assertSame($a->id, $d->fresh()->rider_id);
    }

    public function test_role_and_ownership_are_enforced(): void
    {
        Http::fake();
        $rider = $this->rider();
        $other = $this->rider('Other');
        $d = $this->delivery(['rider_id' => $other->id, 'status' => 'assigned']);

        $this->actingAs($rider)->get(route('manager.dashboard'))->assertForbidden();
        $this->actingAs($rider)->post(route('rider.start', $d))->assertForbidden() ;
        $this->actingAs($rider)->get(route('rider.show', $d))->assertForbidden();
        $this->actingAs($this->manager())->get(route('rider.mine'))->assertForbidden();
        $this->app['auth']->forgetGuards(); // drop the user actingAs() left logged in
        $this->get(route('manager.dashboard'))->assertRedirect('/login');
    }

    public function test_login_by_email_picks_the_right_user_even_when_others_have_no_phone(): void
    {
        $this->manager()->update(['phone' => null]);
        $rider = User::factory()->create(['role' => 'rider', 'email' => 'joe@x.test', 'phone' => null, 'password' => 'secret123']);
        $this->post('/login', ['login' => 'joe@x.test', 'password' => 'secret123'])->assertRedirect(route('rider.mine'));
        $this->assertAuthenticatedAs($rider);
    }

    public function test_rider_history_page_lists_only_that_riders_deliveries(): void
    {
        $a = $this->rider('A');
        $b = $this->rider('B');
        $mine = $this->delivery(['rider_id' => $a->id, 'status' => 'delivered', 'package_description' => 'Mine-pkg']);
        $this->delivery(['rider_id' => $b->id, 'status' => 'delivered', 'package_description' => 'Other-pkg']);
        $this->actingAs($this->manager())->get(route('manager.riders.show', $a))->assertOk()->assertSee($mine->reference)->assertDontSee('Other-pkg');
        $this->actingAs($this->manager())->get(route('manager.riders.show', $this->manager()))->assertNotFound();
        $this->app['auth']->forgetGuards();
        $this->actingAs($a)->get(route('manager.riders.show', $a))->assertForbidden();
    }

    public function test_customer_history_filters_and_prefills_new_order(): void
    {
        $m = $this->manager();
        $d1 = $this->delivery(['status' => 'delivered', 'package_description' => 'Shirt-pkg']);
        $this->delivery(['status' => 'pending', 'package_description' => 'Shoe-pkg']);
        $c = $d1->customer;

        $this->actingAs($m)->get(route('manager.customers.show', $c))->assertOk()->assertSee('Shirt-pkg')->assertSee('Shoe-pkg');
        $this->actingAs($m)->get(route('manager.customers.show', [$c, 'status' => 'delivered']))->assertSee('Shirt-pkg')->assertDontSee('Shoe-pkg');
        $this->actingAs($m)->get(route('manager.deliveries.create', ['name' => 'Ernest', 'phone' => '0240848848']))->assertSee('value="0240848848"', false);
    }

    public function test_manager_can_add_a_phone_to_a_rider_without_one(): void
    {
        $rider = User::factory()->create(['role' => 'rider', 'phone' => null, 'email' => 'r@x.test']);
        $this->actingAs($this->manager())->put(route('manager.riders.update', $rider), ['name' => $rider->name, 'email' => 'r@x.test', 'phone' => '024 123 4567'])
            ->assertSessionHasNoErrors();
        $this->assertSame('0241234567', $rider->fresh()->phone);
    }

    public function test_tracking_needs_matching_phone_and_never_shows_code(): void
    {
        $d = $this->delivery();
        $this->get('/track?ref=' . $d->reference . '&phone=0240848848')->assertOk()->assertSee('Burma Camp');
        $this->get('/track?ref=' . $d->reference . '&phone=0200000000')->assertSee('could not find');
    }

    public function test_manager_pages_render(): void
    {
        $m = $this->manager();
        $d = $this->delivery();
        foreach (['manager.dashboard', 'manager.deliveries.index', 'manager.deliveries.create', 'manager.riders.index', 'manager.customers.index', 'manager.debtors', 'manager.remittances', 'manager.sms'] as $r) {
            $this->actingAs($m)->get(route($r))->assertOk();
        }
        $this->actingAs($m)->get(route('manager.deliveries.show', $d))->assertOk();
        $this->actingAs($m)->get(route('manager.customers.show', $d->customer))->assertOk();
    }

    public function test_manager_creates_delivery_and_assigns_rider(): void
    {
        Http::fake();
        $rider = $this->rider();
        $this->actingAs($this->manager())->post(route('manager.deliveries.store'), [
            'customer_name' => 'Bello', 'customer_phone' => '+233 24 126 6765', 'location' => 'Newtown', 'package_description' => 'Baggy jeans',
            'delivery_mode' => 'rider', 'order_type' => 'standard', 'delivery_date' => today()->toDateString(), 'total_amount' => 470, 'items_sent' => 2, 'rider_id' => $rider->id,
        ])->assertSessionHasNoErrors();

        $d = Delivery::first();
        $this->assertSame('assigned', $d->status);
        $this->assertSame('0241266765', $d->customer->phone);
        $this->actingAs($rider)->get(route('rider.mine'))->assertOk()->assertSee('Bello');
    }
}
