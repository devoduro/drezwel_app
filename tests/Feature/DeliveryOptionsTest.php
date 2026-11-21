<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\Product;
use App\Models\Region;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/** Regular delivery, Express delivery and Pick up, and who has to pay now with Mobile Money. */
class DeliveryOptionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        Setting::set('store_open', '1');
        Setting::set('default_delivery_fee', '15');
        Setting::set('address', 'Loc. Beach Road, Accra');
        config(['services.speso.api_key' => 'sk_test_x', 'services.speso_sms.api_key' => 'sk_test_x']);

        // Accra: Tema GH₵ 50.  Ashanti: Kumasi GH₵ 60.
        $ga = Region::where('name', 'Greater Accra')->first();
        $ga->zones()->where('name', 'Tema')->update(['fee' => 50, 'is_active' => true]);
        $ashanti = Region::where('name', 'Ashanti')->first();
        $ashanti->zones()->where('name', 'Kumasi')->update(['fee' => 60, 'is_active' => true]);
    }

    private $variant = null;

    private function variant()
    {
        if (! $this->variant) {
            $p = Product::create(['name' => 'Tee', 'slug' => 'tee', 'cost_price' => 20, 'selling_price' => 100, 'show_in_store' => true, 'stock_qty' => 200]);
            $this->variant = $p->variants()->create(['stock_qty' => 200]);
        }

        return $this->variant;
    }

    private function place(string $service, string $region, ?string $town, array $extra = [])
    {
        $r = Region::where('name', $region)->first();
        $z = $town ? $r->zones()->where('name', $town)->value('id') : null;

        return $this->withSession(['cart' => [$this->variant()->id => 1]])->post(route('shop.checkout.place'), $extra + [
            'name' => 'Ama', 'phone' => '0241234567', 'service' => $service, 'region_id' => $r->id, 'zone_id' => $z, 'address' => 'House 5',
            'pay_method' => 'speso', 'momo_number' => '0551234567', 'network' => 'MTN',
        ]);
    }

    public function test_checkout_offers_the_three_options_whatever_the_region(): void
    {
        $v = $this->variant();
        $this->withSession(['cart' => [$v->id => 1]])->get(route('shop.checkout'))->assertOk()
            ->assertSee('Regular delivery')->assertSee('48 to 72 hours from the time of order placement')->assertDontSee('Door delivery')->assertSee('Express delivery')->assertSee('Same day in Accra, 24 hours outside Accra')->assertSee('Pick up')
            ->assertSee('name="service" value="door"', false)->assertSee('name="service" value="express"', false)->assertSee('name="service" value="pickup"', false)
            ->assertSee('Payment on delivery unavailable for express delivery, pick up and orders outside Accra.')
            ->assertSee('"accra":true', false);                                       // the page knows which region is Accra
    }

    public function test_door_delivery_in_accra_can_still_be_paid_on_delivery(): void
    {
        $this->place('door', 'Greater Accra', 'Tema', ['pay_method' => 'cod'])->assertSessionHasNoErrors();
        $d = Delivery::firstOrFail();

        $this->assertSame('door', $d->service);
        $this->assertSame('rider', $d->delivery_mode);
        $this->assertEquals(50.0, (float) $d->delivery_fee);
        $this->assertSame('cod', $d->payment_method);
        $this->assertFalse($d->pay_now_only);
    }

    public function test_door_delivery_outside_accra_has_to_be_paid_now(): void
    {
        $this->place('door', 'Ashanti', 'Kumasi', ['pay_method' => 'cod'])->assertSessionHasErrors('pay_method');
        $this->assertSame(0, Delivery::count());

        $this->place('door', 'Ashanti', 'Kumasi')->assertSessionHasNoErrors();
        $d = Delivery::firstOrFail();
        $this->assertSame('speso', $d->payment_method);
        $this->assertSame('awaiting', $d->payment_status);
        $this->assertSame('station_bus', $d->delivery_mode);                          // outside Accra it goes by station bus
        $this->assertEquals(60.0, (float) $d->delivery_fee);                          // no express charge on door delivery
        $this->assertTrue($d->pay_now_only);
    }

    public function test_express_delivery_is_always_paid_now_and_outside_accra_adds_the_station_charge(): void
    {
        // in Accra: pay now, same fee as door delivery, no station charge
        $this->place('express', 'Greater Accra', 'Tema', ['pay_method' => 'cod'])->assertSessionHasErrors('pay_method');
        $this->place('express', 'Greater Accra', 'Tema')->assertSessionHasNoErrors();
        $a = Delivery::firstOrFail();
        $this->assertSame('express', $a->service);
        $this->assertSame('rider', $a->delivery_mode);
        $this->assertEquals(50.0, (float) $a->delivery_fee);
        $this->assertSame('speso', $a->payment_method);

        // outside Accra: the town's fee plus GH₵ 30 to take the goods to the station
        $this->place('express', 'Ashanti', 'Kumasi')->assertSessionHasNoErrors();
        $b = Delivery::latest('id')->first();
        $this->assertSame('station_bus', $b->delivery_mode);
        $this->assertEquals(90.0, (float) $b->delivery_fee);                          // 60 + 30
        $this->assertEquals(190.0, $b->amount_due);                                   // goods 100 + 90

        // another town in the region uses the region's fee, and still gets the station charge
        $this->place('express', 'Ashanti', null)->assertSessionHasNoErrors();
        $this->assertEquals(45.0, (float) Delivery::latest('id')->first()->delivery_fee);   // shop default 15 + 30
    }

    public function test_the_station_charge_can_be_changed_in_settings(): void
    {
        Setting::set('express_station_fee', '45');
        $this->place('express', 'Ashanti', 'Kumasi')->assertSessionHasNoErrors();
        $this->assertEquals(105.0, (float) Delivery::firstOrFail()->delivery_fee);    // 60 + 45
    }

    public function test_pick_up_needs_no_address_costs_nothing_to_deliver_and_is_paid_now(): void
    {
        $this->withSession(['cart' => [$this->variant()->id => 1]])->post(route('shop.checkout.place'), [
            'name' => 'Ama', 'phone' => '0241234567', 'service' => 'pickup', 'pay_method' => 'cod',
        ])->assertSessionHasErrors('pay_method');                                    // cash is not accepted for pick up
        $this->assertSame(0, Delivery::count());

        $this->withSession(['cart' => [$this->variant()->id => 1]])->post(route('shop.checkout.place'), [
            'name' => 'Ama', 'phone' => '0241234567', 'service' => 'pickup', 'pay_method' => 'speso', 'momo_number' => '', 'network' => 'MTN',
        ])->assertSessionHasNoErrors();

        $d = Delivery::firstOrFail();
        $this->assertSame('pickup', $d->service);
        $this->assertSame('pick_up', $d->delivery_mode);
        $this->assertEquals(0.0, (float) $d->delivery_fee);
        $this->assertSame('Pick up: Loc. Beach Road, Accra', $d->location);
        $this->assertSame('awaiting', $d->payment_status);
        $this->assertEquals(100.0, $d->amount_due);

        // a rider cannot pick it up from the queue
        $rider = \App\Models\User::factory()->create(['role' => 'rider']);
        $this->actingAs($rider)->post(route('rider.claim', $d));
        $this->assertNull($d->fresh()->rider_id);
    }

    public function test_online_payment_being_down_blocks_the_options_that_must_be_paid_now(): void
    {
        config(['services.speso.api_key' => null]);

        $this->place('express', 'Greater Accra', 'Tema', ['pay_method' => 'cod'])->assertSessionHasErrors('pay_method');
        $this->place('door', 'Ashanti', 'Kumasi', ['pay_method' => 'cod'])->assertSessionHasErrors('pay_method');
        $this->assertSame(0, Delivery::count());

        $this->place('door', 'Greater Accra', 'Tema', ['pay_method' => 'cod'])->assertSessionHasNoErrors();   // door delivery in Accra still works
        $this->assertSame(1, Delivery::count());
    }

    public function test_a_pay_now_order_cannot_be_switched_to_cash_on_delivery(): void
    {
        $this->place('express', 'Greater Accra', 'Tema')->assertSessionHasNoErrors();
        $d = Delivery::firstOrFail();
        $d->update(['payment_status' => 'failed']);

        $url = fn ($route) => URL::temporarySignedRoute($route, now()->addHour(), ['reference' => $d->reference]);
        $this->get(URL::temporarySignedRoute('shop.order', now()->addDay(), ['reference' => $d->reference]))->assertOk()->assertDontSee('Pay on delivery instead');
        $this->post($url('shop.order.cod'))->assertSessionHas('pay_error');
        $this->assertSame('speso', $d->fresh()->payment_method);
        $this->assertSame('failed', $d->fresh()->payment_status);                      // still unpaid, still not cash

        // door delivery within Accra can still fall back to cash
        $this->place('door', 'Greater Accra', 'Tema')->assertSessionHasNoErrors();
        $ok = Delivery::latest('id')->first();
        $ok->update(['payment_status' => 'failed']);
        $this->post(URL::temporarySignedRoute('shop.order.cod', now()->addHour(), ['reference' => $ok->reference]));
        $this->assertSame('cod', $ok->fresh()->payment_method);
    }

    public function test_an_unknown_option_falls_back_to_door_delivery_and_the_manager_sees_the_option(): void
    {
        $this->place('teleport', 'Greater Accra', 'Tema', ['pay_method' => 'cod'])->assertSessionHasNoErrors();
        $this->assertSame('door', Delivery::firstOrFail()->service);

        $this->place('express', 'Greater Accra', 'Tema')->assertSessionHasNoErrors();
        $m = \App\Models\User::factory()->create(['role' => 'manager']);
        $x = Delivery::where('service', 'express')->firstOrFail();
        $x->update(['payment_status' => 'paid']);
        $this->actingAs($m)->get(route('manager.deliveries.index'))->assertOk()->assertSee('EXPRESS');
        $this->actingAs($m)->get(route('manager.deliveries.show', $x))->assertOk()->assertSee('Express delivery');
        $this->actingAs($m)->get(route('manager.settings'))->assertOk()->assertSee('Express delivery outside Accra')->assertSee('name="express_station_fee"', false);
    }
}
