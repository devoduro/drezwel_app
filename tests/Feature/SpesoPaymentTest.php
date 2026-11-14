<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SpesoPaymentTest extends TestCase
{
    use RefreshDatabase;

    private string $spesoStatus = 'processing';
    private ?float $spesoAmount = null;
    private bool $spesoDown = false;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.speso.api_key' => 'sk_test_x', 'services.speso_sms.api_key' => 'sk_test_x',
            'services.speso.webhook_secret' => 'whsec_test', 'services.speso.callback_url' => 'https://shop.test/webhooks/speso',
        ]);
        Setting::set('store_open', '1');
        Setting::set('default_delivery_fee', '20');

        Http::fake(function (Request $req) {
            $url = $req->url();
            if (str_contains($url, '/collections')) {
                if ($this->spesoDown) {
                    return Http::response(['success' => false, 'message' => 'Service unavailable'], 503);
                }
                if (str_contains($url, '/collections/')) {
                    return Http::response(['success' => true, 'status' => $this->spesoStatus, 'amount' => $this->spesoAmount ?? 270.0, 'speso_reference' => 'SP-REF-1', 'network' => 'MTN']);
                }

                return Http::response(['success' => true, 'status' => 'processing', 'speso_reference' => 'SP-REF-1']);
            }

            return Http::response(['success' => true]);
        });
    }

    private function variant(): ProductVariant
    {
        $p = Product::create(['name' => 'Baggy jeans', 'slug' => 'baggy-jeans', 'cost_price' => 100, 'selling_price' => 250, 'reorder_level' => 1, 'show_in_store' => true, 'stock_qty' => 5]);

        return $p->variants()->create(['size' => '32', 'color' => 'Blue', 'stock_qty' => 5]);
    }

    private function checkout(ProductVariant $v, array $extra = [])
    {
        return $this->withSession(['cart' => [$v->id => 1]])->post(route('shop.checkout.place'), array_merge([
            'name' => 'Ama Mensah', 'phone' => '0241234567', 'location' => 'Osu, Accra', 'pay_method' => 'speso', 'momo_number' => '0551234567', 'network' => 'MTN',
        ], $extra));
    }

    private function payNow(ProductVariant $v): Delivery
    {
        $this->checkout($v)->assertRedirect();

        return Delivery::firstOrFail();
    }

    private function webhook(array $payload, ?int $at = null, string $secret = 'whsec_test')
    {
        $body = json_encode($payload);
        $at ??= time();
        $sig = "t={$at},v1=" . hash_hmac('sha256', $at . '.' . $body, $secret);

        return $this->call('POST', '/webhooks/speso', [], [], [], ['HTTP_X_SPESO_SIGNATURE' => $sig, 'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], $body);
    }

    // ------------------------------------------------------------ placing the order

    public function test_paying_now_sends_a_momo_prompt_and_holds_the_order_until_it_is_paid(): void
    {
        $v = $this->variant();
        $res = $this->checkout($v);
        $d = Delivery::firstOrFail();

        $res->assertRedirect(URL::temporarySignedRoute('shop.order', now()->addDays(7), ['reference' => $d->reference]));
        $this->assertSame('speso', $d->payment_method);
        $this->assertSame('awaiting', $d->payment_status);
        $this->assertSame($d->reference . '-1', $d->payment_order_id);
        $this->assertSame(4, $v->fresh()->stock_qty);                  // stock is held while they pay

        Http::assertSent(fn (Request $r) => $r->method() === 'POST' && str_ends_with($r->url(), '/collections')
            && $r['order_id'] === $d->reference . '-1' && $r['mobile'] === '0551234567' && $r['network'] === 'MTN' && (float) $r['amount'] === 270.0
            && $r['callback_url'] === 'https://shop.test/webhooks/speso');

        // nobody is texted about an order that has not been paid for
        $this->assertSame(0, SmsLog::whereIn('purpose', ['order_received', 'new_order'])->count());

        $page = $this->get(URL::temporarySignedRoute('shop.order', now()->addDays(7), ['reference' => $d->reference]))->assertOk();
        $page->assertSee('Approve the payment on your phone')->assertSee('0551234567')->assertSee('*170#');
    }

    public function test_an_http_callback_url_is_not_sent_to_speso(): void
    {
        config(['services.speso.callback_url' => 'http://localhost/drezwel_app/public/webhooks/speso']);
        $this->checkout($this->variant());

        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/collections') && ! isset($r['callback_url']));
    }

    public function test_pay_on_delivery_still_works_and_notifies_straight_away(): void
    {
        $this->checkout($this->variant(), ['pay_method' => 'cod'])->assertRedirect();
        $d = Delivery::firstOrFail();

        $this->assertSame('cod', $d->payment_method);
        $this->assertNull($d->payment_status);
        $this->assertSame(1, SmsLog::where('purpose', 'order_received')->count());
        Http::assertNotSent(fn (Request $r) => str_ends_with($r->url(), '/collections'));
    }

    public function test_momo_number_defaults_to_the_customers_phone_and_is_validated(): void
    {
        $v = $this->variant();
        $this->checkout($v, ['momo_number' => ''])->assertRedirect();
        $this->assertSame('0241234567', Delivery::firstOrFail()->payment_mobile);

        $this->checkout($v, ['momo_number' => '123'])->assertSessionHasErrors('momo_number');
        $this->checkout($v, ['network' => 'XYZ'])->assertSessionHasErrors('network');
    }

    public function test_online_payment_is_refused_when_speso_is_not_configured(): void
    {
        config(['services.speso.api_key' => null]);
        $v = $this->variant();

        $this->withSession(['cart' => [$v->id => 1]])->get(route('shop.checkout'))->assertOk()->assertDontSee('Pay now with Mobile Money');
        $this->checkout($v)->assertSessionHasErrors('pay_method');
        $this->assertSame(0, Delivery::count());
    }

    public function test_a_prompt_that_cannot_be_sent_leaves_the_order_retryable(): void
    {
        $this->spesoDown = true;
        $d = $this->payNow($this->variant());

        $this->assertSame('failed', $d->payment_status);
        $this->get(URL::temporarySignedRoute('shop.order', now()->addDays(7), ['reference' => $d->reference]))->assertSee('Payment not completed')->assertSee('Pay on delivery instead');
    }

    // ------------------------------------------------------------ resolving the payment

    public function test_the_order_page_poll_records_the_payment_once_when_speso_says_completed(): void
    {
        $d = $this->payNow($this->variant());
        $status = URL::temporarySignedRoute('shop.order.status', now()->addHour(), ['reference' => $d->reference]);

        $this->getJson($status)->assertJson(['state' => 'awaiting']);

        $this->spesoStatus = 'completed';
        $this->app['cache']->flush();                                  // skip the 4s poll throttle
        $this->getJson($status)->assertJson(['state' => 'paid']);
        $this->app['cache']->flush();
        $this->getJson($status)->assertJson(['state' => 'paid']);

        $d->refresh();
        $this->assertSame('paid', $d->payment_status);
        $this->assertSame(1, $d->payments()->count());
        $this->assertSame('momo', $d->payments()->first()->method);
        $this->assertEquals(270.0, (float) $d->amount_paid);
        $this->assertEquals(0.0, $d->balance);
        $this->assertSame('SP-REF-1', $d->payment_reference);
        $this->assertSame(1, SmsLog::where('purpose', 'order_received')->count());   // told once, and only now

        $this->get(URL::temporarySignedRoute('shop.order', now()->addDays(7), ['reference' => $d->reference]))->assertSee('Payment received')->assertSee('Paid online');
    }

    public function test_a_failed_payment_can_be_retried_with_a_fresh_prompt_or_switched_to_cash(): void
    {
        $d = $this->payNow($this->variant());
        $this->spesoStatus = 'failed';
        $this->app['cache']->flush();
        $this->getJson(URL::temporarySignedRoute('shop.order.status', now()->addHour(), ['reference' => $d->reference]))->assertJson(['state' => 'failed']);
        $this->assertSame('failed', $d->fresh()->payment_status);

        $this->spesoStatus = 'processing';
        $this->post(URL::temporarySignedRoute('shop.order.pay', now()->addHour(), ['reference' => $d->reference]), ['momo_number' => '0201234567', 'network' => 'VOD'])->assertRedirect();
        $d->refresh();
        $this->assertSame('awaiting', $d->payment_status);
        $this->assertSame($d->reference . '-2', $d->payment_order_id);         // a new id every attempt: Speso needs them unique
        $this->assertSame('VOD', $d->payment_network);

        // customer gives up and chooses cash
        $this->spesoStatus = 'failed';
        $this->post(URL::temporarySignedRoute('shop.order.cod', now()->addHour(), ['reference' => $d->reference]));
        $d->refresh();
        $this->assertSame('cod', $d->payment_method);
        $this->assertNull($d->payment_status);
        $this->assertSame(1, SmsLog::where('purpose', 'order_received')->count());
    }

    public function test_retrying_does_not_send_a_second_prompt_if_the_first_was_actually_approved(): void
    {
        $d = $this->payNow($this->variant());
        $this->spesoStatus = 'completed';
        $this->app['cache']->flush();

        $this->post(URL::temporarySignedRoute('shop.order.pay', now()->addHour(), ['reference' => $d->reference]), ['momo_number' => '0201234567', 'network' => 'MTN']);

        $this->assertSame('paid', $d->fresh()->payment_status);
        $this->assertSame(1, $d->payments()->count());
        $this->assertCount(1, Http::recorded(fn (Request $r) => $r->method() === 'POST' && str_ends_with($r->url(), '/collections')));
    }

    // ------------------------------------------------------------ webhook

    public function test_a_signed_webhook_marks_the_order_paid_and_a_replay_changes_nothing(): void
    {
        $d = $this->payNow($this->variant());
        $payload = ['order_id' => $d->reference . '-1', 'speso_reference' => 'SP-9', 'status' => 'completed', 'amount' => 270, 'network' => 'MTN'];

        $this->webhook($payload)->assertOk()->assertJson(['success' => true]);
        $this->webhook($payload)->assertOk();

        $d->refresh();
        $this->assertSame('paid', $d->payment_status);
        $this->assertSame('SP-9', $d->payment_reference);
        $this->assertSame(1, $d->payments()->count());
        $this->assertSame(1, SmsLog::where('purpose', 'order_received')->count());
    }

    public function test_webhook_rejects_bad_stale_and_unsigned_requests(): void
    {
        $d = $this->payNow($this->variant());
        $payload = ['order_id' => $d->reference . '-1', 'speso_reference' => 'SP-9', 'status' => 'completed', 'amount' => 270];

        $this->webhook($payload, null, 'wrong-secret')->assertStatus(401);
        $this->webhook($payload, time() - 3600)->assertStatus(401);
        $this->postJson('/webhooks/speso', $payload)->assertStatus(401);
        $this->assertSame('awaiting', $d->fresh()->payment_status);

        config(['services.speso.webhook_secret' => null]);
        $this->webhook($payload)->assertStatus(401);                   // never accept unsigned callbacks when no secret is set
    }

    public function test_webhook_with_the_wrong_amount_or_unknown_order_is_not_applied(): void
    {
        $d = $this->payNow($this->variant());

        $this->webhook(['order_id' => $d->reference . '-1', 'speso_reference' => 'SP-9', 'status' => 'completed', 'amount' => 5])->assertStatus(422);
        $this->assertSame('awaiting', $d->fresh()->payment_status);
        $this->assertSame(0, $d->payments()->count());

        $this->webhook(['order_id' => 'NOPE-1', 'speso_reference' => 'SP-9', 'status' => 'completed', 'amount' => 270])->assertStatus(404);
    }

    public function test_a_failed_webhook_marks_the_payment_failed_but_never_downgrades_a_paid_order(): void
    {
        $d = $this->payNow($this->variant());
        $this->webhook(['order_id' => $d->reference . '-1', 'speso_reference' => 'SP-9', 'status' => 'processing', 'amount' => 270])->assertOk();
        $this->assertSame('awaiting', $d->fresh()->payment_status);

        $this->webhook(['order_id' => $d->reference . '-1', 'speso_reference' => 'SP-9', 'status' => 'failed', 'amount' => 270])->assertOk();
        $this->assertSame('failed', $d->fresh()->payment_status);
    }

    public function test_a_late_failed_callback_does_not_undo_a_payment(): void
    {
        $d = $this->payNow($this->variant());
        $ok = ['order_id' => $d->reference . '-1', 'speso_reference' => 'SP-9', 'amount' => 270];
        $this->webhook($ok + ['status' => 'completed'])->assertOk();
        $this->webhook($ok + ['status' => 'failed'])->assertOk();

        $this->assertSame('paid', $d->fresh()->payment_status);
        $this->assertEquals(0.0, $d->fresh()->balance);
    }

    // ------------------------------------------------------------ reconcile + manager

    public function test_reconcile_settles_a_payment_whose_callback_never_arrived(): void
    {
        $d = $this->payNow($this->variant());
        $d->update(['payment_requested_at' => now()->subMinutes(5)]);
        $this->spesoStatus = 'completed';

        $this->artisan('speso:reconcile')->assertSuccessful();

        $this->assertSame('paid', $d->fresh()->payment_status);
        $this->assertSame(1, $d->payments()->count());
    }

    public function test_reconcile_cancels_an_unpaid_order_after_the_deadline_and_puts_the_stock_back(): void
    {
        $v = $this->variant();
        $d = $this->payNow($v);
        $this->assertSame(4, $v->fresh()->stock_qty);

        $d->update(['payment_requested_at' => now()->subMinutes(10)]);
        $this->artisan('speso:reconcile')->assertSuccessful();
        $this->assertSame('awaiting', $d->fresh()->payment_status);   // still inside the 60 minutes

        $d->update(['payment_requested_at' => now()->subMinutes(90)]);
        $this->artisan('speso:reconcile')->assertSuccessful();

        $d->refresh();
        $this->assertSame('cancelled', $d->status);
        $this->assertSame('abandoned', $d->payment_status);
        $this->assertSame(5, $v->fresh()->stock_qty);
        $this->assertSame(5, $v->product->fresh()->stock_qty);
    }

    public function test_reconcile_never_cancels_an_order_it_could_not_check_or_that_turned_out_paid(): void
    {
        $v = $this->variant();
        $d = $this->payNow($v);
        $d->update(['payment_requested_at' => now()->subMinutes(90)]);

        $this->spesoDown = true;                                        // cannot ask Speso: do not guess
        $this->artisan('speso:reconcile')->assertSuccessful();
        $this->assertSame('pending', $d->fresh()->status);
        $this->assertSame(4, $v->fresh()->stock_qty);

        $this->spesoDown = false;
        $this->spesoStatus = 'completed';                               // it was paid after all
        $this->artisan('speso:reconcile')->assertSuccessful();
        $this->assertSame('pending', $d->fresh()->status);
        $this->assertSame('paid', $d->fresh()->payment_status);
    }

    public function test_unpaid_online_orders_are_not_counted_as_waiting_for_a_rider(): void
    {
        $m = User::factory()->create(['role' => 'manager']);
        $d = $this->payNow($this->variant());

        $this->actingAs($m)->get(route('manager.dashboard'))->assertDontSee('New website / phone orders waiting');

        $this->webhook(['order_id' => $d->reference . '-1', 'speso_reference' => 'SP-9', 'status' => 'completed', 'amount' => 270])->assertOk();
        $this->actingAs($m)->get(route('manager.dashboard'))->assertSee('New website / phone orders waiting');
    }

    public function test_manager_can_verify_a_payment_with_speso_from_the_order_page(): void
    {
        $m = User::factory()->create(['role' => 'manager']);
        $d = $this->payNow($this->variant());

        $this->actingAs($m)->get(route('manager.deliveries.show', $d))->assertOk()->assertSee('Verify with Speso')->assertSee('Do not send a rider yet');

        $this->spesoStatus = 'completed';
        $this->actingAs($m)->post(route('manager.deliveries.verify-payment', $d))->assertSessionHas('success');
        $this->assertSame('paid', $d->fresh()->payment_status);

        $this->actingAs($m)->get(route('manager.deliveries.show', $d))->assertSee('Paid online via Speso')->assertDontSee('Verify with Speso');
    }

    public function test_only_managers_can_verify_payments_and_order_links_must_be_signed(): void
    {
        $d = $this->payNow($this->variant());

        $this->post(route('manager.deliveries.verify-payment', $d))->assertRedirect('/login');
        $this->getJson(route('shop.order.status', $d->reference))->assertForbidden();                 // unsigned
        $this->post(route('shop.order.cod', $d->reference))->assertForbidden();
        $this->post(route('shop.order.pay', $d->reference), ['momo_number' => '0201234567', 'network' => 'MTN'])->assertForbidden();
    }
}
