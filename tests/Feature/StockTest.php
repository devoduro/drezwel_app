<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StockTest extends TestCase
{
    use RefreshDatabase;

    private function manager(array $over = []): User
    {
        return User::factory()->create(['role' => 'manager'] + $over);
    }

    private function product(array $over = []): Product
    {
        $p = Product::create($over + ['name' => 'Baggy jeans', 'cost_price' => 100, 'selling_price' => 200, 'stock_qty' => 10, 'reorder_level' => 3]);
        $p->variants()->create(['stock_qty' => $p->stock_qty]); // the plain variant that carries the stock

        return $p;
    }

    private function order(User $m, array $items, array $over = [])
    {
        return $this->actingAs($m)->post(route('manager.deliveries.store'), $over + [
            'customer_name' => 'Bello', 'customer_phone' => '0241266765', 'location' => 'Newtown', 'delivery_mode' => 'rider', 'order_type' => 'standard',
            'delivery_date' => today()->toDateString(), 'total_amount' => 0, 'items' => $items,
        ]);
    }

    public function test_order_with_products_takes_stock_off_and_cancel_puts_it_back(): void
    {
        Http::fake();
        $m = $this->manager();
        $p = $this->product();

        $this->order($m, [['variant_id' => $p->variants()->first()->id, 'qty' => 4, 'price' => 200]], ['total_amount' => 800])->assertSessionHasNoErrors();
        $d = Delivery::first();
        $this->assertSame(6, $p->fresh()->stock_qty);
        $this->assertSame(4, $d->items_sent);
        $this->assertStringContainsString('Baggy jeans x4', $d->package_description);
        $this->assertEquals(100, $d->items->first()->unit_cost); // cost frozen on the order

        $this->actingAs($m)->post(route('manager.deliveries.cancel', $d));
        $this->assertSame(10, $p->fresh()->stock_qty);
        $this->actingAs($m)->post(route('manager.deliveries.reopen', $d));
        $this->assertSame(6, $p->fresh()->stock_qty);
    }

    public function test_cannot_order_more_than_in_stock_and_nothing_is_saved(): void
    {
        $m = $this->manager();
        $p = $this->product(['stock_qty' => 2]);

        $this->order($m, [['variant_id' => $p->variants()->first()->id, 'qty' => 5, 'price' => 200]], ['total_amount' => 1000])->assertSessionHasErrors('qty');
        $this->assertSame(0, Delivery::count());
        $this->assertSame(2, $p->fresh()->stock_qty);
    }

    public function test_editing_an_order_rebalances_stock(): void
    {
        Http::fake();
        $m = $this->manager();
        $p = $this->product();
        $this->order($m, [['variant_id' => $p->variants()->first()->id, 'qty' => 4, 'price' => 200]], ['total_amount' => 800]);
        $d = Delivery::first();

        $this->actingAs($m)->put(route('manager.deliveries.update', $d), [
            'customer_name' => 'Bello', 'customer_phone' => '0241266765', 'location' => 'Newtown', 'delivery_mode' => 'rider', 'order_type' => 'standard',
            'delivery_date' => today()->toDateString(), 'total_amount' => 400, 'items' => [['variant_id' => $p->variants()->first()->id, 'qty' => 2, 'price' => 200]],
        ])->assertSessionHasNoErrors();
        $this->assertSame(8, $p->fresh()->stock_qty);
    }

    public function test_low_stock_texts_the_manager_once_and_rearms_after_restock(): void
    {
        config(['services.speso_sms.api_key' => 'k']);
        Http::fake(['*' => Http::response(['success' => true])]);
        $m = $this->manager(['phone' => '0241112222']);
        $p = $this->product(['stock_qty' => 5, 'reorder_level' => 3]);

        $this->order($m, [['variant_id' => $p->variants()->first()->id, 'qty' => 2, 'price' => 200]], ['total_amount' => 400]); // 5 -> 3: at the level
        $this->assertSame(1, SmsLog::where('purpose', 'stock_alert')->count());
        $this->order($m, [['variant_id' => $p->variants()->first()->id, 'qty' => 1, 'price' => 200]], ['total_amount' => 200, 'customer_phone' => '0200000001']); // 3 -> 2: no second text
        $this->assertSame(1, SmsLog::where('purpose', 'stock_alert')->count());

        $this->actingAs($m)->post(route('manager.products.adjust', $p), ['variant_id' => $p->variants()->first()->id, 'action' => 'purchase', 'qty' => 20, 'unit_cost' => 110])->assertSessionHasNoErrors();
        $this->assertNull($p->fresh()->low_alerted_at);
        $this->assertEquals(110, $p->fresh()->cost_price);
        $this->order($m, [['variant_id' => $p->variants()->first()->id, 'qty' => 19, 'price' => 200]], ['total_amount' => 3800, 'customer_phone' => '0200000002']); // dips again
        $this->assertSame(2, SmsLog::where('purpose', 'stock_alert')->count());
    }

    public function test_stock_cannot_go_negative_through_adjustments(): void
    {
        $m = $this->manager();
        $p = $this->product(['stock_qty' => 2]);
        $this->actingAs($m)->post(route('manager.products.adjust', $p), ['variant_id' => $p->variants()->first()->id, 'action' => 'damage', 'qty' => 5])->assertSessionHasErrors('qty');
        $this->assertSame(2, $p->fresh()->stock_qty);
    }

    public function test_product_crud_with_opening_stock_and_pages_render(): void
    {
        $m = $this->manager();
        $this->actingAs($m)->post(route('manager.products.store'), ['name' => 'Sneaker', 'sku' => 'SN-1', 'cost_price' => 150, 'selling_price' => 300, 'reorder_level' => 4, 'opening_stock' => 12])->assertSessionHasNoErrors();
        $p = Product::first();
        $this->assertSame(12, $p->stock_qty);
        $this->assertSame(1, $p->movements()->count());

        foreach (['manager.products.index', 'manager.products.create', 'manager.reports'] as $r) {
            $this->actingAs($m)->get(route($r))->assertOk();
        }
        $this->actingAs($m)->get(route('manager.products.show', $p))->assertOk()->assertSee('Opening stock');
        $this->actingAs($m)->get(route('manager.products.edit', $p))->assertOk();
        $this->actingAs($m)->get(route('manager.deliveries.create'))->assertOk()->assertSee('Sneaker');
    }

    public function test_report_numbers_and_csv_exports(): void
    {
        Http::fake();
        $m = $this->manager();
        $p = $this->product();
        $this->order($m, [['variant_id' => $p->variants()->first()->id, 'qty' => 3, 'price' => 200]], ['total_amount' => 600]);

        $res = $this->actingAs($m)->get(route('manager.reports', ['period' => 'all']))->assertOk();
        $res->assertSee('GH₵ 600.00');          // revenue and product sales
        $res->assertSee('GH₵ 300.00');          // cost of goods sold: 3 x 100  /  gross profit 300

        foreach (['orders', 'sales', 'stock'] as $t) {
            $csv = $this->actingAs($m)->get(route('manager.reports.export', ['type' => $t, 'period' => 'all']));
            $csv->assertOk();
            $this->assertStringContainsString('text/csv', $csv->headers->get('content-type'));
        }
        $this->actingAs($m)->get(route('manager.reports.export', ['type' => 'nope']))->assertNotFound();
    }

    public function test_customer_pays_goods_plus_fee_rider_hands_in_all_and_gets_fee_back(): void
    {
        config(['services.speso_sms.api_key' => 'k']);
        Http::fake(['*' => Http::response(['success' => true])]);
        $m = $this->manager();
        $rider = User::factory()->create(['role' => 'rider', 'name' => 'Joe']);
        $c = Customer::create(['name' => 'Ama', 'phone' => '0244000111']);
        $d = Delivery::create([
            'reference' => 'DEL-T-1', 'customer_id' => $c->id, 'location' => 'Osu', 'package_description' => 'Dress', 'delivery_date' => today(),
            'total_amount' => 300, 'delivery_fee' => 30, 'items_sent' => 1, 'status' => 'assigned', 'rider_id' => $rider->id,
        ]);
        $this->assertEquals(330, $d->amount_due);
        $this->assertEquals(330, $d->balance);

        // rider starts, customer code goes out, rider collects the full 330
        $this->actingAs($rider)->post(route('rider.start', $d));
        $code = null;
        Http::assertSent(function ($req) use (&$code) {
            if (preg_match('/code is (\d{4})/', $req['message'] ?? '', $mm)) {
                $code = $mm[1];
            }

            return true;
        });
        $this->actingAs($rider)->post(route('rider.confirm', $d), ['code' => $code, 'cash' => 999])->assertSessionHasNoErrors(); // capped at what is owed
        $d->refresh();
        $this->assertEquals(330, $d->amount_paid);
        $this->assertEquals(0, $d->balance);

        $c1 = $rider->custody();
        $this->assertEquals(330, $c1['cash_held']);   // hands in goods + fee
        $this->assertEquals(30, $c1['fees_owed']);    // the business owes the fee back

        $this->actingAs($m)->post(route('manager.remittances.clear', $rider));
        $this->assertEquals(0, $rider->custody()['cash_held']);
        $this->assertEquals(30, $rider->custody()['fees_owed']); // clearing the cash does not pay the fee

        $this->actingAs($m)->post(route('manager.remittances.pay-fees', $rider))->assertSessionHas('success');
        $c2 = $rider->custody();
        $this->assertEquals(0, $c2['fees_owed']);
        $this->assertEquals(30, $c2['fees_paid']);
        $this->assertSame('paid', $d->fresh()->rider_fee_status);
        // paying twice does nothing
        $this->actingAs($m)->post(route('manager.remittances.pay-fees', $rider));
        $this->assertEquals(30, $rider->custody()['fees_paid']);

        // report: billed 330, but the fee is pass-through, not profit
        $res = $this->actingAs($m)->get(route('manager.reports', ['period' => 'all']))->assertOk();
        $res->assertSee('GH₵ 330.00')->assertSee('Still owed to riders');
        $this->actingAs($m)->get(route('manager.debtors'))->assertOk();
        $this->actingAs($m)->get(route('manager.customers.index'))->assertOk()->assertSee('330.00');
        $this->actingAs($m)->get(route('manager.remittances'))->assertOk();
        $this->actingAs($rider)->get(route('rider.stats'))->assertOk()->assertSee('My delivery fees');
    }

    public function test_riders_cannot_reach_stock_or_reports(): void
    {
        $r = User::factory()->create(['role' => 'rider']);
        foreach (['manager.products.index', 'manager.reports'] as $route) {
            $this->actingAs($r)->get(route($route))->assertForbidden();
        }
    }
}
