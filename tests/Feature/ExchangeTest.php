<?php

namespace Tests\Feature;

use App\Models\Delivery;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeTest extends TestCase
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

    private function variant(string $name, float $price, int $stock = 10): ProductVariant
    {
        $p = Product::create(['name' => $name, 'slug' => strtolower($name), 'cost_price' => $price / 2, 'selling_price' => $price, 'reorder_level' => 1, 'stock_qty' => $stock]);

        return $p->variants()->create(['stock_qty' => $stock]);
    }

    private function sale(ProductVariant $v, int $qty): Delivery
    {
        return app(SalesService::class)->placeOrder(['name' => 'Kofi', 'phone' => '0241234567', 'location' => 'Osu'], [['variant_id' => $v->id, 'qty' => $qty]],
            ['source' => 'pos', 'status' => 'delivered', 'payment' => ['method' => 'cash', 'amount' => $v->selling_price * $qty]]);
    }

    private function exchange(array $o = [])
    {
        return $o + [
            'customer_name' => 'Kofi', 'customer_phone' => '0241234567', 'location' => 'Osu', 'delivery_mode' => 'pick_up', 'order_type' => 'exchange',
            'delivery_date' => today()->toDateString(), 'total_amount' => 0, 'delivery_fee' => 0,
        ];
    }

    public function test_exchange_balance_is_new_item_minus_returned_item_and_stock_moves_both_ways(): void
    {
        $m = $this->manager();
        $old = $this->variant('Cap', 100, 10);
        $new = $this->variant('Hoodie', 250, 10);
        $orig = $this->sale($old, 1);                                       // cap stock 9

        $this->actingAs($m)->post(route('manager.deliveries.store'), $this->exchange([
            'exchange_of_id' => $orig->id, 'total_amount' => 150, 'settle_now' => 1, 'settle_method' => 'cash',
            'returns' => [['variant_id' => $old->id, 'qty' => 1, 'price' => 100]],
            'items' => [['variant_id' => $new->id, 'qty' => 1, 'price' => 250]],
        ]))->assertRedirect()->assertSessionHas('success');

        $x = Delivery::where('order_type', 'exchange')->firstOrFail();
        $this->assertSame($orig->id, $x->exchange_of_id);
        $this->assertEquals(150.0, $x->amount_due);                          // 250 new - 100 returned
        $this->assertSame('delivered', $x->status);                          // counter exchange completes at once
        $this->assertEquals(150.0, (float) $x->amount_paid);
        $this->assertEquals(0.0, $x->balance);
        $this->assertSame(10, $old->fresh()->stock_qty);                     // the returned cap is back on the shelf
        $this->assertSame(9, $new->fresh()->stock_qty);                      // the new hoodie went out
        $this->assertSame(1, $x->returned_items->count());
        $this->assertSame(-1, (int) $x->returned_items->first()->qty);
    }

    public function test_returning_more_than_is_taken_leaves_a_refund_that_can_be_recorded(): void
    {
        $m = $this->manager();
        $old = $this->variant('Jacket', 400, 10);
        $new = $this->variant('Socks', 20, 10);

        $this->actingAs($m)->post(route('manager.deliveries.store'), $this->exchange([
            'total_amount' => -380,
            'returns' => [['variant_id' => $old->id, 'qty' => 1, 'price' => 400]],
            'items' => [['variant_id' => $new->id, 'qty' => 1, 'price' => 20]],
        ]))->assertRedirect();

        $x = Delivery::firstOrFail();
        $this->assertEquals(-380.0, $x->amount_due);
        $this->assertEquals(380.0, $x->refund_due);
        $this->assertEquals(0.0, $x->balance);

        $page = $this->actingAs($m)->get(route('manager.deliveries.show', $x))->assertOk();
        $page->assertSee('Refund to customer')->assertSee('Returned by the customer')->assertSee('Record refund of GH₵ 380.00');

        $this->actingAs($m)->post(route('manager.deliveries.refund', $x), ['amount' => 380, 'method' => 'momo'])->assertSessionHas('success');
        $x->refresh();
        $this->assertEquals(-380.0, (float) $x->amount_paid);
        $this->assertEquals(0.0, $x->refund_due);
        $this->actingAs($m)->post(route('manager.deliveries.refund', $x), ['amount' => 1, 'method' => 'momo'])->assertStatus(422);   // nothing left to refund
    }

    public function test_a_pure_return_with_no_new_item_works_and_counter_settle_records_the_refund(): void
    {
        $m = $this->manager();
        $v = $this->variant('Shirt', 80, 5);
        $this->actingAs($m)->post(route('manager.deliveries.store'), $this->exchange([
            'total_amount' => -80, 'settle_now' => 1, 'settle_method' => 'cash', 'returns' => [['variant_id' => $v->id, 'qty' => 1, 'price' => 80]],
        ]))->assertRedirect()->assertSessionHasNoErrors();

        $x = Delivery::firstOrFail();
        $this->assertSame('delivered', $x->status);
        $this->assertSame(6, $v->fresh()->stock_qty);
        $this->assertEquals(-80.0, (float) $x->amount_paid);                 // cash handed back
        $this->assertEquals(0.0, $x->refund_due);
        $this->assertSame(0, (int) $x->items_sent);
    }

    public function test_sales_and_profit_net_out_the_returned_items(): void
    {
        $m = $this->manager();
        $v = $this->variant('Belt', 100, 10);                                // cost 50
        $this->sale($v, 3);                                                  // sold 300, cost 150
        $this->actingAs($m)->post(route('manager.deliveries.store'), $this->exchange([
            'total_amount' => -100, 'settle_now' => 1, 'returns' => [['variant_id' => $v->id, 'qty' => 1, 'price' => 100]],
        ]));

        $html = $this->actingAs($m)->get(route('manager.sales', ['period' => 'all']))->assertOk()->getContent();
        $this->assertStringContainsString('GH₵ 100.00', $html);               // profit: (300 - 100) - (150 - 50)
        $this->assertStringContainsString('GH₵ 200.00', $html);               // sales net of the return
        $this->assertSame(8, $v->fresh()->stock_qty);                         // 10 - 3 + 1
    }

    public function test_deleting_an_exchange_reverses_its_stock_both_ways(): void
    {
        $m = $this->manager();
        $old = $this->variant('Cap2', 100, 10);
        $new = $this->variant('Hoodie2', 250, 10);
        $this->actingAs($m)->post(route('manager.deliveries.store'), $this->exchange([
            'total_amount' => 150, 'returns' => [['variant_id' => $old->id, 'qty' => 2, 'price' => 100]], 'items' => [['variant_id' => $new->id, 'qty' => 1, 'price' => 250]],
        ]));
        $x = Delivery::firstOrFail();
        $this->assertSame(12, $old->fresh()->stock_qty);
        $this->assertSame(9, $new->fresh()->stock_qty);

        $this->actingAs($m)->delete(route('manager.deliveries.destroy', $x))->assertRedirect();
        $this->assertSame(10, $old->fresh()->stock_qty);
        $this->assertSame(10, $new->fresh()->stock_qty);
        $this->assertSame(0, Delivery::count());
    }

    public function test_cancelling_an_unfinished_exchange_undoes_both_sides(): void
    {
        $m = $this->manager();
        $old = $this->variant('Cap3', 100, 10);
        $new = $this->variant('Hoodie3', 250, 10);
        $this->actingAs($m)->post(route('manager.deliveries.store'), $this->exchange([
            'delivery_mode' => 'rider', 'total_amount' => 150, 'returns' => [['variant_id' => $old->id, 'qty' => 1, 'price' => 100]], 'items' => [['variant_id' => $new->id, 'qty' => 1, 'price' => 250]],
        ]));
        $x = Delivery::firstOrFail();
        $this->assertSame('pending', $x->status);

        $this->actingAs($m)->post(route('manager.deliveries.cancel', $x))->assertRedirect();
        $this->assertSame(10, $old->fresh()->stock_qty);
        $this->assertSame(10, $new->fresh()->stock_qty);
    }

    public function test_editing_an_exchange_keeps_both_lists(): void
    {
        $m = $this->manager();
        $old = $this->variant('Cap4', 100, 10);
        $new = $this->variant('Hoodie4', 250, 10);
        $this->actingAs($m)->post(route('manager.deliveries.store'), $this->exchange([
            'delivery_mode' => 'rider', 'total_amount' => 150, 'returns' => [['variant_id' => $old->id, 'qty' => 1, 'price' => 100]], 'items' => [['variant_id' => $new->id, 'qty' => 1, 'price' => 250]],
        ]));
        $x = Delivery::firstOrFail();

        $this->actingAs($m)->get(route('manager.deliveries.edit', $x))->assertOk()->assertSee('Items the customer is returning');
        $this->actingAs($m)->put(route('manager.deliveries.update', $x), $this->exchange([
            'delivery_mode' => 'rider', 'total_amount' => 50, 'returns' => [['variant_id' => $old->id, 'qty' => 2, 'price' => 100]], 'items' => [['variant_id' => $new->id, 'qty' => 1, 'price' => 250]],
        ]))->assertRedirect();

        $x->refresh()->load('items');
        $this->assertEquals(50.0, $x->amount_due);
        $this->assertSame(12, $old->fresh()->stock_qty);                     // two caps now back
        $this->assertSame(9, $new->fresh()->stock_qty);
        $this->assertSame(2, $x->items->count());
    }

    public function test_returns_are_ignored_on_normal_orders_and_a_sale_has_a_return_button(): void
    {
        $m = $this->manager();
        $v = $this->variant('Tee', 60, 10);
        $orig = $this->sale($v, 1);

        $this->actingAs($m)->post(route('manager.deliveries.store'), $this->exchange([
            'order_type' => 'standard', 'delivery_mode' => 'rider', 'total_amount' => 60, 'items' => [['variant_id' => $v->id, 'qty' => 1, 'price' => 60]],
            'returns' => [['variant_id' => $v->id, 'qty' => 5, 'price' => 60]],
        ]));
        $this->assertSame(0, Delivery::where('order_type', 'exchange')->count());
        $this->assertSame(8, $v->fresh()->stock_qty);                        // only the real sale took stock; the bogus return did nothing

        $this->actingAs($m)->get(route('manager.deliveries.show', $orig))->assertSee('Return / exchange')->assertSee(route('manager.deliveries.create', ['exchange_of' => $orig->id]), false);
        $this->actingAs($m)->get(route('manager.sales', ['period' => 'all']))->assertSee('Return</a>', false);

        $form = $this->actingAs($m)->get(route('manager.deliveries.create', ['exchange_of' => $orig->id]))->assertOk();
        $form->assertSee('Exchange for ' . $orig->reference)->assertSee('"variant_id":' . $v->id, false)->assertSee('value="Kofi"', false);
    }

    public function test_only_managers_can_refund(): void
    {
        $x = Delivery::create(['reference' => Delivery::newReference(), 'customer_id' => \App\Models\Customer::create(['name' => 'A', 'phone' => '0240000000'])->id, 'location' => 'x', 'package_description' => 'p',
            'delivery_date' => today(), 'total_amount' => -50, 'items_sent' => 0, 'status' => 'delivered', 'order_type' => 'exchange']);
        $rider = User::factory()->create(['role' => 'rider']);
        $this->actingAs($rider)->post(route('manager.deliveries.refund', $x), ['amount' => 50, 'method' => 'cash'])->assertForbidden();
    }
}
