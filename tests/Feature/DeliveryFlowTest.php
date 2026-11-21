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

    public function test_full_flow_without_a_code_then_clearance(): void
    {
        config(['services.speso_sms.api_key' => 'test-key']);
        \App\Models\Setting::set('phone', '0200680701 / 0203264474');
        Http::fake(['*' => Http::response(['success' => true])]);
        $rider = $this->rider();
        $d = $this->delivery();

        // rider picks it and sets off: no code is texted to the customer any more
        $this->actingAs($rider)->post(route('rider.claim', $d))->assertRedirect();
        $this->assertSame('assigned', $d->fresh()->status);
        $this->actingAs($rider)->post(route('rider.start', $d))->assertSessionHas('success');
        $this->assertSame('out_for_delivery', $d->fresh()->status);
        $this->assertFalse(SmsLog::where('message', 'like', '%code%')->exists());

        // the rider simply taps Delivered: no code to type
        $this->actingAs($rider)->get(route('rider.show', $d))->assertOk()->assertSee('Delivered')->assertDontSee('4-digit')->assertDontSee('Resend');
        $this->actingAs($rider)->post(route('rider.deliver', $d), ['items_kept' => 1, 'cash' => 300])->assertSessionHasNoErrors();

        $d->refresh();
        $this->assertSame('delivered', $d->status);
        $this->assertEquals(300, $d->amount_paid);
        $this->assertSame(1, $d->items_kept);
        $this->assertTrue($d->events()->where('type', 'delivered')->exists());

        // the customer gets one short text, with the numbers to call if it did not arrive
        $sms = SmsLog::where('delivery_id', $d->id)->where('purpose', 'delivered')->get();
        $this->assertCount(1, $sms);
        $this->assertSame("Drezwel: Your order {$d->reference} has been delivered. Not received? Call 0200680701 / 0203264474.", $sms->first()->message);
        $this->assertLessThanOrEqual(160, mb_strlen($sms->first()->message));                        // a single SMS

        // rider now holds 300 cash and 1 item
        $c = $rider->custody();
        $this->assertEquals(300, $c['cash_held']);
        $this->assertSame(1, $c['items_pending']);

        $this->actingAs($this->manager())->post(route('manager.remittances.clear', $rider))->assertSessionHas('success');
        $this->assertTrue($rider->custody()['cleared']);
    }

    public function test_a_rider_can_only_deliver_their_own_open_deliveries_and_only_once(): void
    {
        Http::fake();
        $rider = $this->rider();
        $other = $this->rider('Other');
        $d = $this->delivery(['rider_id' => $rider->id, 'status' => 'assigned']);

        $this->actingAs($other)->post(route('rider.deliver', $d))->assertForbidden();                 // not their delivery
        $this->assertSame('assigned', $d->fresh()->status);

        $this->actingAs($rider)->post(route('rider.deliver', $d), ['cash' => 300])->assertSessionHasNoErrors();   // straight from assigned is fine
        $this->assertSame('delivered', $d->fresh()->status);

        $this->actingAs($rider)->post(route('rider.deliver', $d), ['cash' => 300])->assertSessionHasErrors('status');   // already delivered
        $this->assertSame(1, SmsLog::where('delivery_id', $d->id)->where('purpose', 'delivered')->count());   // and the customer is texted once
        $this->assertEquals(300, $d->fresh()->amount_paid);
    }

    public function test_manager_can_mark_a_delivery_delivered_without_a_reason(): void
    {
        Http::fake();
        $rider = $this->rider();
        $d = $this->delivery(['rider_id' => $rider->id, 'status' => 'out_for_delivery']);

        $page = $this->actingAs($this->manager())->get(route('manager.deliveries.show', $d))->assertOk()->assertSee('Mark as delivered')->assertDontSee('Delivery code')->assertDontSee('Send a new code');
        $this->actingAs($this->manager())->post(route('manager.deliveries.confirm', $d), [])->assertSessionHasNoErrors();
        $this->assertSame('delivered', $d->fresh()->status);
        $this->assertTrue($d->fresh()->confirmed_by_manager);
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
