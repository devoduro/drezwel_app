<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Payment;
use App\Models\User;
use App\Services\DeliveryWorkflow;
use App\Services\SpesoPayments;
use App\Services\StockService;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DeliveryController extends Controller
{
    public function __construct(private DeliveryWorkflow $flow, private StockService $stock) {}

    public function index(Request $request)
    {
        $deliveries = Delivery::with(['customer', 'rider'])
            ->search($request->input('q'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('rider'), fn ($q) => $q->where('rider_id', $request->input('rider')))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('delivery_date', $request->input('date')))
            ->latest('delivery_date')->latest('id')->paginate(25)->withQueryString();

        return view('manager.deliveries.index', [
            'deliveries' => $deliveries,
            'counts' => Delivery::selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status'),
            'riders' => User::where('role', 'rider')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('manager.deliveries.form', [
            'delivery' => new Delivery(['delivery_date' => today(), 'items_sent' => 1, 'delivery_mode' => 'rider', 'order_type' => 'standard']),
            'riders' => $this->activeRiders(),
            'products' => $this->products(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $delivery = DB::transaction(function () use ($data, $request) {
            $customer = $this->customerFor($data);
            $delivery = Delivery::create($this->deliveryFields($data) + [
                'reference' => Delivery::newReference(),
                'customer_id' => $customer->id,
                'created_by' => $request->user()->id,
                'status' => 'pending',
            ]);
            $delivery->logEvent('created', 'Order created', $request->user()->id);
            // Product lines come off stock in the same transaction: "not enough stock" cancels the whole order.
            if ($data['items']) {
                $this->stock->syncDeliveryItems($delivery, $data['items'], $request->user());
            }

            return $delivery;
        });

        if ($request->filled('rider_id')) {
            $this->flow->assign($delivery, User::findOrFail($request->input('rider_id')), $request->user());
        }

        return redirect()->route('manager.deliveries.show', $delivery)->with('success', "Delivery {$delivery->reference} created.");
    }

    public function show(Delivery $delivery)
    {
        $delivery->load(['customer', 'rider', 'events.user', 'payments.rider', 'items.product']);

        return view('manager.deliveries.show', ['delivery' => $delivery, 'riders' => $this->activeRiders()]);
    }

    public function edit(Delivery $delivery)
    {
        return view('manager.deliveries.form', ['delivery' => $delivery->load(['customer', 'items.variant']), 'riders' => $this->activeRiders(), 'products' => $this->products()]);
    }

    public function update(Request $request, Delivery $delivery)
    {
        abort_if(in_array($delivery->status, ['delivered', 'cancelled'], true), 403, 'This delivery is closed.');
        $data = $this->validated($request);
        DB::transaction(function () use ($data, $delivery, $request) {
            $customer = $this->customerFor($data);
            $delivery->update($this->deliveryFields($data) + ['customer_id' => $customer->id]);
            $this->stock->syncDeliveryItems($delivery, $data['items'], $request->user());
        });
        $delivery->recalcPaid();
        $delivery->logEvent('edited', 'Order details updated', $request->user()->id);

        return redirect()->route('manager.deliveries.show', $delivery)->with('success', 'Delivery updated.');
    }

    public function assign(Request $request, Delivery $delivery)
    {
        $request->validate(['rider_id' => 'required|exists:users,id']);
        $this->flow->assign($delivery, User::findOrFail($request->input('rider_id')), $request->user());

        return back()->with('success', 'Rider assigned and the customer has been notified.');
    }

    public function resendCode(Request $request, Delivery $delivery)
    {
        $result = $this->flow->sendCode($delivery, $request->user());

        return back()->with($result['success'] ? 'success' : 'error', $result['success'] ? 'A new code was sent to the customer.' : 'SMS failed: ' . $result['message']);
    }

    public function confirm(Request $request, Delivery $delivery)
    {
        $data = $request->validate(['reason' => 'required|string|max:200', 'items_kept' => 'nullable|integer|min:0', 'cash' => 'nullable|numeric|min:0']);
        $this->flow->overrideConfirm($delivery, $request->user(), $data['reason'], $data['items_kept'] ?? null, isset($data['cash']) ? (float) $data['cash'] : null);

        return back()->with('success', 'Delivery confirmed.');
    }

    public function undeliver(Request $request, Delivery $delivery)
    {
        $data = $request->validate(['reason' => 'required|string|max:200']);
        $this->flow->markUndelivered($delivery, $request->user(), $data['reason']);

        return back()->with('success', 'Marked as undelivered.');
    }

    public function reopen(Request $request, Delivery $delivery)
    {
        $data = $request->validate(['delivery_date' => 'nullable|date']);
        $this->flow->reopen($delivery, $request->user(), $data['delivery_date'] ?? null);

        return back()->with('success', 'Delivery is back in the queue.');
    }

    public function cancel(Request $request, Delivery $delivery)
    {
        $this->flow->cancel($delivery, $request->user());

        return back()->with('success', 'Delivery cancelled.');
    }

    public function payment(Request $request, Delivery $delivery)
    {
        $data = $request->validate(['amount' => 'required|numeric|min:0.01', 'method' => ['required', Rule::in(array_keys(Payment::METHODS))], 'notes' => 'nullable|string|max:200']);
        $this->flow->recordPayment($delivery, $request->user(), (float) $data['amount'], $data['method'], $data['notes'] ?? null);

        return back()->with('success', 'Payment recorded.');
    }

    /** Ask Speso whether the customer really paid, and record the answer. */
    public function verifyPayment(Delivery $delivery, SpesoPayments $payments)
    {
        $msg = match ($payments->verify($delivery)) {
            'paid' => ['success', 'Speso confirms this order is paid.'],
            'failed' => ['error', 'Speso says the payment was not completed. No money was taken.'],
            'awaiting' => ['success', 'Still waiting for the customer to approve the prompt. No money has been taken yet.'],
            'mismatch' => ['error', 'Speso shows a different amount than this order. It was left untouched: check it in your Speso dashboard.'],
            'unreachable' => ['error', 'Could not reach Speso. Try again in a moment.'],
            default => ['error', 'This order has no online payment to verify.'],
        };

        return back()->with(...$msg);
    }

    // ----------------------------------------------------------------------------------------

    /** Every size/colour that can be ordered, for the product lines on the order form. */
    private function products()
    {
        return \App\Models\ProductVariant::with('product')->where('is_active', true)
            ->whereHas('product', fn ($q) => $q->where('is_active', true))->get()
            ->sortBy(fn ($v) => $v->product->name . $v->label)->values();
    }

    private function activeRiders()
    {
        return User::where('role', 'rider')->where('is_active', true)->orderBy('name')->get();
    }

    private function validated(Request $request): array
    {
        $request->merge(['customer_phone' => Phone::clean($request->input('customer_phone'))]);

        $data = $request->validate([
            'items' => 'nullable|array|max:30',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.qty' => 'required|integer|min:1|max:999',
            'items.*.price' => 'required|numeric|min:0',
            'customer_name' => 'required|string|max:120',
            'customer_phone' => ['required', 'regex:/^0\d{9}$/'],
            'location' => 'required|string|max:200',
            'package_description' => 'required_without:items|nullable|string|max:200',
            'delivery_mode' => ['required', Rule::in(array_keys(Delivery::MODES))],
            'order_type' => ['required', Rule::in(array_keys(Delivery::TYPES))],
            'delivery_date' => 'required|date',
            'time_window' => ['nullable', Rule::in(Delivery::WINDOWS)],
            'total_amount' => 'required|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'items_sent' => 'required_without:items|nullable|integer|min:1|max:99',
            'notes' => 'nullable|string|max:1000',
            'rider_id' => 'nullable|exists:users,id',
        ], ['customer_phone.regex' => 'Enter a 10-digit Ghana number, e.g. 0241234567.']);

        // With product lines, the package text and item count come from the lines.
        $data['items'] = array_values($data['items'] ?? []);
        if ($data['items']) {
            $names = \App\Models\ProductVariant::with('product')->whereIn('id', array_column($data['items'], 'variant_id'))->get()->keyBy('id');
            $data['package_description'] ??= collect($data['items'])->map(fn ($l) => $names[$l['variant_id']]->product->name . ($names[$l['variant_id']]->is_plain ? '' : ' (' . $names[$l['variant_id']]->label . ')') . ' x' . $l['qty'])->implode(', ');
            $data['items_sent'] = array_sum(array_column($data['items'], 'qty'));
        }

        return $data;
    }

    private function deliveryFields(array $d): array
    {
        return collect($d)->only(['location', 'package_description', 'delivery_mode', 'order_type', 'delivery_date', 'time_window', 'total_amount', 'delivery_fee', 'items_sent', 'notes'])
            ->map(fn ($v) => $v === '' ? null : $v)->put('delivery_fee', $d['delivery_fee'] ?? 0)->all();
    }

    /** Orders are tied to a customer record found by phone number; its name/location are kept fresh. */
    private function customerFor(array $d): Customer
    {
        $customer = Customer::firstOrNew(['phone' => $d['customer_phone']]);
        $customer->name = $d['customer_name'];
        $customer->location = $d['location'];
        $customer->save();

        return $customer;
    }
}
