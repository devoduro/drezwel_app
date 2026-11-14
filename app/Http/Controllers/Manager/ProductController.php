<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /** What the "Update stock" form offers, and how each choice is stored. [stored type, sign] */
    public const ACTIONS = [
        'purchase' => ['Received new stock', 'purchase', 1],
        'return' => ['Customer return / back to shelf', 'return', 1],
        'adjust_up' => ['Stock count: add', 'adjustment', 1],
        'adjust_down' => ['Stock count: remove', 'adjustment', -1],
        'damage' => ['Damaged / lost', 'damage', -1],
    ];

    public function __construct(private StockService $stock) {}

    public function index(Request $request)
    {
        $term = trim((string) $request->input('q'));
        $products = Product::query()
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%")))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->when($request->input('level') === 'out', fn ($q) => $q->where('stock_qty', '<=', 0))
            ->when($request->input('level') === 'low', fn ($q) => $q->where('stock_qty', '>', 0)->whereColumn('stock_qty', '<=', 'reorder_level'))
            ->when($request->input('level') === 'ok', fn ($q) => $q->whereColumn('stock_qty', '>', 'reorder_level'))
            ->when(! $request->boolean('inactive'), fn ($q) => $q->where('is_active', true))
            ->orderByRaw('stock_qty <= 0 desc, stock_qty <= reorder_level desc')->orderBy('name')
            ->paginate(30)->withQueryString();

        $active = Product::active();

        return view('manager.products.index', [
            'products' => $products,
            'categories' => Product::whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'stats' => [
                'products' => (clone $active)->count(),
                'units' => (int) (clone $active)->sum('stock_qty'),
                'cost_value' => (float) (clone $active)->sum(DB::raw('stock_qty * cost_price')),
                'retail_value' => (float) (clone $active)->sum(DB::raw('stock_qty * selling_price')),
                'low' => (clone $active)->where('stock_qty', '>', 0)->whereColumn('stock_qty', '<=', 'reorder_level')->count(),
                'out' => (clone $active)->where('stock_qty', '<=', 0)->count(),
            ],
            'alertPhone' => $request->user()->phone,
        ]);
    }

    public function create()
    {
        return view('manager.products.form', ['product' => new Product(['reorder_level' => 5, 'is_active' => true]), 'categories' => $this->categories()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $opening = (int) $request->validate(['opening_stock' => 'nullable|integer|min:0|max:100000'])['opening_stock'] ?? 0;

        $product = DB::transaction(function () use ($data, $opening, $request) {
            $product = Product::create($data + ['stock_qty' => 0]);
            if ($opening > 0) {
                $this->stock->move($product, 'opening', $opening, $request->user(), 'Opening stock', null, (float) $product->cost_price);
            }

            return $product;
        });

        return redirect()->route('manager.products.show', $product)->with('success', "{$product->name} added.");
    }

    public function show(Product $product)
    {
        $sold = $product->items()->whereHas('delivery', fn ($q) => $q->where('status', '!=', 'cancelled'));

        return view('manager.products.show', [
            'product' => $product,
            'movements' => $product->movements()->with(['user', 'delivery'])->paginate(20),
            'actions' => self::ACTIONS,
            'sold' => [
                'units' => (int) (clone $sold)->sum('qty'),
                'revenue' => (float) (clone $sold)->sum(DB::raw('qty * unit_price')),
                'profit' => (float) (clone $sold)->sum(DB::raw('qty * (unit_price - unit_cost)')),
            ],
        ]);
    }

    public function edit(Product $product)
    {
        return view('manager.products.form', ['product' => $product, 'categories' => $this->categories()]);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request, $product));
        $this->stock->checkAlert($product->fresh()); // the reorder level may have changed

        return redirect()->route('manager.products.show', $product)->with('success', 'Product updated.');
    }

    /** Receive, return, count-correct or write off stock. */
    public function adjust(Request $request, Product $product)
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(array_keys(self::ACTIONS))],
            'qty' => 'required|integer|min:1|max:100000',
            'unit_cost' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:200',
        ]);
        [, $type, $sign] = self::ACTIONS[$data['action']];
        $this->stock->move($product, $type, $sign * (int) $data['qty'], $request->user(), $data['note'] ?? self::ACTIONS[$data['action']][0],
            null, $data['action'] === 'purchase' && ! empty($data['unit_cost']) ? (float) $data['unit_cost'] : null);

        return back()->with('success', 'Stock updated.');
    }

    public function toggle(Product $product)
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('success', $product->is_active ? 'Product re-activated.' : 'Product hidden from new orders.');
    }

    /** Where low-stock texts are sent: the signed-in manager's own phone. */
    public function alertPhone(Request $request)
    {
        $request->merge(['phone' => Phone::clean($request->input('phone'))]);
        $data = $request->validate(['phone' => ['required', 'regex:/^0\d{9}$/', Rule::unique('users', 'phone')->ignore($request->user()->id)]]);
        $request->user()->update($data);

        return back()->with('success', 'Low-stock alerts will be texted to ' . $data['phone'] . '.');
    }

    private function categories()
    {
        return Product::whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'sku' => ['nullable', 'string', 'max:40', Rule::unique('products', 'sku')->ignore($product?->id)],
            'category' => 'nullable|string|max:60',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0|max:100000',
            'notes' => 'nullable|string|max:1000',
        ]);
        $data['is_active'] = $product ? $product->is_active : true;

        return $data;
    }
}
