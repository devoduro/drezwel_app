<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\DeliveryWorkflow;
use App\Services\SalesService;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function store(Request $request)
    {
        $request->merge(['phone' => Phone::clean($request->input('phone'))]);
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'phone' => ['required', 'regex:/^0\d{9}$/', 'unique:customers,phone', Rule::notIn([SalesService::WALK_IN_PHONE])],
            'location' => 'nullable|string|max:200', 'notes' => 'nullable|string|max:1000',
        ], ['phone.regex' => 'Enter a 10-digit Ghana phone number, e.g. 0241234567.', 'phone.unique' => 'A customer with that phone number already exists.']);

        $customer = Customer::create($data);

        return redirect()->route('manager.customers.show', $customer)->with('success', 'Customer added.');
    }

    public function update(Request $request, Customer $customer)
    {
        $rules = ['name' => 'required|string|max:120', 'location' => 'nullable|string|max:200', 'notes' => 'nullable|string|max:1000'];
        if ($request->has('phone') && $customer->phone !== SalesService::WALK_IN_PHONE) {
            $request->merge(['phone' => Phone::clean($request->input('phone'))]);
            $rules['phone'] = ['required', 'regex:/^0\d{9}$/', Rule::unique('customers', 'phone')->ignore($customer->id), Rule::notIn([SalesService::WALK_IN_PHONE])];
        }
        $data = $request->validate($rules, ['phone.regex' => 'Enter a 10-digit Ghana phone number, e.g. 0241234567.', 'phone.unique' => 'Another customer already has that phone number.']);
        $customer->update($data);

        return back()->with('success', 'Customer updated.');
    }

    /**
     * Delete a customer. One with no orders just goes. One with orders is only removed when the manager
     * ticks "also delete their orders", which deletes each order the same way the order page does.
     */
    public function destroy(Request $request, Customer $customer, DeliveryWorkflow $flow)
    {
        if ($customer->phone === SalesService::WALK_IN_PHONE) {
            return back()->with('error', 'The walk-in customer is used for counter sales and cannot be deleted.');
        }

        $orders = $customer->deliveries()->get();
        if ($orders->isNotEmpty() && ! $request->boolean('with_orders')) {
            return back()->with('error', "{$customer->name} has {$orders->count()} " . ($orders->count() === 1 ? 'order' : 'orders') . ' on record. Delete the customer together with their orders, or leave them as they are.');
        }
        foreach ($orders as $o) {
            if ($why = $flow->deletionBlocker($o)) {
                return back()->with('error', "Nothing was deleted. {$why}");
            }
        }

        $name = $customer->name;
        \Illuminate\Support\Facades\DB::transaction(function () use ($orders, $flow, $request, $customer) {
            foreach ($orders as $o) {
                $flow->destroy($o, $request->user());
            }
            $customer->delete();
        });

        return redirect()->route('manager.customers.index')->with('success', "{$name} deleted" . ($orders->isNotEmpty() ? " along with {$orders->count()} " . ($orders->count() === 1 ? 'order' : 'orders') . ', and their items went back to stock.' : '.'));
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
