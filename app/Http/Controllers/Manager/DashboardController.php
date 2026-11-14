<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public const PERIODS = [
        'all' => 'All time', 'today' => 'Today', 'yesterday' => 'Yesterday', '7d' => 'Last 7 days',
        '30d' => 'Last 30 days', 'month' => 'This month', 'custom' => 'Custom range',
    ];

    public static function range(Request $request): array
    {
        $p = $request->input('period', 'all');
        $today = today();

        return match ($p) {
            'today' => [$today, $today],
            'yesterday' => [$today->copy()->subDay(), $today->copy()->subDay()],
            '7d' => [$today->copy()->subDays(6), $today],
            '30d' => [$today->copy()->subDays(29), $today],
            'month' => [$today->copy()->startOfMonth(), $today],
            'custom' => [
                $request->filled('from') ? \Carbon\Carbon::parse($request->input('from')) : null,
                $request->filled('to') ? \Carbon\Carbon::parse($request->input('to')) : null,
            ],
            default => [null, null],
        };
    }

    public function index(Request $request)
    {
        [$from, $to] = self::range($request);
        $scope = fn () => Delivery::active()
            ->when($from, fn ($q) => $q->whereDate('delivery_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('delivery_date', '<=', $to));

        $orders = $scope()->count();
        $delivered = $scope()->where('status', 'delivered')->count();
        $revenue = (float) $scope()->sum(\Illuminate\Support\Facades\DB::raw('total_amount + delivery_fee'));
        $collected = (float) $scope()->sum('amount_paid');

        $kpi = [
            'orders' => $orders,
            'revenue' => $revenue,
            'collected' => $collected,
            'outstanding' => max($revenue - $collected, 0),
            'delivered' => $delivered,
            'pending' => $scope()->whereIn('status', ['pending', 'assigned', 'out_for_delivery', 'undelivered'])->count(),
            'rate' => $orders ? round($delivered / $orders * 100, 1) : 0,
            'unassigned' => $scope()->where('status', 'pending')->count(),
            'on_the_road' => $scope()->where('status', 'out_for_delivery')->count(),
        ];

        $daily = $scope()->selectRaw("delivery_date as day, COUNT(*) as orders, SUM(status='delivered') as delivered,
                SUM(status!='delivered') as pending, SUM(total_amount + delivery_fee) as revenue, SUM(amount_paid) as cash")
            ->groupBy('delivery_date')->orderByDesc('delivery_date')->limit(14)->get();

        $riders = User::where('role', 'rider')->where('is_active', true)->orderBy('name')->get()->map(function ($r) use ($scope) {
            $q = fn () => $scope()->where('rider_id', $r->id);
            $assigned = $q()->count();
            $done = $q()->where('status', 'delivered')->count();

            return (object) [
                'rider' => $r, 'assigned' => $assigned, 'delivered' => $done, 'pending' => $assigned - $done,
                'rate' => $assigned ? round($done / $assigned * 100, 1) : 0,
                'revenue' => (float) $q()->sum(\Illuminate\Support\Facades\DB::raw('total_amount + delivery_fee')),
                'custody' => $r->custody(),
            ];
        });

        $modes = $scope()->selectRaw('delivery_mode, COUNT(*) as orders, SUM(total_amount + delivery_fee) as revenue')->groupBy('delivery_mode')->get()->keyBy('delivery_mode');
        $types = $scope()->selectRaw('order_type, COUNT(*) as orders, SUM(items_sent) as sent,
                SUM(CASE WHEN status="delivered" THEN GREATEST(items_sent-COALESCE(items_kept,items_sent),0) ELSE 0 END) as returned')
            ->groupBy('order_type')->get()->keyBy('order_type');

        $topCustomers = $scope()->join('customers', 'customers.id', '=', 'deliveries.customer_id')
            ->selectRaw('customers.id, customers.name, COUNT(*) as orders, SUM(total_amount + delivery_fee) as spend, SUM(amount_paid) as paid')
            ->groupBy('customers.id', 'customers.name')->orderByDesc('spend')->limit(8)->get();

        $todayQueue = Delivery::active()->with(['customer', 'rider'])->whereDate('delivery_date', today())
            ->whereIn('status', ['pending', 'assigned', 'out_for_delivery', 'undelivered'])->orderBy('id')->get();
        $upcoming = Delivery::active()->with(['customer', 'rider'])->whereDate('delivery_date', '>', today())
            ->whereIn('status', ['pending', 'assigned'])->orderBy('delivery_date')->limit(10)->get();

        return view('manager.dashboard', [
            'kpi' => $kpi, 'daily' => $daily, 'riders' => $riders, 'modes' => $modes, 'types' => $types,
            'topCustomers' => $topCustomers, 'todayQueue' => $todayQueue, 'upcoming' => $upcoming,
            'period' => $request->input('period', 'all'), 'periods' => self::PERIODS,
            'cashHeld' => $riders->sum(fn ($r) => $r->custody['cash_held']),
            'lowProducts' => \App\Models\Product::needingStock()->orderBy('stock_qty')->limit(8)->get(),
        ]);
    }
}
