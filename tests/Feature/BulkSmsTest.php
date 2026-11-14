<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\SmsCampaign;
use App\Models\SmsLog;
use App\Models\User;
use App\Services\SalesService;
use App\Support\SmsText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BulkSmsTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    private function customer(string $name, string $phone): Customer
    {
        return Customer::create(['name' => $name, 'phone' => $phone]);
    }

    private function order(Customer $c, array $o = []): Delivery
    {
        return Delivery::create($o + [
            'reference' => Delivery::newReference(), 'customer_id' => $c->id, 'location' => 'Osu', 'package_description' => 'Dress',
            'delivery_date' => today(), 'total_amount' => 100, 'items_sent' => 1, 'status' => 'delivered', 'amount_paid' => 100,
        ]);
    }

    private function fakeSms(): void
    {
        config(['services.speso_sms.api_key' => 'k']);
        Http::fake(['*' => Http::response(['success' => true])]);
    }

    private function preview(User $m, array $data)
    {
        return $this->actingAs($m)->postJson(route('manager.bulk.preview'), $data);
    }

    // ---------------------------------------------------------------- audiences

    public function test_group_audiences_pick_the_right_people_and_skip_walk_ins_and_bad_numbers(): void
    {
        $m = $this->manager();
        $paid = $this->customer('Ama Paid', '0244000111');
        $owes = $this->customer('Kofi Owes', '0244000222');
        $open = $this->customer('Esi Open', '0244000333');
        $this->customer('Walk In', SalesService::WALK_IN_PHONE);
        $this->customer('Bad Number', '12345');
        $this->order($paid);
        $this->order($owes, ['amount_paid' => 20]);                                   // 80 still owed
        $this->order($open, ['status' => 'assigned', 'amount_paid' => 100]);
        User::factory()->create(['role' => 'rider', 'name' => 'Joe', 'phone' => '0200000001']);
        User::factory()->create(['role' => 'rider', 'name' => 'Off Duty', 'phone' => '0200000002', 'is_active' => false]);
        User::factory()->create(['role' => 'rider', 'name' => 'No Phone', 'phone' => null]);

        $all = $this->preview($m, ['audience' => 'customers_all'])->assertOk()->json();
        $this->assertSame(3, $all['count']);                                          // walk-in excluded
        $this->assertSame(1, $all['skipped_count']);                                  // the bad number is reported

        $this->assertSame(1, $this->preview($m, ['audience' => 'customers_owing'])->json('count'));
        $this->assertSame(['Kofi Owes'], $this->preview($m, ['audience' => 'customers_owing'])->json('sample'));
        $this->assertSame(['Esi Open'], $this->preview($m, ['audience' => 'customers_open'])->json('sample'));
        $this->assertSame(3, $this->preview($m, ['audience' => 'customers_recent'])->json('count'));

        $riders = $this->preview($m, ['audience' => 'riders_all'])->json();
        $this->assertSame(1, $riders['count']);                                       // only the active rider with a phone
        $this->assertSame(1, $riders['skipped_count']);                               // the active rider with no phone
    }

    public function test_selected_people_and_typed_numbers_are_deduplicated(): void
    {
        $m = $this->manager();
        $ama = $this->customer('Ama', '0244000111');
        $joe = User::factory()->create(['role' => 'rider', 'name' => 'Joe', 'phone' => '0244000111']);   // same number as Ama

        $this->assertSame(1, $this->preview($m, ['audience' => 'selected', 'customer_ids' => [$ama->id], 'rider_ids' => [$joe->id]])->json('count'));

        // only riders ticked (no customers), and only customers ticked (no riders): either list can be empty
        $this->assertSame(1, $this->preview($m, ['audience' => 'selected', 'rider_ids' => [$joe->id]])->assertOk()->json('count'));
        $this->assertSame(1, $this->preview($m, ['audience' => 'selected', 'customer_ids' => [$ama->id]])->assertOk()->json('count'));
        $this->assertSame(0, $this->preview($m, ['audience' => 'selected'])->assertOk()->json('count'));

        $typed = $this->preview($m, ['audience' => 'numbers', 'numbers' => "0241112222\nKofi +233 55 111 2233, 0241112222\nnot a number\n123"])->json();
        $this->assertSame(2, $typed['count']);                                        // 0241112222 once, Kofi
        $this->assertSame(2, $typed['skipped_count']);                                // "not a number" and "123"
        $this->assertContains('Kofi', $typed['sample']);
    }

    // ---------------------------------------------------------------- sending

    public function test_sending_to_a_group_personalises_logs_and_summarises(): void
    {
        $this->fakeSms();
        $m = $this->manager();
        $this->customer('Ama Mensah', '0244000111');
        $this->customer('Kofi', '0244000222');
        $this->customer('Walk In', SalesService::WALK_IN_PHONE);

        $res = $this->actingAs($m)->post(route('manager.bulk.send'), ['audience' => 'customers_all', 'message' => 'Hello {name}, new styles are in!']);
        $c = SmsCampaign::first();
        $res->assertRedirect(route('manager.bulk.show', $c))->assertSessionHas('success');

        $this->assertSame([2, 2, 0, 'done'], [$c->recipients, $c->sent, $c->failed, $c->status]);
        $this->assertSame(2, SmsLog::where('campaign_id', $c->id)->where('purpose', 'bulk')->count());
        Http::assertSent(fn ($r) => $r['phone'] === '0244000111' && $r['message'] === 'Hello Ama, new styles are in!');   // first name only
        Http::assertSent(fn ($r) => $r['phone'] === '0244000222' && $r['message'] === 'Hello Kofi, new styles are in!');
        Http::assertNotSent(fn ($r) => $r['phone'] === SalesService::WALK_IN_PHONE);

        $this->actingAs($m)->get(route('manager.bulk.show', $c))->assertOk()->assertSee('All customers')->assertSee('0244000111')->assertSee('Accepted');
        $this->actingAs($m)->get(route('manager.bulk'))->assertOk()->assertSee('All customers')->assertSee('Hello {name}');
    }

    public function test_riders_and_individuals_and_typed_numbers_can_be_texted(): void
    {
        $this->fakeSms();
        $m = $this->manager();
        User::factory()->create(['role' => 'rider', 'name' => 'Joe Rider', 'phone' => '0200000001']);
        $ama = $this->customer('Ama', '0244000111');
        $this->customer('Not Picked', '0244000999');

        $this->actingAs($m)->post(route('manager.bulk.send'), ['audience' => 'riders_all', 'message' => 'Meeting at 8am, {name}.']);
        Http::assertSent(fn ($r) => $r['phone'] === '0200000001' && $r['message'] === 'Meeting at 8am, Joe.');

        $this->actingAs($m)->post(route('manager.bulk.send'), ['audience' => 'selected', 'customer_ids' => [$ama->id], 'message' => 'Just for you.']);
        Http::assertSent(fn ($r) => $r['phone'] === '0244000111' && $r['message'] === 'Just for you.');
        Http::assertNotSent(fn ($r) => $r['phone'] === '0244000999');

        $this->actingAs($m)->post(route('manager.bulk.send'), ['audience' => 'numbers', 'numbers' => '0551112233', 'message' => 'Hi {name}']);
        Http::assertSent(fn ($r) => $r['phone'] === '0551112233' && $r['message'] === 'Hi there');                      // no name known
        $this->assertSame(3, SmsCampaign::count());
    }

    public function test_sending_stops_after_repeated_failures_and_reports_it(): void
    {
        config(['services.speso_sms.api_key' => 'k']);
        Http::fake(['*' => Http::response(['success' => false, 'message' => 'Selected Sender ID has not been approved'], 422)]);
        $m = $this->manager();
        foreach (range(1, 12) as $i) {
            $this->customer("C{$i}", '02440001' . str_pad((string) $i, 2, '0', STR_PAD_LEFT));
        }

        $this->actingAs($m)->post(route('manager.bulk.send'), ['audience' => 'customers_all', 'message' => 'Hello there'])->assertSessionHas('error');
        $c = SmsCampaign::first();
        $this->assertSame('aborted', $c->status);
        $this->assertSame(5, $c->failed);                         // gave up after 5 in a row, not 12
        $this->assertSame(7, $c->not_sent);
        $this->assertStringContainsString('Sender ID has not been approved', $c->note);
        Http::assertSentCount(5);
    }

    public function test_nobody_to_send_to_and_too_many_recipients_are_refused_before_anything_is_sent(): void
    {
        $this->fakeSms();
        $m = $this->manager();

        $this->actingAs($m)->post(route('manager.bulk.send'), ['audience' => 'customers_owing', 'message' => 'Please pay'])->assertSessionHas('error');
        $this->assertSame(0, SmsCampaign::count());

        $numbers = collect(range(1, 501))->map(fn ($i) => '0244' . str_pad((string) $i, 6, '0', STR_PAD_LEFT))->implode("\n");
        $this->actingAs($m)->post(route('manager.bulk.send'), ['audience' => 'numbers', 'numbers' => $numbers, 'message' => 'Hello'])->assertSessionHas('error');
        $this->assertTrue($this->preview($m, ['audience' => 'numbers', 'numbers' => $numbers])->json('over_limit'));
        Http::assertNothingSent();
        $this->assertSame(0, SmsCampaign::count());
    }

    public function test_live_recipient_counts_do_not_use_up_the_allowance_for_sending(): void
    {
        $this->fakeSms();
        $m = $this->manager();
        $this->customer('Ama', '0244000111');

        // the screen re-counts on every change; a manager editing numbers easily does this dozens of times
        for ($i = 0; $i < 30; $i++) {
            $this->preview($m, ['audience' => 'customers_all'])->assertOk();
        }
        $this->actingAs($m)->post(route('manager.bulk.send'), ['audience' => 'customers_all', 'message' => 'Hello {name}'])->assertRedirect();   // not 429
        $this->assertSame(1, SmsCampaign::count());
    }

    public function test_different_pages_do_not_share_one_request_counter(): void
    {
        // contact form allows 5 a minute; browsing the tracking page many times must not eat into it
        for ($i = 0; $i < 12; $i++) {
            $this->get('/track')->assertOk();
        }
        $this->post('/contact', ['type' => 'contact', 'name' => 'Ama', 'phone' => '0244000111', 'message' => 'Hello there'])->assertRedirect('/contact');
        $this->post('/checkout', [])->assertStatus(302);        // the checkout limit (6) is also separate from the 12 page views above
    }

    public function test_message_and_audience_are_validated(): void
    {
        $m = $this->manager();
        $this->actingAs($m)->post(route('manager.bulk.send'), ['audience' => 'customers_all', 'message' => ''])->assertSessionHasErrors('message');
        $this->actingAs($m)->post(route('manager.bulk.send'), ['audience' => 'everyone-on-earth', 'message' => 'Hello'])->assertSessionHasErrors('audience');
        $this->actingAs($m)->post(route('manager.bulk.send'), ['audience' => 'customers_all', 'message' => str_repeat('x', 641)])->assertSessionHasErrors('message');
    }

    public function test_only_managers_can_use_bulk_sms(): void
    {
        $rider = User::factory()->create(['role' => 'rider']);
        $this->actingAs($rider)->get(route('manager.bulk'))->assertForbidden();
        $this->actingAs($rider)->post(route('manager.bulk.send'), ['audience' => 'customers_all', 'message' => 'Hello'])->assertForbidden();
        $this->actingAs($rider)->postJson(route('manager.bulk.preview'), ['audience' => 'customers_all'])->assertForbidden();

        $this->app['auth']->forgetGuards();
        $this->get(route('manager.bulk'))->assertRedirect('/login');
    }

    // ---------------------------------------------------------------- helpers

    public function test_sms_part_counting_and_name_filling(): void
    {
        $this->assertSame(['length' => 5, 'unicode' => false, 'parts' => 1], SmsText::measure('Hello'));
        $this->assertSame(1, SmsText::measure(str_repeat('a', 160))['parts']);
        $this->assertSame(2, SmsText::measure(str_repeat('a', 161))['parts']);
        $this->assertSame(['length' => 9, 'unicode' => true, 'parts' => 1], SmsText::measure('GH₵ 50.00'));   // the ₵ forces 70-character parts
        $this->assertSame(2, SmsText::measure(str_repeat('a', 70) . '₵')['parts']);

        $this->assertSame('Hi Ama!', SmsText::personalise('Hi {name}!', 'Ama Mensah'));
        $this->assertSame('Hi there!', SmsText::personalise('Hi {name}!', null));
        $this->assertSame('Hi there!', SmsText::personalise('Hi {name}!', '  '));
    }

    public function test_menu_has_the_bulk_sms_link(): void
    {
        $this->actingAs($this->manager())->get(route('manager.dashboard'))->assertSee('Bulk SMS');
    }
}
