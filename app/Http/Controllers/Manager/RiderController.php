<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RiderController extends Controller
{
    public function index()
    {
        $riders = User::where('role', 'rider')->withCount([
            'deliveries as total_deliveries' => fn ($q) => $q->where('status', 'delivered'),
            'deliveries as open_deliveries' => fn ($q) => $q->whereIn('status', ['assigned', 'out_for_delivery']),
        ])->orderBy('name')->get();

        $cashHeld = $riders->sum(fn ($r) => $r->custody()['cash_held']);

        return view('manager.riders.index', ['riders' => $riders, 'cashHeld' => $cashHeld]);
    }

    /** A rider's full delivery history with totals, filterable by status and date range. */
    public function show(Request $request, User $rider)
    {
        abort_unless($rider->isRider(), 404);

        $base = fn () => \App\Models\Delivery::where('rider_id', $rider->id)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('delivery_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('delivery_date', '<=', $request->input('to')));

        $all = \App\Models\Delivery::where('rider_id', $rider->id);
        $stats = [
            'total' => (clone $all)->count(),
            'delivered' => (clone $all)->where('status', 'delivered')->count(),
            'undelivered' => (clone $all)->where('status', 'undelivered')->count(),
            'open' => (clone $all)->whereIn('status', ['assigned', 'out_for_delivery'])->count(),
            'value' => (float) (clone $all)->where('status', 'delivered')->sum(\Illuminate\Support\Facades\DB::raw('total_amount + delivery_fee')),
            'fees' => (float) (clone $all)->where('status', 'delivered')->sum('delivery_fee'),
        ];
        $stats['rate'] = $stats['total'] ? round($stats['delivered'] / $stats['total'] * 100, 1) : 0;

        return view('manager.riders.show', [
            'rider' => $rider,
            'stats' => $stats,
            'custody' => $rider->custody(),
            // delivery fees received on the deliveries in the current filter (delivered ones only)
            'listFees' => (float) $base()->where('status', 'delivered')->sum('delivery_fee'),
            // delivery fees set on every listed order (cancelled ones excluded)
            'listCharged' => (float) $base()->where('status', '!=', 'cancelled')->sum('delivery_fee'),
            'deliveries' => $base()->with('customer')->latest('delivery_date')->latest('id')->paginate(25)->withQueryString(),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge(['phone' => Phone::clean($request->input('phone'))]);
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => ['required', 'regex:/^0\d{9}$/', 'unique:users,phone'],
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);
        User::create($data + ['role' => 'rider', 'is_active' => true]);

        return back()->with('success', "Rider {$data['name']} added. They sign in with their phone number.");
    }

    public function update(Request $request, User $rider)
    {
        abort_unless($rider->isRider(), 404);
        $request->merge(['phone' => Phone::clean($request->input('phone'))]);
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => ['required', 'regex:/^0\d{9}$/', Rule::unique('users', 'phone')->ignore($rider->id)],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($rider->id)],
            'password' => 'nullable|string|min:6',
        ]);
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $rider->update($data);

        return back()->with('success', 'Rider updated.');
    }

    public function toggle(User $rider)
    {
        abort_unless($rider->isRider(), 404);
        $rider->update(['is_active' => ! $rider->is_active]);

        return back()->with('success', $rider->is_active ? 'Rider re-activated.' : 'Rider deactivated.');
    }
}
