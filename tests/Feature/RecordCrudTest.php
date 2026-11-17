<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecordCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    private function variant(int $stock = 10): ProductVariant
    {
        $p = Product::create(['name' => 'Jeans', 'slug' => 'jeans', 'cost_price' => 60, 'selling_price' => 100, 'reorder_level' => 1, 'stock_qty' => $stock]);

        return $p->variants()->create(['stock_qty' => $stock]);
    }

    /** A real order through the same service the POS uses, so stock and ledger are genuine. */
    private function sale(ProductVariant $v, int $qty, array $o = []): Delivery
    {
        return app(SalesService::class)->placeOrder(['name' => 'Kofi', 'phone' => '0241234567', 'location' => 'Osu'], [['variant_id' => $v->id, 'qty' => $qty]],
            $o + ['source' => 'pos', 'status' => 'delivered', 'payment' => ['method' => 'cash', 'amount' => 100 * $qty]]);
    }

    public function test_deleting_a_sale_removes_it_and_puts_the_items_back_in_stock(): void
    {
        $m = $this->manager();
        $v = $this->variant(10);
        $d = $this->sale($v, 3);
        $this->assertSame(7, $v->fresh()->stock_qty);

        $this->actingAs($m)->delete(route('manager.deliveries.destroy', $d))->assertRedirect()->assertSessionHas('success');

        $this->assertNull(Delivery::find($d->id));
        $this->assertSame(0, $d->payments()->count());
        $this->assertSame(0, $d->items()->count());
        $this->assertSame(10, $v->fresh()->stock_qty);
        $this->assertSame(10, $v->product->fresh()->stock_qty);
        $this->assertNotNull(Customer::where('phone', '0241234567')->first());          // the customer is kept
    }

    public function test_deleting_a_cancelled_order_does_not_return_its_stock_twice(): void
    {
        $m = $this->manager();
        $v = $this->variant(10);
        $d = $this->sale($v, 2, ['status' => 'pending', 'source' => 'phone', 'payment' => null]);
        $this->actingAs($m)->post(route('manager.deliveries.cancel', $d))->assertRedirect();
        $this->assertSame(10, $v->fresh()->stock_qty);                                   // cancelling returned it once

        $this->actingAs($m)->delete(route('manager.deliveries.destroy', $d))->assertRedirect();

        $this->assertSame(10, $v->fresh()->stock_qty);                                   // and deleting does not add it again
        $this->assertNull(Delivery::find($d->id));
    }

    public function test_orders_paid_online_or_out_with_a_rider_cannot_be_deleted(): void
    {
        $m = $this->manager();
        $v = $this->variant(10);

        $paid = $this->sale($v, 1, ['status' => 'pending', 'source' => 'online', 'payment' => null]);
        $paid->update(['payment_method' => 'speso', 'payment_status' => 'paid']);
        $this->actingAs($m)->delete(route('manager.deliveries.destroy', $paid))->assertSessionHas('error');
        $this->assertNotNull(Delivery::find($paid->id));

        $out = $this->sale($v, 1, ['status' => 'pending', 'source' => 'phone', 'payment' => null]);
        $out->update(['status' => 'out_for_delivery']);
        $this->actingAs($m)->delete(route('manager.deliveries.destroy', $out))->assertSessionHas('error');
        $this->assertNotNull(Delivery::find($out->id));
    }

    public function test_only_managers_can_delete_records(): void
    {
        $d = $this->sale($this->variant(), 1);
        $rider = User::factory()->create(['role' => 'rider']);

        $this->actingAs($rider)->delete(route('manager.deliveries.destroy', $d))->assertForbidden();
        $this->app['auth']->forgetGuards();
        $this->delete(route('manager.deliveries.destroy', $d))->assertRedirect('/login');
        $this->assertNotNull(Delivery::find($d->id));
    }

    public function test_delete_buttons_appear_on_deliveries_orders_and_sales_and_the_sale_page_links_to_new_sale(): void
    {
        $m = $this->manager();
        $d = $this->sale($this->variant(), 1);
        $url = route('manager.deliveries.destroy', $d);

        foreach ([route('manager.deliveries.index'), route('manager.orders'), route('manager.sales', ['period' => 'all']), route('manager.deliveries.show', $d)] as $page) {
            $this->actingAs($m)->get($page)->assertOk()->assertSee($url, false);
        }
        $this->actingAs($m)->get(route('manager.sales', ['period' => 'all']))->assertSee('New sale')->assertSee(route('manager.deliveries.edit', $d), false);
    }

    public function test_a_finished_sale_can_be_edited_and_stock_follows(): void
    {
        $m = $this->manager();
        $v = $this->variant(10);
        $d = $this->sale($v, 2);                                                         // delivered, 2 sold, stock 8

        $this->actingAs($m)->get(route('manager.deliveries.edit', $d))->assertOk();
        $this->actingAs($m)->put(route('manager.deliveries.update', $d), [
            'customer_name' => 'Kofi Mensah', 'customer_phone' => '0241234567', 'location' => 'Osu, Accra', 'delivery_mode' => 'pick_up', 'order_type' => 'standard',
            'delivery_date' => today()->toDateString(), 'package_description' => 'Jeans', 'total_amount' => 400, 'delivery_fee' => 0, 'items_sent' => 4,
            'items' => [['variant_id' => $v->id, 'qty' => 4, 'price' => 100]],
        ])->assertRedirect();

        $d->refresh();
        $this->assertSame('delivered', $d->status);
        $this->assertSame(6, $v->fresh()->stock_qty);                                    // 10 - 4
        $this->assertSame(4, (int) $d->items_kept);                                      // stays in step with the corrected count
        $this->assertSame('Kofi Mensah', $d->customer->name);
    }

    public function test_cancelled_orders_still_cannot_be_edited(): void
    {
        $m = $this->manager();
        $d = $this->sale($this->variant(), 1, ['status' => 'pending', 'source' => 'phone', 'payment' => null]);
        $this->actingAs($m)->post(route('manager.deliveries.cancel', $d));

        $this->actingAs($m)->put(route('manager.deliveries.update', $d), ['customer_name' => 'X', 'customer_phone' => '0241234567', 'location' => 'Y', 'delivery_mode' => 'rider',
            'order_type' => 'standard', 'delivery_date' => today()->toDateString(), 'package_description' => 'p', 'total_amount' => 1, 'items_sent' => 1])->assertForbidden();
    }
}
