<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;

/** Every order in one place, whichever way it came in: website, phone, shop counter or entered by staff. */
class OrderController extends Controller
{
    public const SOURCES = ['online' => 'Website', 'phone' => 'Phone', 'pos' => 'Shop counter', 'manager' => 'Entered by staff'];

    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $source = $request->input('source');

        $base = fn () => Delivery::query()
            ->when($source, fn ($q) => $q->where('source', $source))
            ->search($request->input('q'))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('delivery_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('delivery_date', '<=', $request->input('to')));

        $counts = $base()->selectRaw('status, COUNT(*) n')->groupBy('status')->pluck('n', 'status');

        $orders = $base()->with(['customer', 'rider', 'items.product'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest('created_at')->latest('id')->paginate(25)->withQueryString();

        // Website and phone orders that nobody has picked up yet: the ones that need action first.
        $waiting = Delivery::whereIn('source', ['online', 'phone'])->where('status', 'pending')->whereNull('rider_id')->paymentSettled()->count();

        return view('manager.orders', [
            'orders' => $orders,
            'counts' => $counts,
            'status' => $status,
            'sources' => self::SOURCES,
            'waiting' => $waiting,
            'today' => [
                'orders' => Delivery::active()->whereDate('created_at', today())->count(),
                'value' => (float) Delivery::active()->whereDate('created_at', today())->sum(\Illuminate\Support\Facades\DB::raw('total_amount + delivery_fee')),
                'web' => Delivery::where('source', 'online')->whereDate('created_at', today())->count(),
                'in_progress' => Delivery::whereIn('status', ['pending', 'assigned', 'out_for_delivery'])->count(),
            ],
        ]);
    }
}
