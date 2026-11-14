<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\SmsLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrackLoginTest extends TestCase
{
    use RefreshDatabase;

    private function order(Customer $c, string $pkg = 'Jeans'): Delivery
    {
        return Delivery::create([
            'reference' => Delivery::newReference(), 'customer_id' => $c->id, 'location' => 'Osu', 'package_description' => $pkg,
            'delivery_date' => today(), 'total_amount' => 200, 'items_sent' => 1, 'status' => 'pending',
        ]);
    }

    private function sentCode(): ?string
    {
        $code = null;
        Http::assertSent(function ($req) use (&$code) {
            if (preg_match('/code is (\d{6})/', $req['message'] ?? '', $m)) {
                $code = $m[1];
            }

            return true;
        });

        return $code;
    }

    public function test_customer_signs_in_with_a_texted_code_and_sees_only_their_orders(): void
    {
        config(['services.speso_sms.api_key' => 'k']);
        Http::fake(['*' => Http::response(['success' => true])]);
        $ama = Customer::create(['name' => 'Ama', 'phone' => '0244000111']);
        $kofi = Customer::create(['name' => 'Kofi', 'phone' => '0244000222']);
        $mine = $this->order($ama, 'Ama-dress');
        $theirs = $this->order($kofi, 'Kofi-shoes');

        $this->get('/track')->assertOk()->assertSee('Send me a code');
        $this->post('/track/code', ['phone' => '+233 24 400 0111'])->assertRedirect('/track');
        $code = $this->sentCode();
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);

        $this->get('/track')->assertSee('Enter the 6-digit code');
        $this->post('/track/verify', ['code' => $code])->assertRedirect('/track');

        $this->get('/track')->assertOk()->assertSee('Ama-dress')->assertDontSee('Kofi-shoes');
        $this->get('/track?ref=' . $mine->reference)->assertSee('Order placed');
        $this->get('/track?ref=' . $theirs->reference)->assertSee('not on your account');

        // the code was single-use and never stored in the SMS log
        $this->assertFalse(SmsLog::where('message', 'like', "%{$code}%")->exists());
        $this->post('/track/logout');
        $this->get('/track')->assertSee('Send me a code')->assertDontSee('Ama-dress');
    }

    public function test_wrong_codes_are_limited_and_unknown_numbers_look_the_same(): void
    {
        config(['services.speso_sms.api_key' => 'k']);
        Http::fake(['*' => Http::response(['success' => true])]);
        $ama = Customer::create(['name' => 'Ama', 'phone' => '0244000111']);
        $this->order($ama, 'Ama-dress');

        // unknown number: same reply, but nothing is texted
        $this->post('/track/code', ['phone' => '0200000000'])->assertRedirect('/track')->assertSessionHas('success');
        Http::assertNothingSent();

        $this->post('/track/code', ['phone' => '0244000111']);
        $code = $this->sentCode();
        $bad = $code === '000000' ? '111111' : '000000';
        for ($i = 0; $i < 5; $i++) {
            $this->post('/track/verify', ['code' => $bad])->assertSessionHasErrors('code');
        }
        // after 5 misses even the right code is refused
        $this->post('/track/verify', ['code' => $code])->assertSessionHasErrors('code');
        $this->get('/track')->assertDontSee('Ama-dress');
    }

    public function test_codes_cannot_be_requested_in_quick_succession(): void
    {
        Http::fake();
        config(['services.speso_sms.api_key' => 'k']);
        Customer::create(['name' => 'Ama', 'phone' => '0244000111']);
        $this->post('/track/code', ['phone' => '0244000111'])->assertSessionHasNoErrors();
        $this->post('/track/code', ['phone' => '0244000111'])->assertSessionHasErrors('phone');
    }

    public function test_signed_in_orders_are_split_into_tabs_and_detail_shows_items_and_payment(): void
    {
        $ama = Customer::create(['name' => 'Ama', 'phone' => '0244000111']);
        $open = $this->order($ama, 'Open-dress');
        $done = $this->order($ama, 'Done-shirt');
        $done->update(['status' => 'delivered', 'delivered_at' => now(), 'amount_paid' => 200]);
        $gone = $this->order($ama, 'Gone-hat');
        $gone->update(['status' => 'cancelled']);

        $this->withSession(['track_customer_id' => $ama->id]);
        $this->get('/track')->assertOk()->assertSee('Orders (3)')->assertSee('Ongoing (1)')->assertSee('Delivered (1)')->assertSee('Cancelled (1)')
            ->assertSee('Open-dress')->assertDontSee('Done-shirt')->assertDontSee('Gone-hat')->assertSee('Sign out');
        $this->get('/track?tab=delivered')->assertSee('Done-shirt')->assertDontSee('Open-dress');
        $this->get('/track?tab=cancelled')->assertSee('Gone-hat')->assertDontSee('Open-dress');

        $this->get('/track?ref=' . $open->reference)->assertOk()->assertSee('Back to orders')->assertSee('Items in your order')->assertSee('Payment information')
            ->assertSee('Delivery information')->assertSee('Order updates')->assertSee('Print invoice')->assertSee('GH₵ 200.00');
    }

    public function test_visitor_page_has_track_and_sign_in_cards(): void
    {
        $this->get('/track')->assertOk()->assertSee('Track an order')->assertSee('Sign in to see all your orders')->assertSee('Order number')->assertSee('Send me a code');
    }

    public function test_quick_lookup_still_works_without_signing_in(): void
    {
        $c = Customer::create(['name' => 'Ama', 'phone' => '0244000111']);
        $d = $this->order($c, 'Ama-dress');
        $this->get('/track?ref=' . $d->reference . '&phone=0244000111')->assertSee('Order placed');
        $this->get('/track?ref=' . $d->reference . '&phone=0200000000')->assertSee('could not find');
    }
}
