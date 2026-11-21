<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersSalesTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    private function order(string $name, array $o = []): Delivery
    {
        $c = Customer::firstOrCreate(['phone' => $o['phone'] ?? '0244' . random_int(100000, 999999)], ['name' => $name]);
        unset($o['phone']);

        return Delivery::create($o + [
            'reference' => Delivery::newReference(), 'customer_id' => $c->id, 'location' => 'Osu', 'package_description' => "{$name}-pkg",
            'delivery_date' => today(), 'total_amount' => 100, 'delivery_fee' => 0, 'items_sent' => 1, 'status' => 'delivered', 'source' => 'manager',
        ]);
    }

    public function test_sidebar_has_orders_and_sales_and_a_badge_for_new_web_orders(): void
    {
        $m = $this->manager();
        $res = $this->actingAs($m)->get(route('manager.dashboard'))->assertOk()->assertSee('Orders')->assertSee('Sales');
        $this->assertStringContainsString('href="' . route('manager.orders') . '"', $res->getContent());
        $this->assertStringContainsString('href="' . route('manager.sales') . '"', $res->getContent());
        $this->assertStringNotContainsString('New website / phone orders waiting', $res->getContent());   // no badge when nothing is waiting

        $this->order('Web Ama', ['source' => 'online', 'status' => 'pending']);
        $this->order('Phone Kofi', ['source' => 'phone', 'status' => 'pending']);
        $this->order('Staff Esi', ['source' => 'manager', 'status' => 'pending']);                 // not counted: staff made it themselves
        $this->assertStringContainsString('New website / phone orders waiting', $this->actingAs($m)->get(route('manager.dashboard'))->getContent());
    }

    public function test_orders_page_lists_every_channel_with_status_tabs_and_filters(): void
    {
        $m = $this->manager();
        $this->order('Web Ama', ['source' => 'online', 'status' => 'pending']);
        $this->order('Pos Joe', ['source' => 'pos', 'status' => 'delivered']);
        $this->order('Gone Kofi', ['source' => 'phone', 'status' => 'cancelled']);

        $this->actingAs($m)->get(route('manager.orders'))->assertOk()->assertSee('Web Ama')->assertSee('Pos Joe')->assertSee('Gone Kofi')
            ->assertSee('Website')->assertSee('Shop counter')->assertSeeText('1 new website / phone order is waiting for a rider')
            ->assertSee('Orders today')->assertSee('Invoice');

        $this->actingAs($m)->get(route('manager.orders', ['status' => 'pending']))->assertSee('Web Ama')->assertDontSee('Pos Joe')->assertDontSee('Gone Kofi');
        $this->actingAs($m)->get(route('manager.orders', ['source' => 'pos']))->assertSee('Pos Joe')->assertDontSee('Web Ama');
        $this->actingAs($m)->get(route('manager.orders', ['q' => 'Kofi']))->assertSee('Gone Kofi')->assertDontSee('Web Ama');
        $this->actingAs($m)->get(route('manager.orders', ['from' => today()->addDay()->toDateString()]))->assertDontSee('Web Ama');   // date range
    }

    public function test_orders_page_shows_walk_in_customers_by_name_and_cancelled_orders_owe_nothing(): void
    {
        $m = $this->manager();
        $this->order('Counter', ['phone' => SalesService::WALK_IN_PHONE, 'source' => 'pos']);
        $this->order('Cancelled', ['status' => 'cancelled', 'total_amount' => 500]);
        $res = $this->actingAs($m)->get(route('manager.orders'))->assertOk()->assertSee('Walk-in customer');
        $this->assertStringNotContainsString('500.00</td>' . "\n" . '                <td class="text-right text-red-600', $res->getContent());
    }

    public function test_sales_page_totals_channels_and_payment_methods(): void
    {
        $m = $this->manager();
        $web = $this->order('Web', ['source' => 'online', 'total_amount' => 300, 'delivery_fee' => 20, 'amount_paid' => 120]);
        $web->payments()->create(['amount' => 120, 'method' => 'momo', 'paid_at' => now()]);
        $pos = $this->order('Pos', ['source' => 'pos', 'total_amount' => 200, 'amount_paid' => 200]);
        $pos->payments()->create(['amount' => 200, 'method' => 'cash', 'paid_at' => now()]);
        $this->order('Cancelled', ['status' => 'cancelled', 'total_amount' => 9999]);              // never counted

        $res = $this->actingAs($m)->get(route('manager.sales', ['period' => 'all']))->assertOk();
        $res->assertSee('GH₵ 500.00')                  // goods sold: 300 and 200 (the GH₵ 20 delivery fee is not a sale)
            ->assertSee('GH₵ 300.00')                  // received for the goods: 120 - 20 fee = 100, and 200
            ->assertSee('GH₵ 200.00')                  // still owed for the goods: 500 - 300
            ->assertSee('Delivery fees')->assertSee('GH₵ 20.00')   // the fee is shown separately
            ->assertSee('Sales by channel')->assertSee('Top performing products')->assertDontSee('9,999')->assertDontSee('9999');
        $res->assertSee($web->reference)->assertSee($pos->reference);

        // filters
        $this->actingAs($m)->get(route('manager.sales', ['period' => 'all', 'source' => 'pos']))->assertSee($pos->reference)->assertDontSee($web->reference)->assertSee('GH₵ 200.00');
        $this->actingAs($m)->get(route('manager.sales', ['period' => 'all', 'method' => 'momo']))->assertSee($web->reference)->assertDontSee($pos->reference);
        $this->actingAs($m)->get(route('manager.sales', ['period' => 'today']))->assertOk();
    }

    public function test_sales_page_shows_product_profit_and_a_csv_link(): void
    {
        $m = $this->manager();
        $p = Product::create(['name' => 'Jeans', 'slug' => 'jeans', 'cost_price' => 60, 'selling_price' => 100, 'stock_qty' => 5]);
        $v = $p->variants()->create(['stock_qty' => 5]);
        $d = $this->order('Buyer', ['total_amount' => 200, 'amount_paid' => 200]);
        $d->items()->create(['product_id' => $p->id, 'variant_id' => $v->id, 'qty' => 2, 'unit_price' => 100, 'unit_cost' => 60]);

        $this->actingAs($m)->get(route('manager.sales', ['period' => 'all']))->assertOk()->assertSee('GH₵ 80.00')   // 200 - 120 cost
            ->assertSee('Download CSV')->assertSee('reports/export/orders', false);
    }

    public function test_top_products_are_ranked_by_profit_and_by_units_on_sales_and_dashboard(): void
    {
        $m = $this->manager();
        $mk = function (string $name, float $price, float $cost, int $qty) {
            $p = Product::create(['name' => $name, 'slug' => strtolower($name), 'cost_price' => $cost, 'selling_price' => $price, 'stock_qty' => 50]);
            $v = $p->variants()->create(['stock_qty' => 50]);
            $this->order("{$name}-buyer", ['total_amount' => $price * $qty, 'amount_paid' => $price * $qty])->items()->create(['product_id' => $p->id, 'variant_id' => $v->id, 'qty' => $qty, 'unit_price' => $price, 'unit_cost' => $cost]);
        };
        $mk('Blazer', 500, 200, 2);     // profit 600, 2 units
        $mk('Socks', 10, 5, 30);        // profit 150, 30 units
        $mk('Cap', 40, 30, 4);          // profit 40, 4 units
        $cancelled = $this->order('Gone', ['status' => 'cancelled', 'total_amount' => 9000]);
        $p = Product::create(['name' => 'Ghost', 'slug' => 'ghost', 'cost_price' => 1, 'selling_price' => 900, 'stock_qty' => 500]);
        $cancelled->items()->create(['product_id' => $p->id, 'variant_id' => $p->variants()->create(['stock_qty' => 500])->id, 'qty' => 10, 'unit_price' => 900, 'unit_cost' => 1]);

        foreach ([route('manager.sales', ['period' => 'all']), route('manager.dashboard')] as $url) {
            $html = $this->actingAs($m)->get($url)->assertOk()->assertSee('Most profit')->assertSee('Most units sold')->assertDontSee('Ghost')->getContent();
            $html = substr($html, strpos($html, 'Top performing products'));   // ignore customer names etc. above the panel
            $profit = strpos($html, 'Most profit');
            $units = strpos($html, 'Most units sold');
            $this->assertLessThan(strpos($html, 'Socks', $profit), strpos($html, 'Blazer', $profit));   // Blazer out-earns Socks
            $this->assertLessThan(strpos($html, 'Blazer', $units), strpos($html, 'Socks', $units));     // Socks outsell Blazer
            $this->assertStringContainsString('GH₵ 600.00', $html);
            $this->assertStringContainsString('30 units', $html);
        }
    }

    public function test_only_managers_can_open_orders_and_sales(): void
    {
        $rider = User::factory()->create(['role' => 'rider']);
        foreach (['manager.orders', 'manager.sales'] as $r) {
            $this->actingAs($rider)->get(route($r))->assertForbidden();
        }
        $this->app['auth']->forgetGuards();
        $this->get(route('manager.orders'))->assertRedirect('/login');
        $this->get(route('manager.sales'))->assertRedirect('/login');
    }

    public function test_manager_screens_have_phone_tabs_with_a_more_button_and_the_full_menu_stays_reachable(): void
    {
        $m = $this->manager();
        $this->order('Web Ama', ['source' => 'online', 'status' => 'pending']);

        $html = $this->actingAs($m)->get(route('manager.orders'))->assertOk()->assertSee('aria-label="Main tabs"', false)->assertSee('More')->getContent();
        foreach (['manager.dashboard', 'manager.orders', 'manager.deliveries.index', 'manager.products.index'] as $r) {
            $this->assertStringContainsString('<nav class="lg:hidden fixed bottom-0', $html);
            $this->assertStringContainsString('href="' . route($r) . '" class="relative py-2', $html);       // each tab
        }
        $this->assertStringContainsString('id="menuBtn"', $html);                                           // "More" opens the drawer
        $this->assertStringContainsString('id="sidebar"', $html);                                           // which still lists everything
        $this->assertStringNotContainsString('aria-label="Open menu"', $html);                              // the old hamburger is gone
    }

    public function test_only_delivered_orders_that_brought_in_money_are_sales_and_the_page_shows_location(): void
    {
        $m = $this->manager();
        $order = $this->order('Waiting Ama', ['source' => 'phone', 'status' => 'pending', 'total_amount' => 200, 'location' => 'Madina Estate']);
        $sales = fn () => $this->actingAs($m)->get(route('manager.sales', ['period' => 'all']))->assertOk();

        $sales()->assertDontSee($order->reference)->assertSee('No delivered and paid sales');
        $this->actingAs($m)->get(route('manager.orders'))->assertSee($order->reference);                    // still an order
        $this->actingAs($m)->get(route('manager.deliveries.index'))->assertSee($order->reference);          // and still on the Deliveries page

        // money recorded, but it has not been delivered yet: not a sale
        $this->actingAs($m)->post(route('manager.deliveries.payment', $order), ['amount' => 50, 'method' => 'cash'])->assertSessionHas('success');
        $sales()->assertDontSee($order->reference);

        // out for delivery, undelivered or cancelled: still not sales, even with money recorded
        foreach (['out_for_delivery', 'undelivered', 'cancelled'] as $status) {
            $order->update(['status' => $status]);
            $sales()->assertDontSee($order->reference);
        }

        // delivered and paid (part payment): now it is a sale
        $order->update(['status' => 'delivered']);
        $html = $sales()->assertSee($order->reference)->assertSee('Madina Estate')->assertSee('GH₵ 150.00')->getContent();   // GH₵ 150 still owed
        $this->assertStringContainsString('<th>Location</th>', $html);
        $this->assertStringNotContainsString('<th>Channel</th>', $html);

        // delivered but nothing paid: not a sale
        $unpaid = $this->order('Unpaid Kojo', ['status' => 'delivered', 'total_amount' => 90]);
        $sales()->assertDontSee($unpaid->reference);
    }

    public function test_deliveries_dashboard_shows_expected_revenue_while_sales_shows_only_actual_sales(): void
    {
        $m = $this->manager();
        $this->order('Open A', ['status' => 'pending', 'total_amount' => 300, 'delivery_fee' => 20]);                       // expected 320
        $this->order('Open B', ['status' => 'out_for_delivery', 'total_amount' => 200, 'amount_paid' => 50]);               // expected 150 more
        $this->order('Failed', ['status' => 'undelivered', 'total_amount' => 999]);                                          // not expected
        $this->order('Gone', ['status' => 'cancelled', 'total_amount' => 888]);
        $this->order('Done', ['status' => 'delivered', 'total_amount' => 400, 'amount_paid' => 400]);                       // actual 400

        $res = $this->actingAs($m)->get(route('manager.deliveries.index'))->assertOk();
        $res->assertSee('Expected revenue')->assertSee('GH₵ 450.00')    // goods still to collect: 300 + 150 (the GH₵ 20 fee goes to the rider)
            ->assertSee('GH₵ 500.00')                                    // value of the open deliveries' goods: 300 + 200
            ->assertSee('GH₵ 400.00')                                    // actual sales
            ->assertDontSee('GH₵ 999.00')->assertDontSee('GH₵ 888.00')->assertDontSee('GH₵ 1,');   // undelivered and cancelled orders are not counted
    }

    public function test_counter_sales_do_not_appear_on_the_deliveries_page_but_phone_orders_do(): void
    {
        $m = $this->manager();
        $counter = $this->order('Counter Joe', ['source' => 'pos', 'status' => 'delivered', 'total_amount' => 120, 'amount_paid' => 120]);
        $phone = $this->order('Phone Ama', ['source' => 'phone', 'status' => 'pending', 'total_amount' => 80]);
        $web = $this->order('Web Kojo', ['source' => 'online', 'status' => 'pending', 'total_amount' => 60]);

        $this->actingAs($m)->get(route('manager.deliveries.index'))->assertOk()->assertSee($phone->reference)->assertSee($web->reference)->assertDontSee($counter->reference);
        $this->actingAs($m)->get(route('manager.deliveries.index', ['status' => 'delivered']))->assertDontSee($counter->reference);
        $this->actingAs($m)->get(route('manager.deliveries.index', ['q' => 'Counter Joe']))->assertDontSee($counter->reference);

        // they are still sales and orders, and can still be opened directly
        $this->actingAs($m)->get(route('manager.sales', ['period' => 'all']))->assertSee($counter->reference);
        $this->actingAs($m)->get(route('manager.orders'))->assertSee($counter->reference);
        $this->actingAs($m)->get(route('manager.deliveries.show', $counter))->assertOk();
    }

    public function test_recording_the_full_payment_completes_the_delivery_without_a_rider_and_moves_it_to_sales(): void
    {
        $m = $this->manager();
        $order = $this->order('Kingsley', ['source' => 'phone', 'status' => 'pending', 'total_amount' => 180, 'delivery_fee' => 0, 'items_sent' => 1]);
        $sales = fn () => $this->actingAs($m)->get(route('manager.sales', ['period' => 'all']))->assertOk();
        $this->assertNull($order->rider_id);

        $this->actingAs($m)->get(route('manager.deliveries.show', $order))->assertSee('complete the delivery and move it to Sales');

        // a part payment records the money but does not finish anything
        $this->actingAs($m)->post(route('manager.deliveries.payment', $order), ['amount' => 80, 'method' => 'cash', 'complete' => 1]);
        $this->assertSame('pending', $order->fresh()->status);
        $sales()->assertDontSee($order->reference);

        // the rest of the money: delivered, even with no rider, and now a sale
        $this->actingAs($m)->post(route('manager.deliveries.payment', $order), ['amount' => 100, 'method' => 'momo', 'complete' => 1])
            ->assertSessionHas('success', 'Payment recorded. The order is complete and now shows in Sales.');
        $order->refresh();
        $this->assertSame('delivered', $order->status);
        $this->assertNotNull($order->delivered_at);
        $this->assertSame(1, (int) $order->items_kept);
        $this->assertTrue($order->events()->where('type', 'delivered')->exists());
        $sales()->assertSee($order->reference);
    }

    public function test_the_complete_tick_box_can_be_left_off_and_cancelled_orders_stay_cancelled(): void
    {
        $m = $this->manager();
        $advance = $this->order('Prepaid', ['source' => 'phone', 'status' => 'pending', 'total_amount' => 100]);
        $this->actingAs($m)->post(route('manager.deliveries.payment', $advance), ['amount' => 100, 'method' => 'cash', 'complete' => 0]);   // unticked: payment in advance
        $this->assertSame('pending', $advance->fresh()->status);
        $this->actingAs($m)->post(route('manager.deliveries.payment', $advance), ['amount' => 0.01, 'method' => 'cash'])->assertSessionHasErrors();   // nothing owing now

        $out = $this->order('On the road', ['source' => 'phone', 'status' => 'out_for_delivery', 'total_amount' => 60, 'rider_id' => User::factory()->create(['role' => 'rider'])->id]);
        $this->actingAs($m)->post(route('manager.deliveries.payment', $out), ['amount' => 60, 'method' => 'cash', 'complete' => 1]);
        $this->assertSame('delivered', $out->fresh()->status);                                                  // a rider on the road: still completes

        $gone = $this->order('Cancelled', ['source' => 'phone', 'status' => 'cancelled', 'total_amount' => 40]);
        $this->actingAs($m)->post(route('manager.deliveries.payment', $gone), ['amount' => 40, 'method' => 'cash', 'complete' => 1]);
        $this->assertSame('cancelled', $gone->fresh()->status);
    }

    public function test_rider_names_on_the_cash_and_items_page_link_to_their_orders_and_deliveries(): void
    {
        $m = $this->manager();
        $rider = User::factory()->create(['role' => 'rider', 'name' => 'Daniel Rider']);

        $this->actingAs($m)->get(route('manager.remittances'))->assertOk()->assertSee(route('manager.riders.show', $rider), false);
        $this->actingAs($m)->get(route('manager.riders.show', $rider))->assertOk()->assertSee('Daniel Rider');
    }

    public function test_a_rider_with_assigned_orders_is_not_shown_as_cleared(): void
    {
        $m = $this->manager();
        $rider = User::factory()->create(['role' => 'rider', 'name' => 'Busy Joe']);

        // nothing held, nothing assigned: cleared
        $this->assertTrue($rider->custody()['settled']);
        $this->actingAs($m)->get(route('manager.riders.show', $rider))->assertOk()->assertSee('Cleared');

        // handed everything in but still has two orders on the go: not cleared
        $this->order('Open one', ['source' => 'phone', 'status' => 'assigned', 'rider_id' => $rider->id]);
        $this->order('Open two', ['source' => 'phone', 'status' => 'out_for_delivery', 'rider_id' => $rider->id]);
        $c = $rider->fresh()->custody();
        $this->assertTrue($c['cleared']);                                       // nothing owed in cash or items
        $this->assertFalse($c['settled']);                                      // but not settled
        $this->assertSame(2, $c['open_runs']);

        $this->actingAs($m)->get(route('manager.riders.show', $rider))->assertSee('2 open orders to finish');
        $page = $this->actingAs($m)->get(route('manager.remittances'))->assertOk()->assertSee('Has open orders')->getContent();
        $this->assertStringNotContainsString('>Cleared<', substr($page, strpos($page, 'Busy Joe'), 700));
    }

    public function test_rider_page_shows_the_delivery_fee_on_each_order_and_the_totals(): void
    {
        $m = $this->manager();
        $rider = User::factory()->create(['role' => 'rider', 'name' => 'Fee Rider']);
        $this->order('Done one', ['status' => 'delivered', 'rider_id' => $rider->id, 'delivery_fee' => 40]);
        $this->order('Done two', ['status' => 'delivered', 'rider_id' => $rider->id, 'delivery_fee' => 25]);
        $this->order('On the road', ['status' => 'out_for_delivery', 'rider_id' => $rider->id, 'delivery_fee' => 30]);
        $this->order('Cancelled', ['status' => 'cancelled', 'rider_id' => $rider->id, 'delivery_fee' => 99]);

        $html = $this->actingAs($m)->get(route('manager.riders.show', $rider))->assertOk()
            ->assertSee('Delivery fee')->assertSee('Fee received')->assertSee('Total delivery fees')->getContent();
        $this->assertStringContainsString('30.00', $html);                       // the fee on the order still on the road is visible
        $this->assertMatchesRegularExpression('/Total delivery fees.*?95\.00.*?65\.00/s', $html);   // charged 40+25+30, received 40+25
    }

    public function test_sales_figures_use_the_goods_only_and_show_the_delivery_fee_beside_them(): void
    {
        $m = $this->manager();
        $o = $this->order('Kingsley', ['source' => 'phone', 'status' => 'delivered', 'total_amount' => 180, 'delivery_fee' => 40, 'amount_paid' => 220]);
        $o->payments()->create(['amount' => 220, 'method' => 'cash', 'paid_at' => now()]);

        $this->assertEquals(180.0, $o->goods_received);                       // 220 paid - 40 fee
        $this->assertEquals(0.0, $o->goods_balance);

        $html = $this->actingAs($m)->get(route('manager.sales', ['period' => 'all']))->assertOk()->assertSee('Goods total')->assertSee('Delivery fee')->getContent();
        $this->assertStringContainsString('GH₵ 180.00', $html);                // sales and money received are the goods, not 220
        $this->assertStringNotContainsString('GH₵ 220.00', $html);
        $this->assertStringContainsString('GH₵ 40.00', $html);                 // the fee is indicated on its own tile
        $this->assertStringContainsString('>40.00</td>', $html);               // and on the row
    }

    public function test_reports_and_dashboard_count_the_goods_only_and_show_delivery_fees_separately(): void
    {
        $m = $this->manager();
        $o = $this->order('Fee customer', ['source' => 'phone', 'status' => 'delivered', 'total_amount' => 300, 'delivery_fee' => 50, 'amount_paid' => 350]);
        $o->payments()->create(['amount' => 350, 'method' => 'cash', 'paid_at' => now()]);
        $unpaid = $this->order('Owes goods', ['source' => 'phone', 'status' => 'delivered', 'total_amount' => 100, 'delivery_fee' => 20, 'amount_paid' => 20]);   // paid only the fee
        $this->assertEquals(0.0, $unpaid->goods_received);
        $this->assertEquals(100.0, $unpaid->goods_balance);

        $report = $this->actingAs($m)->get(route('manager.reports', ['period' => 'all']))->assertOk()->assertSee('Sales (goods)')->assertSee('Delivery fees GH₵ 70.00 go to riders')->getContent();
        $this->assertStringContainsString('GH₵ 400.00', $report);              // sales: 300 + 100, not 470
        $this->assertStringContainsString('GH₵ 300.00', $report);              // collected for the goods: 300 + 0
        $this->assertStringContainsString('GH₵ 100.00', $report);              // still owed for the goods
        $this->assertStringNotContainsString('GH₵ 470.00', $report);

        $dash = $this->actingAs($m)->get(route('manager.dashboard', ['period' => 'all']))->assertOk()->assertSee('Total sales (goods)')->assertSee('delivery fees GH₵ 70.00 go to riders')->getContent();
        $this->assertStringContainsString('GH₵ 400.00', $dash);
        $this->assertStringNotContainsString('GH₵ 470.00', $dash);
    }

    public function test_orders_can_be_listed_oldest_first_or_newest_first(): void
    {
        $m = $this->manager();
        $a = $this->order('First Ama', ['source' => 'phone', 'status' => 'pending', 'created_at' => now()->subDays(2)]);
        $b = $this->order('Second Kofi', ['source' => 'phone', 'status' => 'pending', 'created_at' => now()->subDay()]);
        $c = $this->order('Third Esi', ['source' => 'phone', 'status' => 'pending', 'created_at' => now()]);
        $refs = fn ($html) => array_values(array_filter([strpos($html, $a->reference), strpos($html, $b->reference), strpos($html, $c->reference)], fn ($v) => $v !== false));

        $desc = $this->actingAs($m)->get(route('manager.orders', ['status' => 'pending']))->assertOk()->getContent();
        $this->assertTrue(strpos($desc, $c->reference) < strpos($desc, $b->reference) && strpos($desc, $b->reference) < strpos($desc, $a->reference));   // newest first by default

        $asc = $this->actingAs($m)->get(route('manager.orders', ['status' => 'pending', 'dir' => 'asc']))->assertOk()->assertSee('Oldest first')->getContent();
        $this->assertTrue(strpos($asc, $a->reference) < strpos($asc, $b->reference) && strpos($asc, $b->reference) < strpos($asc, $c->reference));       // oldest first

        $this->actingAs($m)->get(route('manager.orders', ['status' => 'pending', 'dir' => 'nonsense']))->assertOk();                                  // anything else falls back to newest first
        $this->assertStringContainsString('dir=desc', $this->actingAs($m)->get(route('manager.orders', ['status' => 'pending', 'dir' => 'asc']))->getContent());   // the header toggles back
    }

    public function test_deliveries_can_be_listed_oldest_first_or_newest_first(): void
    {
        $m = $this->manager();
        $a = $this->order('Old Ama', ['source' => 'phone', 'status' => 'pending', 'delivery_date' => today()->subDays(2)]);
        $b = $this->order('Mid Kofi', ['source' => 'phone', 'status' => 'pending', 'delivery_date' => today()->subDay()]);
        $c = $this->order('New Esi', ['source' => 'phone', 'status' => 'pending', 'delivery_date' => today()]);

        $desc = $this->actingAs($m)->get(route('manager.deliveries.index'))->assertOk()->getContent();
        $this->assertTrue(strpos($desc, $c->reference) < strpos($desc, $b->reference) && strpos($desc, $b->reference) < strpos($desc, $a->reference));   // newest first by default

        $asc = $this->actingAs($m)->get(route('manager.deliveries.index', ['dir' => 'asc']))->assertOk()->assertSee('Oldest first')->getContent();
        $this->assertTrue(strpos($asc, $a->reference) < strpos($asc, $b->reference) && strpos($asc, $b->reference) < strpos($asc, $c->reference));       // oldest first
        $this->assertStringContainsString('dir=desc', $asc);                                                                                            // the Date header toggles back
        $this->actingAs($m)->get(route('manager.deliveries.index', ['dir' => 'x', 'status' => 'pending']))->assertOk();                                  // anything else = newest first
    }

    public function test_dashboard_summary_tiles_are_links(): void
    {
        $m = $this->manager();
        $html = $this->actingAs($m)->get(route('manager.dashboard', ['period' => '7d']))->assertOk()->getContent();

        foreach ([
            route('manager.sales', ['period' => '7d']),                                  // Total sales and Money collected
            route('manager.deliveries.index', ['status' => 'delivered']),                 // Delivered
            route('manager.deliveries.index', ['status' => 'pending']),                   // Pending
            route('manager.debtors'),                                                    // Outstanding
        ] as $url) {
            $this->assertStringContainsString('href="' . $url . '"', $html, $url);
        }
        $this->assertGreaterThanOrEqual(2, substr_count($html, 'href="' . route('manager.sales', ['period' => '7d']) . '"'));
    }
}
