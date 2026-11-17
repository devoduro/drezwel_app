<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Sales ledger: what was sold in a period, through which channel, and how it was paid. */
class SalesController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = DashboardController::range(new Request(['period' => $request->input('period', '30d')] + $request->only(['from', 'to'])));
        $source = $request->input('source');
        $method = $request->input('method');

        // A sale only counts once money has been recorded against it (by the rider, the manager, the counter or an online
        // payment). A new order that nobody has paid for yet is an order, not a sale, and stays on the Orders page.
        // A finished exchange is kept even when nothing changed hands, because it still moves stock and profit.
        $scope = fn () => Delivery::active()
            ->where(fn ($q) => $q->where('amount_paid', '!=', 0)->orWhere(fn ($e) => $e->where('order_type', 'exchange')->where('status', 'delivered')))
            ->when($from, fn ($q) => $q->whereDate('delivery_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('delivery_date', '<=', $to))
            ->when($source, fn ($q) => $q->where('source', $source))
            ->when($method, fn ($q) => $q->whereHas('payments', fn ($p) => $p->where('method', $method)))
            ->search($request->input('q'));

        $billed = (float) $scope()->sum(DB::raw('total_amount + delivery_fee'));
        $collected = (float) $scope()->sum('amount_paid');
        $orders = $scope()->count();

        // profit from product lines in the same slice
        $lineIds = $scope()->pluck('deliveries.id');
        $lines = DeliveryItem::whereIn('delivery_id', $lineIds);
        $lineRevenue = (float) (clone $lines)->sum(DB::raw('qty * unit_price'));
        $cost = (float) (clone $lines)->sum(DB::raw('qty * unit_cost'));

        $bySource = $scope()->selectRaw('source, COUNT(*) n, SUM(total_amount + delivery_fee) value, SUM(amount_paid) paid')->groupBy('source')->get()->keyBy('source');

        $topProducts = DashboardController::topProducts($lineIds);

        $sales = $scope()->with(['customer', 'items.product', 'payments'])->latest('delivery_date')->latest('id')->paginate(25)->withQueryString();

        return view('manager.sales', [
            'sales' => $sales,
            'kpi' => [
                'billed' => $billed, 'collected' => $collected, 'outstanding' => max($billed - $collected, 0), 'orders' => $orders,
                'average' => $orders ? $billed / $orders : 0, 'profit' => $lineRevenue - $cost,
            ],
            'bySource' => $bySource,
            'topProducts' => $topProducts,
            'sources' => OrderController::SOURCES,
            'period' => $request->input('period', '30d'),
            'periods' => DashboardController::PERIODS,
            'from' => $from, 'to' => $to,
        ]);
    }
}
