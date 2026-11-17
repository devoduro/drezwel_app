<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\DeliveryZone;
use App\Models\Product;
use App\Models\Region;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeliveryFeeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        Setting::set('store_open', '1');
        Setting::set('default_delivery_fee', '15');
    }

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    private function region(string $name): Region
    {
        return Region::where('name', $name)->firstOrFail();
    }

    private function item()
    {
        $p = Product::create(['name' => 'Tee', 'slug' => 'tee', 'cost_price' => 20, 'selling_price' => 100, 'show_in_store' => true, 'stock_qty' => 9]);

        return $p->variants()->create(['stock_qty' => 9]);
    }

    private function checkout(array $extra = [], $variant = null)
    {
        $v = $variant ?? $this->item();

        return $this->withSession(['cart' => [$v->id => 1]])->post(route('shop.checkout.place'), $extra + [
            'name' => 'Ama', 'phone' => '0241234567', 'pay_method' => 'cod', 'address' => 'House 5, Near the market',
        ]);
    }

    public function test_all_sixteen_regions_are_in_the_system_with_their_towns_unpriced(): void
    {
        $this->assertSame(16, Region::count());
        foreach (['Greater Accra', 'Ashanti', 'Western', 'Western North', 'Central', 'Eastern', 'Volta', 'Oti', 'Northern', 'Savannah', 'North East', 'Upper East', 'Upper West', 'Bono', 'Bono East', 'Ahafo'] as $name) {
            $this->assertNotNull(Region::where('name', $name)->first(), $name);
        }
        $this->assertTrue($this->region('Ashanti')->zones()->where('name', 'Kumasi')->exists());
        $this->assertSame(0, DeliveryZone::whereNotNull('fee')->count());           // nothing is priced until the manager does it
    }

    public function test_manager_adds_many_towns_at_once_with_or_without_fees(): void
    {
        $m = $this->manager();
        $volta = $this->region('Volta');

        $this->actingAs($m)->post(route('manager.fees.towns', $volta), ['names' => "Kpetoe, 40\nAdidome: 45\nTefle - 50\nDzodze\n\n Ho , 35", 'fee' => 55])->assertSessionHas('success');

        $z = fn ($n) => DeliveryZone::where('region_id', $volta->id)->where('name', $n)->first();
        $this->assertEquals(40.0, (float) $z('Kpetoe')->fee);
        $this->assertEquals(45.0, (float) $z('Adidome')->fee);
        $this->assertEquals(50.0, (float) $z('Tefle')->fee);
        $this->assertEquals(55.0, (float) $z('Dzodze')->fee);                         // the shared fee for a line without one
        $this->assertEquals(35.0, (float) $z('Ho')->fee);                             // existing town: updated, not duplicated
        $this->assertSame(1, DeliveryZone::where('region_id', $volta->id)->where('name', 'Ho')->count());
    }

    public function test_manager_prices_many_towns_in_one_go_and_can_switch_them_off_or_clear_them(): void
    {
        $m = $this->manager();
        $ga = $this->region('Greater Accra');
        $ids = $ga->zones()->whereIn('name', ['Tema', 'Madina', 'Adenta'])->pluck('id')->all();

        $this->actingAs($m)->put(route('manager.fees.update', $ga), ['do' => 'set_fee', 'bulk_fee' => 25, 'ids' => $ids])->assertSessionHas('success');
        $this->assertSame(3, DeliveryZone::whereIn('id', $ids)->where('fee', 25)->count());
        $this->assertSame(0, $ga->zones()->whereNotIn('id', $ids)->whereNotNull('fee')->count());

        $this->actingAs($m)->put(route('manager.fees.update', $ga), ['do' => 'set_fee', 'bulk_fee' => 18, 'all' => 1])->assertSessionHas('success');       // every town in the region
        $this->assertSame($ga->zones()->count(), $ga->zones()->where('fee', 18)->count());

        $this->actingAs($m)->put(route('manager.fees.update', $ga), ['do' => 'deactivate', 'ids' => [$ids[0]]]);
        $this->assertFalse(DeliveryZone::find($ids[0])->is_active);
        $this->actingAs($m)->put(route('manager.fees.update', $ga), ['do' => 'clear_fee', 'ids' => [$ids[1]]]);
        $this->assertNull(DeliveryZone::find($ids[1])->fee);

        $this->actingAs($m)->put(route('manager.fees.update', $ga), ['do' => 'set_fee', 'bulk_fee' => 9])->assertSessionHas('error');      // nothing ticked
        $this->actingAs($m)->put(route('manager.fees.update', $ga), ['do' => 'set_fee', 'ids' => $ids])->assertSessionHas('error');       // no fee typed
    }

    public function test_saving_the_table_updates_every_fee_and_the_regions_other_town_fee(): void
    {
        $m = $this->manager();
        $ashanti = $this->region('Ashanti');
        $kumasi = $ashanti->zones()->where('name', 'Kumasi')->first();
        $obuasi = $ashanti->zones()->where('name', 'Obuasi')->first();

        $this->actingAs($m)->put(route('manager.fees.update', $ashanti), [
            'do' => 'save', 'default_fee' => 60, 'fees' => [$kumasi->id => 40, $obuasi->id => ''], 'active' => [$kumasi->id => 1],
        ])->assertSessionHas('success');

        $this->assertEquals(40.0, (float) $kumasi->fresh()->fee);
        $this->assertTrue($kumasi->fresh()->is_active);
        $this->assertNull($obuasi->fresh()->fee);
        $this->assertFalse($obuasi->fresh()->is_active);                              // unticked = switched off
        $this->assertEquals(60.0, (float) $ashanti->fresh()->default_fee);

        $this->actingAs($m)->delete(route('manager.fees.destroy', $kumasi))->assertSessionHas('success');
        $this->assertNull(DeliveryZone::find($kumasi->id));
    }

    public function test_the_fees_page_lists_regions_and_towns_and_only_managers_can_use_it(): void
    {
        $m = $this->manager();
        $this->actingAs($m)->get(route('manager.fees'))->assertOk()->assertSee('Greater Accra')->assertSee('Upper West')->assertSee('NOT PRICED');
        $this->actingAs($m)->get(route('manager.fees', ['region' => $this->region('Ashanti')->id]))->assertOk()->assertSee('Kumasi');
        $this->actingAs($m)->get(route('manager.dashboard'))->assertSee('Delivery fees');

        $rider = User::factory()->create(['role' => 'rider']);
        $this->actingAs($rider)->get(route('manager.fees'))->assertForbidden();
        $this->actingAs($rider)->put(route('manager.fees.update', $this->region('Ashanti')), ['do' => 'set_fee', 'bulk_fee' => 1, 'all' => 1])->assertForbidden();
        $this->assertSame(0, DeliveryZone::whereNotNull('fee')->count());
    }

    public function test_checkout_only_offers_priced_active_towns_and_the_fee_follows_the_choice(): void
    {
        $ashanti = $this->region('Ashanti');
        $ashanti->zones()->where('name', 'Kumasi')->update(['fee' => 40]);
        $ashanti->zones()->where('name', 'Obuasi')->update(['fee' => 55, 'is_active' => false]);
        $ashanti->update(['default_fee' => 70]);
        $v = $this->item();

        $page = $this->withSession(['cart' => [$v->id => 1]])->get(route('shop.checkout'))->assertOk()->assertSee('Region')->assertSee('City / town')->assertSee('Greater Accra')->getContent();
        $this->assertStringContainsString('"name":"Kumasi","fee":40', $page);          // priced + on: offered
        $this->assertStringNotContainsString('"name":"Obuasi"', $page);                // switched off
        $this->assertStringNotContainsString('"name":"Mampong"', $page);               // not priced

        $this->checkout(['region_id' => $ashanti->id, 'zone_id' => $ashanti->zones()->where('name', 'Kumasi')->value('id')], $v)->assertRedirect();
        $d = Delivery::firstOrFail();
        $this->assertEquals(40.0, (float) $d->delivery_fee);
        $this->assertEquals(140.0, $d->amount_due);                                    // goods 100 + fee 40
        $this->assertSame('House 5, Near the market, Kumasi, Ashanti', $d->location);
    }

    public function test_another_town_uses_the_region_fee_and_an_unlisted_region_uses_the_shop_default(): void
    {
        $ashanti = $this->region('Ashanti');
        $ashanti->update(['default_fee' => 70]);
        $v = $this->item();

        $this->checkout(['region_id' => $ashanti->id, 'zone_id' => 'other'], $v)->assertRedirect();
        $this->assertEquals(70.0, (float) Delivery::firstOrFail()->delivery_fee);
        $this->assertSame('House 5, Near the market, Ashanti', Delivery::firstOrFail()->location);

        $volta = $this->region('Volta');                                               // no default fee, so the shop-wide GH₵ 15 applies
        $this->checkout(['region_id' => $volta->id, 'zone_id' => 'other'], $v)->assertRedirect();
        $this->assertEquals(15.0, (float) Delivery::latest('id')->first()->delivery_fee);
    }

    public function test_a_customer_cannot_choose_their_own_fee_or_a_town_from_another_region(): void
    {
        $ga = $this->region('Greater Accra');
        $ga->zones()->where('name', 'Tema')->update(['fee' => 30]);
        $ashanti = $this->region('Ashanti');
        $ashanti->zones()->where('name', 'Kumasi')->update(['fee' => 40]);
        $unpriced = $ashanti->zones()->where('name', 'Obuasi')->first();
        $v = $this->item();

        $this->checkout(['region_id' => $ga->id, 'zone_id' => $ga->zones()->where('name', 'Tema')->value('id'), 'delivery_fee' => 0], $v)->assertRedirect();
        $this->assertEquals(30.0, (float) Delivery::firstOrFail()->delivery_fee);       // a forged delivery_fee field is ignored

        $this->checkout(['region_id' => $ga->id, 'zone_id' => $ashanti->zones()->where('name', 'Kumasi')->value('id')], $v)->assertSessionHasErrors('zone_id');   // wrong region
        $this->checkout(['region_id' => $ashanti->id, 'zone_id' => $unpriced->id], $v)->assertSessionHasErrors('zone_id');                                      // not priced
        $this->checkout(['region_id' => $ashanti->id, 'zone_id' => 'other', 'address' => ''], $v)->assertSessionHasErrors('address');
        $this->assertSame(1, Delivery::count());
    }

    public function test_cart_says_the_fee_is_worked_out_at_checkout_and_the_manager_form_can_fill_the_fee(): void
    {
        $v = $this->item();
        $this->withSession(['cart' => [$v->id => 1]])->get(route('shop.cart'))->assertOk()->assertSee('Worked out at checkout');

        $this->region('Volta')->zones()->where('name', 'Ho')->update(['fee' => 35]);
        $this->actingAs($this->manager())->get(route('manager.deliveries.create'))->assertOk()->assertSee('Fill the delivery fee from the price list')->assertSee('"name":"Ho","fee":35', false);
    }
}
