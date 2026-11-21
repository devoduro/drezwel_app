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
        // newest first by default; ?dir=asc lists the oldest first
        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';
        $deliveries = Delivery::with(['customer', 'rider'])
            ->forDelivery()   // counter sales are sales, not deliveries
            ->search($request->input('q'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('rider'), fn ($q) => $q->where('rider_id', $request->input('rider')))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('delivery_date', $request->input('date')))
            ->orderBy('delivery_date', $dir)->orderBy('id', $dir)->paginate(25)->withQueryString();

        return view('manager.deliveries.index', [
            'deliveries' => $deliveries,
            'dir' => $dir,
            'counts' => Delivery::forDelivery()->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status'),
            'money' => $this->expectedMoney(),
            'riders' => User::where('role', 'rider')->orderBy('name')->get(),
        ]);
    }

    /**
     * What the deliveries board is worth. Expected revenue is what open deliveries (waiting, assigned or on the road) will bring in
     * once they are delivered and paid; actual sales are the delivered orders that have really brought money in (the Sales page).
     */
    private function expectedMoney(): array
    {
        $open = Delivery::forDelivery()->whereIn('status', ['pending', 'assigned', 'out_for_delivery']);
        // Goods only: the delivery fee goes to the rider, so it is not expected revenue.
        $billed = (float) (clone $open)->sum('total_amount');
        $deposits = (float) (clone $open)->sum(\Illuminate\Support\Facades\DB::raw(Delivery::GOODS_RECEIVED_SQL));
        $actual = (float) Delivery::where('status', 'delivered')->where('amount_paid', '>', 0)->sum(\Illuminate\Support\Facades\DB::raw(Delivery::GOODS_RECEIVED_SQL));

        return ['open' => (clone $open)->count(), 'expected' => max($billed - $deposits, 0), 'billed' => $billed, 'deposits' => $deposits, 'actual' => $actual];
    }

    public function create()
    {
        $original = request()->filled('exchange_of') ? Delivery::with(['customer', 'items.variant'])->find(request()->input('exchange_of')) : null;
        $exchange = request()->input('order_type') === 'exchange';
        $delivery = new Delivery(['delivery_date' => today(), 'items_sent' => $exchange ? 0 : 1, 'delivery_mode' => $exchange ? 'pick_up' : 'rider', 'order_type' => $exchange ? 'exchange' : 'standard', 'total_amount' => 0]);
        $returnRows = [];
        if ($original) {
            // Started from a sale: same customer, handed over at the counter, with that sale's items ready to be returned.
            $delivery->fill(['order_type' => 'exchange', 'delivery_mode' => 'pick_up', 'items_sent' => 1, 'exchange_of_id' => $original->id, 'location' => $original->location, 'total_amount' => 0]);
            $delivery->setRelation('customer', $original->customer);
            $returnRows = $original->items->filter(fn ($i) => $i->qty > 0)->map(fn ($i) => ['variant_id' => $i->variant_id, 'qty' => $i->qty, 'price' => (float) $i->unit_price])->values()->all();
        }

        return view('manager.deliveries.form', ['delivery' => $delivery, 'riders' => $this->activeRiders(), 'products' => $this->products(), 'original' => $original, 'returnRows' => $returnRows, 'prefillReturns' => (bool) $original, 'feeCatalogue' => \App\Services\DeliveryFees::catalogue()]);
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

        if ($delivery->order_type === 'exchange' && $request->boolean('settle_now')) {
            $this->settleAtCounter($delivery, $request);
        } elseif ($request->filled('rider_id')) {
            $this->flow->assign($delivery, User::findOrFail($request->input('rider_id')), $request->user());
        }

        $word = $delivery->order_type === 'exchange' ? 'Exchange' : 'Delivery';

        return redirect()->route('manager.deliveries.show', $delivery)->with('success', "{$word} {$delivery->reference} " . ($delivery->status === 'delivered' ? 'completed.' : 'created.'));
    }

    public function show(Delivery $delivery)
    {
        $delivery->load(['customer', 'rider', 'events.user', 'payments.rider', 'items.product', 'items.variant', 'exchangeOf', 'exchanges']);

        return view('manager.deliveries.show', ['delivery' => $delivery, 'riders' => $this->activeRiders()]);
    }

    public function edit(Delivery $delivery)
    {
        $delivery->load(['customer', 'items.variant']);
        $returnRows = $delivery->returned_items->map(fn ($i) => ['variant_id' => $i->variant_id, 'qty' => -$i->qty, 'price' => (float) $i->unit_price])->all();

        return view('manager.deliveries.form', ['delivery' => $delivery, 'riders' => $this->activeRiders(), 'products' => $this->products(), 'original' => $delivery->exchangeOf, 'returnRows' => $returnRows, 'prefillReturns' => false, 'feeCatalogue' => \App\Services\DeliveryFees::catalogue()]);
    }

    public function update(Request $request, Delivery $delivery)
    {
        abort_if($delivery->status === 'cancelled', 403, 'Reopen this cancelled order before editing it.');
        $data = $this->validated($request);
        DB::transaction(function () use ($data, $delivery, $request) {
            $customer = $this->customerFor($data);
            $fields = $this->deliveryFields($data) + ['customer_id' => $customer->id];
            if ($delivery->status === 'delivered') {
                // A finished sale: keep "items kept" in step with the corrected number of items.
                $newSent = (int) ($fields['items_sent'] ?? $delivery->items_sent);
                $fields['items_kept'] = $delivery->items_kept === null || (int) $delivery->items_kept === (int) $delivery->items_sent ? $newSent : min((int) $delivery->items_kept, $newSent);
            }
            $delivery->update($fields);
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

    public function confirm(Request $request, Delivery $delivery)
    {
        $data = $request->validate(['reason' => 'nullable|string|max:200', 'items_kept' => 'nullable|integer|min:0', 'cash' => 'nullable|numeric|min:0']);
        $this->flow->overrideConfirm($delivery, $request->user(), $data['reason'] ?? null, $data['items_kept'] ?? null, isset($data['cash']) ? (float) $data['cash'] : null);

        return back()->with('success', 'Delivery marked as delivered. The customer has been texted.');
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
        $this->flow->recordPayment($delivery, $request->user(), (float) $data['amount'], $data['method'], $data['notes'] ?? null, $request->boolean('complete'));

        return back()->with('success', $delivery->fresh()->status === 'delivered' && $request->boolean('complete') ? 'Payment recorded. The order is complete and now shows in Sales.' : 'Payment recorded.');
    }

    /** Record money handed back to the customer for an exchange or return. Stored as a negative payment. */
    public function refund(Request $request, Delivery $delivery)
    {
        $max = $delivery->refund_due;
        abort_if($max <= 0, 422, 'There is nothing to refund on this order.');
        $data = $request->validate(['amount' => ['required', 'numeric', 'min:0.01', 'max:' . max($max, 0.01)], 'method' => ['required', Rule::in(array_keys(Payment::METHODS))], 'notes' => 'nullable|string|max:200']);

        $delivery->payments()->create(['amount' => -(float) $data['amount'], 'method' => $data['method'], 'notes' => $data['notes'] ?? 'Refund to customer', 'recorded_by' => $request->user()->id, 'paid_at' => now()]);
        $delivery->recalcPaid();
        $delivery->logEvent('payment', 'Refunded GH₵ ' . number_format((float) $data['amount'], 2) . " to the customer ({$data['method']})", $request->user()->id);

        return back()->with('success', 'Refund recorded.');
    }

    /** The customer is at the counter: finish the exchange now and record the money that changed hands. */
    private function settleAtCounter(Delivery $delivery, Request $request): void
    {
        $method = in_array($request->input('settle_method'), array_keys(Payment::METHODS), true) ? $request->input('settle_method') : 'cash';
        $delivery->update(['status' => 'delivered', 'delivered_at' => now(), 'items_kept' => $delivery->items_sent, 'delivery_mode' => 'pick_up']);
        $due = $delivery->amount_due;
        if (abs($due) >= 0.01) {
            $delivery->payments()->create(['amount' => $due, 'method' => $method, 'notes' => $due < 0 ? 'Refund at the counter' : 'Paid at the counter', 'recorded_by' => $request->user()->id, 'paid_at' => now()]);
            $delivery->recalcPaid();
        }
        $delivery->logEvent('delivered', 'Exchange completed at the counter' . ($due < 0 ? ', customer refunded GH₵ ' . number_format(-$due, 2) : ($due > 0 ? ', customer paid GH₵ ' . number_format($due, 2) : ', even swap')), $request->user()->id);
    }

    /** Permanently remove an order/sale: its items go back to stock; payments, items and history are deleted with it. */
    public function destroy(Request $request, Delivery $delivery)
    {
        if ($why = $this->flow->deletionBlocker($delivery)) {
            return back()->with('error', $why);
        }

        $ref = $delivery->reference;
        $id = $delivery->id;
        $this->flow->destroy($delivery, $request->user());

        $prev = url()->previous();
        $target = str_contains($prev, "/deliveries/{$id}") ? route('manager.deliveries.index') : $prev;

        return redirect($target)->with('success', "Order {$ref} deleted. Its items went back to stock.");
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
            'returns' => 'nullable|array|max:30',
            'returns.*.variant_id' => 'required|exists:product_variants,id',
            'returns.*.qty' => 'required|integer|min:1|max:999',
            'returns.*.price' => 'required|numeric|min:0',
            'exchange_of_id' => 'nullable|exists:deliveries,id',
            'customer_name' => 'required|string|max:120',
            'customer_phone' => ['required', 'regex:/^0\d{9}$/'],
            'location' => 'required|string|max:200',
            'package_description' => 'required_without_all:items,returns|nullable|string|max:200',
            'delivery_mode' => ['required', Rule::in(array_keys(Delivery::MODES))],
            'order_type' => ['required', Rule::in(array_keys(Delivery::TYPES))],
            'delivery_date' => 'required|date',
            'time_window' => ['nullable', Rule::in(Delivery::WINDOWS)],
            'total_amount' => ['required', 'numeric', $request->input('order_type') === 'exchange' ? 'min:-9999999' : 'min:0'],
            'delivery_fee' => 'nullable|numeric|min:0',
            'items_sent' => 'required_without_all:items,returns|nullable|integer|min:0|max:99',
            'notes' => 'nullable|string|max:1000',
            'rider_id' => 'nullable|exists:users,id',
        ], ['customer_phone.regex' => 'Enter a 10-digit Ghana number, e.g. 0241234567.']);

        // With product lines, the package text and item count come from the lines.
        $data['items'] = array_values($data['items'] ?? []);
        // Items brought back are only meaningful on an exchange; they are stored as negative quantities.
        $returns = $data['order_type'] === 'exchange' ? array_values($data['returns'] ?? []) : [];
        unset($data['returns']);
        $all = array_merge($data['items'], array_map(fn ($r) => ['variant_id' => $r['variant_id'], 'qty' => -(int) $r['qty'], 'price' => $r['price']], $returns));
        if ($all) {
            $names = \App\Models\ProductVariant::with('product')->whereIn('id', array_column($all, 'variant_id'))->get()->keyBy('id');
            $label = fn ($l) => $names[$l['variant_id']]->product->name . ($names[$l['variant_id']]->is_plain ? '' : ' (' . $names[$l['variant_id']]->label . ')') . ' x' . abs($l['qty']);
            $sent = array_filter($all, fn ($l) => $l['qty'] > 0);
            $back = array_filter($all, fn ($l) => $l['qty'] < 0);
            $data['package_description'] ??= $back
                ? trim(($sent ? 'New: ' . collect($sent)->map($label)->implode(', ') . '. ' : '') . 'Returned: ' . collect($back)->map($label)->implode(', '))
                : collect($sent)->map($label)->implode(', ');
            $data['package_description'] = mb_substr($data['package_description'], 0, 200);
            $data['items_sent'] = array_sum(array_column($sent, 'qty'));
            $data['items'] = $all;
        }

        return $data;
    }

    private function deliveryFields(array $d): array
    {
        return collect($d)->only(['location', 'package_description', 'delivery_mode', 'order_type', 'delivery_date', 'time_window', 'total_amount', 'delivery_fee', 'items_sent', 'notes', 'exchange_of_id'])
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
