<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\CashbookEntry;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** One comprehensive report: finances, orders and stock for a chosen period. */
class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = DashboardController::range($request);
        $d = $this->data($from, $to);

        return view('manager.reports', $d + [
            'period' => $request->input('period', '30d'),
            'periods' => DashboardController::PERIODS,
            'from' => $from, 'to' => $to,
        ]);
    }

    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, ['orders', 'stock', 'sales'], true), 404);
        [$from, $to] = DashboardController::range($request);
        $name = "drezwel-{$type}-" . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($type, $from, $to) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // so Excel reads GH₵ and accents correctly
            match ($type) {
                'orders' => $this->csvOrders($out, $from, $to),
                'stock' => $this->csvStock($out),
                'sales' => $this->csvSales($out, $from, $to),
            };
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ------------------------------------------------------------------------------------------

    private function scope($from, $to)
    {
        return Delivery::active()
            ->when($from, fn ($q) => $q->whereDate('delivery_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('delivery_date', '<=', $to));
    }

    private function lines($from, $to)
    {
        return DeliveryItem::query()->join('deliveries', 'deliveries.id', '=', 'delivery_items.delivery_id')
            ->where('deliveries.status', '!=', 'cancelled')
            ->when($from, fn ($q) => $q->whereDate('deliveries.delivery_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('deliveries.delivery_date', '<=', $to));
    }

    private function data($from, $to): array
    {
        $scope = fn () => $this->scope($from, $to);

        // ---- Orders
        $orders = $scope()->count();
        $byStatus = $scope()->selectRaw('status, COUNT(*) n')->groupBy('status')->pluck('n', 'status');
        $delivered = (int) ($byStatus['delivered'] ?? 0);
        $goods = (float) $scope()->sum('total_amount');
        $feesCharged = (float) $scope()->sum('delivery_fee');
        $revenue = $goods + $feesCharged; // what customers are billed: goods + delivery fee
        $collected = (float) $scope()->sum('amount_paid');

        // ---- Product-line profit (orders without product lines have no known cost)
        $lineRevenue = (float) $this->lines($from, $to)->sum(DB::raw('delivery_items.qty * delivery_items.unit_price'));
        $cogs = (float) $this->lines($from, $to)->sum(DB::raw('delivery_items.qty * delivery_items.unit_cost'));
        $feesEarned = (float) $scope()->where('status', 'delivered')->sum('delivery_fee');
        $feesPaid = (float) $scope()->where('status', 'delivered')->where('rider_fee_status', 'paid')->sum('delivery_fee');
        $gross = $lineRevenue - $cogs;

        // ---- Cash book: what the business spent in the period (by the date on each entry)
        $book = fn () => CashbookEntry::query()
            ->when($from, fn ($q) => $q->whereDate('entry_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('entry_date', '<=', $to));
        $purchases = (float) $book()->where('type', 'purchase')->sum('amount');
        $expenses = (float) $book()->where('type', 'expense')->sum('amount');
        $cashbookCategories = $book()->selectRaw('category, type, COUNT(*) n, SUM(amount) total')->groupBy('category', 'type')->orderByDesc('total')->get();

        $finance = [
            'revenue' => $revenue, 'collected' => $collected, 'outstanding' => max($revenue - $collected, 0),
            'collection_rate' => $revenue > 0 ? round($collected / $revenue * 100, 1) : 0,
            'line_revenue' => $lineRevenue, 'cogs' => $cogs, 'gross' => $gross,
            'margin' => $lineRevenue > 0 ? round($gross / $lineRevenue * 100, 1) : 0,
            'goods' => $goods, 'fees_charged' => $feesCharged, 'fees_earned' => $feesEarned,
            'fees_paid' => $feesPaid, 'fees_owed' => $feesEarned - $feesPaid,
            // Delivery fees are paid by the customer and passed to the rider, so they are not business profit or cost.
            // Cost of goods sold already covers the stock that was sold, so only running expenses come off gross profit.
            // Purchases are shown separately as cash that went out.
            'expenses' => $expenses, 'purchases' => $purchases, 'cash_out' => $purchases + $expenses,
            'net' => $gross - $expenses, 'cash_position' => $collected - $purchases - $expenses,
            'untracked' => max($goods - $lineRevenue, 0), // goods sold on orders with no products attached
        ];

        $payments = Payment::query()
            ->when($from, fn ($q) => $q->whereDate('paid_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('paid_at', '<=', $to))
            ->selectRaw('method, COUNT(*) n, SUM(amount) total')->groupBy('method')->get()->keyBy('method');

        $daily = $scope()->selectRaw("delivery_date day, COUNT(*) orders, SUM(status='delivered') delivered, SUM(total_amount + delivery_fee) revenue, SUM(amount_paid) paid")
            ->groupBy('delivery_date')->orderBy('delivery_date')->get();

        // ---- Orders detail
        $modes = $scope()->selectRaw('delivery_mode k, COUNT(*) n, SUM(total_amount + delivery_fee) revenue')->groupBy('delivery_mode')->get()->keyBy('k');
        $types = $scope()->selectRaw('order_type k, COUNT(*) n, SUM(total_amount + delivery_fee) revenue')->groupBy('order_type')->get()->keyBy('k');
        $topCustomers = $scope()->join('customers', 'customers.id', '=', 'deliveries.customer_id')
            ->selectRaw('customers.id, customers.name, COUNT(*) orders, SUM(total_amount + delivery_fee) spend, SUM(amount_paid) paid')
            ->groupBy('customers.id', 'customers.name')->orderByDesc('spend')->limit(8)->get();
        $riders = User::where('role', 'rider')->orderBy('name')->get()->map(function ($r) use ($scope) {
            $q = fn () => $scope()->where('rider_id', $r->id);
            $assigned = $q()->count();
            $done = $q()->where('status', 'delivered')->count();

            return (object) ['rider' => $r, 'assigned' => $assigned, 'delivered' => $done, 'undelivered' => $q()->where('status', 'undelivered')->count(),
                'rate' => $assigned ? round($done / $assigned * 100, 1) : 0, 'value' => (float) $q()->where('status', 'delivered')->sum(\Illuminate\Support\Facades\DB::raw('total_amount + delivery_fee')),
                'fees' => (float) $q()->where('status', 'delivered')->sum('delivery_fee'), 'fees_owed' => (float) $q()->where('status', 'delivered')->where('rider_fee_status', 'pending')->sum('delivery_fee'), 'custody' => $r->custody()];
        })->filter(fn ($r) => $r->assigned > 0 || ! $r->custody['cleared']);

        // ---- Stock
        $active = fn () => Product::active();
        $stock = [
            'products' => $active()->count(),
            'units' => (int) $active()->sum('stock_qty'),
            'cost_value' => (float) $active()->sum(DB::raw('stock_qty * cost_price')),
            'retail_value' => (float) $active()->sum(DB::raw('stock_qty * selling_price')),
        ];
        $stock['potential_profit'] = $stock['retail_value'] - $stock['cost_value'];
        $needing = Product::needingStock()->orderBy('stock_qty')->get();

        $sales = $this->lines($from, $to)->join('products', 'products.id', '=', 'delivery_items.product_id')
            ->selectRaw('products.id, products.name, products.sku, SUM(delivery_items.qty) units, SUM(delivery_items.qty * delivery_items.unit_price) revenue,
                         SUM(delivery_items.qty * delivery_items.unit_cost) cost')
            ->groupBy('products.id', 'products.name', 'products.sku')->orderByDesc('units')->limit(15)->get();

        $movements = StockMovement::query()
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->selectRaw('type, COUNT(*) n, SUM(qty) units')->groupBy('type')->get()->keyBy('type');

        return compact('orders', 'byStatus', 'delivered', 'finance', 'payments', 'daily', 'modes', 'types', 'topCustomers', 'riders', 'stock', 'needing', 'sales', 'movements', 'cashbookCategories');
    }

    // ---- CSV

    private function csvOrders($out, $from, $to): void
    {
        fputcsv($out, ['Reference', 'Date', 'Customer', 'Phone', 'Location', 'Package', 'Mode', 'Type', 'Rider', 'Status', 'Goods total', 'Delivery fee', 'Customer pays', 'Paid', 'Balance', 'Fee paid to rider', 'Delivered at']);
        $this->scope($from, $to)->with(['customer', 'rider'])->orderBy('delivery_date')->orderBy('id')->chunk(200, function ($rows) use ($out) {
            foreach ($rows as $d) {
                fputcsv($out, [$d->reference, $d->delivery_date->format('Y-m-d'), $d->customer->name, $d->customer->phone, $d->location, $d->package_description,
                    Delivery::MODES[$d->delivery_mode] ?? $d->delivery_mode, Delivery::TYPES[$d->order_type] ?? $d->order_type, $d->rider?->name, $d->status_label,
                    $d->total_amount, $d->delivery_fee, $d->amount_due, $d->amount_paid, $d->balance, $d->rider_fee_status === 'paid' ? 'yes' : 'no', $d->delivered_at?->format('Y-m-d H:i')]);
            }
        });
    }

    private function csvStock($out): void
    {
        fputcsv($out, ['Product', 'SKU', 'Category', 'In stock', 'Reorder level', 'Level', 'Cost price', 'Selling price', 'Stock value (cost)', 'Stock value (retail)', 'Active']);
        Product::orderBy('name')->chunk(200, function ($rows) use ($out) {
            foreach ($rows as $p) {
                fputcsv($out, [$p->name, $p->sku, $p->category, $p->stock_qty, $p->reorder_level, strtoupper($p->level), $p->cost_price, $p->selling_price,
                    round($p->stock_qty * $p->cost_price, 2), round($p->stock_qty * $p->selling_price, 2), $p->is_active ? 'yes' : 'no']);
            }
        });
    }

    private function csvSales($out, $from, $to): void
    {
        fputcsv($out, ['Product', 'SKU', 'Units sold', 'Revenue', 'Cost', 'Profit', 'Margin %']);
        $rows = $this->lines($from, $to)->join('products', 'products.id', '=', 'delivery_items.product_id')
            ->selectRaw('products.name, products.sku, SUM(delivery_items.qty) units, SUM(delivery_items.qty * delivery_items.unit_price) revenue, SUM(delivery_items.qty * delivery_items.unit_cost) cost')
            ->groupBy('products.id', 'products.name', 'products.sku')->orderByDesc('units')->get();
        foreach ($rows as $r) {
            fputcsv($out, [$r->name, $r->sku, $r->units, $r->revenue, $r->cost, $r->revenue - $r->cost, $r->revenue > 0 ? round(($r->revenue - $r->cost) / $r->revenue * 100, 1) : 0]);
        }
    }
}
