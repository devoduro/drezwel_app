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
        $res->assertSee('GH₵ 520.00')                  // billed: 300+20 and 200
            ->assertSee('GH₵ 320.00')                  // received: 120 and 200
            ->assertSee('GH₵ 200.00')                  // still owed: 520 - 320
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
        $res->assertSee('Expected revenue')->assertSee('GH₵ 470.00')    // 320 + 150 still to collect
            ->assertSee('GH₵ 520.00')                                    // value of the open deliveries: 320 + 200
            ->assertSee('GH₵ 400.00')                                    // actual sales
            ->assertDontSee('GH₵ 999.00')->assertDontSee('GH₵ 888.00')->assertDontSee('GH₵ 1,');   // undelivered and cancelled orders are not counted
    }
}
