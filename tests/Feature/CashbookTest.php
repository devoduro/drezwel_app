<?php

namespace Tests\Feature;

use App\Models\CashbookEntry;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashbookTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        return User::factory()->create(['role' => 'manager']);
    }

    private function entry(array $o = []): CashbookEntry
    {
        return CashbookEntry::create($o + [
            'entry_date' => today(), 'type' => 'expense', 'category' => 'Rent', 'description' => 'Shop rent', 'amount' => 500, 'method' => 'cash',
        ]);
    }

    private function payload(array $o = []): array
    {
        return $o + ['entry_date' => today()->toDateString(), 'type' => 'purchase', 'category' => 'Stock / goods for resale', 'description' => '20 jeans', 'payee' => 'Kantamanto trader', 'amount' => '1200.50', 'method' => 'momo', 'reference' => 'R-77'];
    }

    public function test_sidebar_links_to_the_cash_book(): void
    {
        $this->actingAs($this->manager())->get(route('manager.dashboard'))->assertSee('Cash book')->assertSee(route('manager.cashbook'), false);
    }

    public function test_manager_records_a_purchase_and_an_expense(): void
    {
        $m = $this->manager();
        $this->actingAs($m)->post(route('manager.cashbook.store'), $this->payload())->assertRedirect()->assertSessionHas('success');
        $this->actingAs($m)->post(route('manager.cashbook.store'), $this->payload(['type' => 'expense', 'category' => 'Rent', 'description' => 'November rent', 'amount' => 800]));

        $e = CashbookEntry::where('description', '20 jeans')->firstOrFail();
        $this->assertEquals(1200.50, (float) $e->amount);
        $this->assertSame($m->id, $e->created_by);
        $this->assertSame(2, CashbookEntry::count());
    }

    public function test_entries_are_validated(): void
    {
        $m = $this->manager();
        $this->actingAs($m)->post(route('manager.cashbook.store'), $this->payload(['amount' => 0]))->assertSessionHasErrors('amount');
        $this->actingAs($m)->post(route('manager.cashbook.store'), $this->payload(['category' => 'Rent']))->assertSessionHasErrors('category');   // an expense category on a purchase
        $this->actingAs($m)->post(route('manager.cashbook.store'), $this->payload(['entry_date' => today()->addDay()->toDateString()]))->assertSessionHasErrors('entry_date');
        $this->actingAs($m)->post(route('manager.cashbook.store'), $this->payload(['method' => 'cheque']))->assertSessionHasErrors('method');
        $this->actingAs($m)->post(route('manager.cashbook.store'), $this->payload(['description' => '']))->assertSessionHasErrors('description');
        $this->assertSame(0, CashbookEntry::count());
    }

    public function test_totals_compare_money_out_with_money_received(): void
    {
        $m = $this->manager();
        $this->entry(['type' => 'purchase', 'category' => 'Stock / goods for resale', 'description' => 'Stock run', 'amount' => 1000]);
        $this->entry(['amount' => 250]);
        $this->entry(['description' => 'Old rent', 'amount' => 9999, 'entry_date' => today()->subDays(90)]);          // outside the default 30 days
        $c = Customer::create(['name' => 'Buyer', 'phone' => '0241112222']);
        Delivery::create(['reference' => Delivery::newReference(), 'customer_id' => $c->id, 'location' => 'Osu', 'package_description' => 'x', 'delivery_date' => today(),
            'total_amount' => 2000, 'delivery_fee' => 0, 'items_sent' => 1, 'status' => 'delivered', 'source' => 'pos', 'amount_paid' => 2000]);

        $res = $this->actingAs($m)->get(route('manager.cashbook'))->assertOk();
        $res->assertSee('GH₵ 1,250.00')            // money out: 1000 + 250
            ->assertSee('GH₵ 1,000.00')            // purchases
            ->assertSee('GH₵ 250.00')              // expenses
            ->assertSee('GH₵ 750.00')              // cash position: 2000 received - 1250 spent
            ->assertDontSee('Old rent');
        $this->actingAs($m)->get(route('manager.cashbook', ['period' => 'all']))->assertSee('Old rent');
    }

    public function test_filter_by_type_category_and_search(): void
    {
        $m = $this->manager();
        $this->entry(['description' => 'Fuel for riders', 'category' => 'Transport & fuel']);
        $this->entry(['description' => 'Jeans restock', 'type' => 'purchase', 'category' => 'Stock / goods for resale', 'payee' => 'Madam Ama']);

        $this->actingAs($m)->get(route('manager.cashbook', ['type' => 'purchase']))->assertSee('Jeans restock')->assertDontSee('Fuel for riders');
        $this->actingAs($m)->get(route('manager.cashbook', ['category' => 'Transport & fuel']))->assertSee('Fuel for riders')->assertDontSee('Jeans restock');
        $this->actingAs($m)->get(route('manager.cashbook', ['q' => 'Madam']))->assertSee('Jeans restock')->assertDontSee('Fuel for riders');
    }

    public function test_edit_and_delete(): void
    {
        $m = $this->manager();
        $e = $this->entry();

        $this->actingAs($m)->get(route('manager.cashbook', ['edit' => $e->id]))->assertOk()->assertSee('Edit entry')->assertSee('Shop rent');
        $this->actingAs($m)->put(route('manager.cashbook.update', $e), $this->payload(['type' => 'expense', 'category' => 'Utilities (light, water)', 'description' => 'ECG bill', 'amount' => 90]))->assertRedirect();
        $e->refresh();
        $this->assertSame('ECG bill', $e->description);
        $this->assertEquals(90.0, (float) $e->amount);

        $this->actingAs($m)->delete(route('manager.cashbook.destroy', $e))->assertRedirect();
        $this->assertSame(0, CashbookEntry::count());
    }

    public function test_csv_export_lists_entries_with_a_total(): void
    {
        $this->entry(['description' => 'Packing tape', 'amount' => 15.5]);
        $this->entry(['description' => 'Airtime', 'category' => 'Phone & data', 'amount' => 10]);

        $csv = $this->actingAs($this->manager())->get(route('manager.cashbook.export', ['period' => 'all']))->assertOk()->streamedContent();
        $this->assertStringContainsString('Packing tape', $csv);
        $this->assertStringContainsString('Airtime', $csv);
        $this->assertStringContainsString('TOTAL', $csv);
        $this->assertStringContainsString('25.50', $csv);
    }

    public function test_riders_and_visitors_cannot_open_the_cash_book(): void
    {
        $e = $this->entry();
        $rider = User::factory()->create(['role' => 'rider']);
        $this->actingAs($rider)->get(route('manager.cashbook'))->assertForbidden();
        $this->actingAs($rider)->post(route('manager.cashbook.store'), $this->payload())->assertForbidden();
        $this->actingAs($rider)->delete(route('manager.cashbook.destroy', $e))->assertForbidden();
        $this->assertSame(1, CashbookEntry::count());

        $this->app['auth']->forgetGuards();
        $this->get(route('manager.cashbook'))->assertRedirect('/login');
    }

    public function test_reports_page_shows_cash_book_spending_and_profit_after_expenses(): void
    {
        $m = $this->manager();
        $this->entry(['type' => 'purchase', 'category' => 'Stock / goods for resale', 'description' => 'Restock', 'amount' => 1000]);
        $this->entry(['description' => 'Rent', 'amount' => 300]);
        $this->entry(['description' => 'Old', 'amount' => 7777, 'entry_date' => today()->subDays(90)]);   // outside the period
        $c = Customer::create(['name' => 'Buyer', 'phone' => '0241112222']);
        $p = \App\Models\Product::create(['name' => 'Jeans', 'slug' => 'jeans', 'cost_price' => 60, 'selling_price' => 100, 'stock_qty' => 5]);
        $v = $p->variants()->create(['stock_qty' => 5]);
        $d = Delivery::create(['reference' => Delivery::newReference(), 'customer_id' => $c->id, 'location' => 'Osu', 'package_description' => 'x', 'delivery_date' => today(),
            'total_amount' => 2000, 'delivery_fee' => 0, 'items_sent' => 10, 'status' => 'delivered', 'source' => 'pos', 'amount_paid' => 2000]);
        $d->items()->create(['product_id' => $p->id, 'variant_id' => $v->id, 'qty' => 10, 'unit_price' => 200, 'unit_cost' => 100]);   // gross profit 1000

        $res = $this->actingAs($m)->get(route('manager.reports', ['period' => '30d']))->assertOk();
        $res->assertSee('Cash book: where the money went')->assertSee('Rent')->assertSee('Stock / goods for resale')->assertDontSee('7,777')
            ->assertSee('GH₵ 1,300.00')      // money spent: 1000 + 300
            ->assertSee('GH₵ 700.00')        // net profit: gross 1000 - expenses 300 (purchases are not deducted twice)
            ->assertSee('GH₵ 700.00')        // cash position: 2000 collected - 1300 spent
            ->assertSee(route('manager.cashbook.export', ['period' => '30d']), false);
    }
}
