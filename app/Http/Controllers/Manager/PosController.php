<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Services\SalesService;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Point of sale. One screen for two jobs:
 *   - "Counter sale": the customer is in the shop, pays now, and takes the goods (recorded as delivered, paid).
 *   - "Phone order": the customer called; the order goes into the delivery queue to be sent by a rider.
 */
class PosController extends Controller
{
    public function __construct(private SalesService $sales) {}

    public function index()
    {
        $products = Product::active()->with(['images', 'variants' => fn ($q) => $q->where('is_active', true)])
            ->whereHas('variants', fn ($q) => $q->where('is_active', true))->orderBy('name')->get();

        $catalog = $products->map(fn ($p) => [
            'id' => $p->id, 'name' => $p->name, 'category' => $p->category, 'sku' => $p->sku, 'image' => $p->primary_image_url,
            'variants' => $p->variants->map(fn ($v) => ['id' => $v->id, 'label' => $v->label, 'plain' => $v->is_plain, 'price' => $v->selling_price, 'stock' => $v->stock_qty])->values(),
        ])->values();

        return view('manager.pos', [
            'catalog' => $catalog,
            'categories' => $products->pluck('category')->filter()->unique()->sort()->values(),
            'defaultFee' => (float) Setting::get('default_delivery_fee', '0'),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge(['phone' => Phone::clean($request->input('phone'))]);
        $type = $request->input('type') === 'delivery' ? 'delivery' : 'counter';

        $data = $request->validate([
            'type' => 'required|in:counter,delivery',
            'items' => 'required|array|min:1|max:50',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.qty' => 'required|integer|min:1|max:999',
            'items.*.price' => 'required|numeric|min:0',
            'name' => ['nullable', 'string', 'max:120', Rule::requiredIf($type === 'delivery')],
            'phone' => [$type === 'delivery' ? 'required' : 'nullable', 'regex:/^0\d{9}$/'],
            'location' => [$type === 'delivery' ? 'required' : 'nullable', 'string', 'max:200'],
            'delivery_fee' => 'nullable|numeric|min:0',
            'method' => ['nullable', Rule::in(array_keys(Payment::METHODS))],
            'received' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ], ['phone.regex' => 'Enter a 10-digit phone number, e.g. 0241234567.', 'items.required' => 'Add at least one product to the sale.']);

        $customer = ['name' => $data['name'] ?? '', 'phone' => $data['phone'] ?? null, 'location' => $data['location'] ?? null];
        $lines = array_values($data['items']);

        $delivery = $this->sales->placeOrder($customer, $lines, $type === 'counter'
            ? ['source' => 'pos', 'status' => 'delivered', 'user' => $request->user(), 'trust_prices' => true, 'update_customer' => ! empty($data['phone']),
                'notes' => $data['notes'] ?? null, 'payment' => ['method' => $data['method'] ?? 'cash', 'amount' => (float) ($data['received'] ?? PHP_INT_MAX)]]
            : ['source' => 'phone', 'user' => $request->user(), 'trust_prices' => true, 'update_customer' => false, 'delivery_fee' => (float) ($data['delivery_fee'] ?? 0),
                'notes' => $data['notes'] ?? null]);

        return redirect()->route('manager.deliveries.invoice', ['delivery' => $delivery, 'print' => 1] + ($type === 'counter' ? ['format' => 'receipt'] : []))
            ->with('success', $type === 'counter' ? "Sale {$delivery->reference} completed." : "Order {$delivery->reference} created - assign a rider from the delivery page.");
    }
}
