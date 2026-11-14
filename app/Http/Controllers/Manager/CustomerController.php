<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $term = trim((string) $request->input('q'));
        $customers = Customer::query()
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")))
            ->withCount(['deliveries as orders' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->withSum(['deliveries as spend' => fn ($q) => $q->where('status', '!=', 'cancelled')], \Illuminate\Support\Facades\DB::raw('total_amount + delivery_fee'))
            ->withSum(['deliveries as paid' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'amount_paid')
            ->orderBy('name')->paginate(30)->withQueryString();

        $active = \App\Models\Delivery::active();
        $totals = [
            'customers' => Customer::count(),
            'spend' => (float) (clone $active)->sum(\Illuminate\Support\Facades\DB::raw('total_amount + delivery_fee')),
            'owed' => max((float) (clone $active)->sum(\Illuminate\Support\Facades\DB::raw('total_amount + delivery_fee')) - (float) (clone $active)->sum('amount_paid'), 0),
            'debtors' => \App\Models\Delivery::active()->whereRaw('total_amount + delivery_fee > amount_paid')->distinct('customer_id')->count('customer_id'),
        ];

        return view('manager.customers.index', ['customers' => $customers, 'totals' => $totals]);
    }

    /** A customer's delivery history with totals, filterable by status and date range. */
    public function show(Request $request, Customer $customer)
    {
        $all = $customer->deliveries()->where('status', '!=', 'cancelled');
        $stats = [
            'orders' => (clone $all)->count(),
            'delivered' => (clone $all)->where('status', 'delivered')->count(),
            'pending' => (clone $all)->whereIn('status', ['pending', 'assigned', 'out_for_delivery', 'undelivered'])->count(),
            'spend' => (float) (clone $all)->sum(\Illuminate\Support\Facades\DB::raw('total_amount + delivery_fee')),
            'paid' => (float) (clone $all)->sum('amount_paid'),
            'last' => $customer->deliveries()->max('delivery_date'),
        ];

        $deliveries = $customer->deliveries()->with('rider')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('delivery_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('delivery_date', '<=', $request->input('to')))
            ->latest('delivery_date')->latest('id')->paginate(25)->withQueryString();

        return view('manager.customers.show', ['customer' => $customer, 'stats' => $stats, 'deliveries' => $deliveries]);
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate(['name' => 'required|string|max:120', 'location' => 'nullable|string|max:200', 'notes' => 'nullable|string|max:1000']);
        $customer->update($data);

        return back()->with('success', 'Customer updated.');
    }

    /** Customers who still owe money, oldest debt first. */
    public function debtors()
    {
        $rows = \App\Models\Delivery::with('customer')->active()
            ->whereRaw('total_amount + delivery_fee > amount_paid')
            ->orderBy('delivery_date')->get();

        $byCustomer = $rows->groupBy('customer_id')->map(function ($ds) {
            $oldest = $ds->min('delivery_date');

            return (object) [
                'customer' => $ds->first()->customer,
                'balance' => $ds->sum(fn ($d) => $d->balance),
                'deliveries' => $ds,
                'oldest' => $oldest,
                'age' => $oldest->diffInDays(today()),
            ];
        })->sortByDesc('balance')->values();

        return view('manager.debtors', [
            'debtors' => $byCustomer,
            'total' => $byCustomer->sum('balance'),
            'aging' => ['recent' => $byCustomer->where('age', '<=', 7)->sum('balance'), 'old' => $byCustomer->where('age', '>', 7)->sum('balance')],
        ]);
    }
}
