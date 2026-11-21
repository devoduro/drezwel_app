<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Services\ProductImageService;
use App\Services\StockService;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /** What the "Update stock" form offers, and how each choice is stored. [label, stored type, sign] */
    public const ACTIONS = [
        'purchase' => ['Received new stock', 'purchase', 1],
        'return' => ['Customer return / back to shelf', 'return', 1],
        'adjust_up' => ['Stock count: add', 'adjustment', 1],
        'adjust_down' => ['Stock count: remove', 'adjustment', -1],
        'damage' => ['Damaged / lost', 'damage', -1],
    ];

    public function __construct(private StockService $stock, private ProductImageService $images) {}

    public function index(Request $request)
    {
        $term = trim((string) $request->input('q'));
        $products = Product::query()->with(['images', 'variants'])
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
            'categories' => $this->categories(),
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
        return view('manager.products.form', ['product' => new Product(['reorder_level' => 5, 'is_active' => true, 'show_in_store' => true]), 'categories' => $this->categories()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $variants = $this->variantRows($request);
        $request->validate(['opening_stock' => 'nullable|integer|min:0|max:100000']);

        $product = DB::transaction(function () use ($data, $variants, $request) {
            $product = Product::create($data + ['stock_qty' => 0]);
            $product->update(['slug' => Str::slug($product->name) . '-' . $product->id]);

            // No sizes or colours entered: one plain variant that holds the opening stock.
            $rows = $variants ?: [['size' => null, 'color' => null, 'sku' => null, 'price' => null, 'stock' => (int) $request->input('opening_stock', 0)]];
            foreach ($rows as $row) {
                $variant = $product->variants()->create(['size' => $row['size'], 'color' => $row['color'], 'sku' => $row['sku'], 'price' => $row['price'], 'stock_qty' => 0]);
                if ($row['stock'] > 0) {
                    $this->stock->move($product, 'opening', $row['stock'], $request->user(), 'Opening stock', null, (float) $product->cost_price, $variant);
                }
            }
            $this->saveImages($product, $request);

            return $product;
        });

        return redirect()->route('manager.products.show', $product)->with('success', "{$product->name} added.");
    }

    public function show(Product $product)
    {
        $product->load(['variants', 'images']);
        $sold = $product->items()->whereHas('delivery', fn ($q) => $q->where('status', '!=', 'cancelled'));

        return view('manager.products.show', [
            'product' => $product,
            'movements' => $product->movements()->with(['user', 'delivery', 'variant'])->paginate(20),
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
        return view('manager.products.form', ['product' => $product->load(['variants', 'images']), 'categories' => $this->categories()]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);
        $restock = $request->validate([
            'existing.*.add' => 'nullable|integer|min:0|max:100000',
            'restock_cost' => 'nullable|numeric|min:0',
            'restock_note' => 'nullable|string|max:200',
        ]);

        DB::transaction(function () use ($request, $product, $data, $restock) {
            $product->update($data);

            // Existing variants: size, colour, price and active flag (stock changes go through "Update stock").
            foreach ((array) $request->input('existing', []) as $id => $row) {
                $variant = $product->variants()->find($id);
                if (! $variant) {
                    continue;
                }
                $variant->update([
                    'size' => $this->clean($row['size'] ?? null), 'color' => $this->clean($row['color'] ?? null),
                    'sku' => $this->clean($row['sku'] ?? null), 'price' => ($row['price'] ?? '') === '' ? null : (float) $row['price'],
                    'is_active' => ! empty($row['is_active']),
                ]);

                // New units that arrived: added to the stock already on hand, with a line in the stock history.
                $add = (int) ($row['add'] ?? 0);
                if ($add > 0) {
                    $this->stock->move($product, 'purchase', $add, $request->user(), ($restock['restock_note'] ?? null) ?: 'Stock added', null,
                        ! empty($restock['restock_cost']) ? (float) $restock['restock_cost'] : null, $variant);
                }
            }
            $this->assertNoDuplicateVariants($product->fresh('variants')->variants->map(fn ($v) => [$v->size, $v->color])->all());

            foreach ($this->variantRows($request) as $row) {
                $variant = $product->variants()->create(['size' => $row['size'], 'color' => $row['color'], 'sku' => $row['sku'], 'price' => $row['price'], 'stock_qty' => 0]);
                if ($row['stock'] > 0) {
                    $this->stock->move($product, 'opening', $row['stock'], $request->user(), 'Opening stock', null, (float) $product->cost_price, $variant);
                }
            }
            $this->saveImages($product, $request);
        });
        $this->stock->checkAlert($product->fresh()); // the reorder level may have changed

        return redirect()->route('manager.products.show', $product)->with('success', 'Product updated.');
    }

    /** Receive, return, count-correct or write off stock for one size/colour. */
    public function adjust(Request $request, Product $product)
    {
        $data = $request->validate([
            'variant_id' => ['required', Rule::exists('product_variants', 'id')->where('product_id', $product->id)],
            'action' => ['required', Rule::in(array_keys(self::ACTIONS))],
            'qty' => 'required|integer|min:1|max:100000',
            'unit_cost' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:200',
        ]);
        [, $type, $sign] = self::ACTIONS[$data['action']];
        $this->stock->move($product, $type, $sign * (int) $data['qty'], $request->user(), $data['note'] ?? self::ACTIONS[$data['action']][0],
            null, $data['action'] === 'purchase' && ! empty($data['unit_cost']) ? (float) $data['unit_cost'] : null, ProductVariant::findOrFail($data['variant_id']));

        return back()->with('success', 'Stock updated.');
    }

    /** Add a product to (or take it out of) Today's deals on the store. */
    public function deal(Product $product)
    {
        $product->update(['is_deal' => ! $product->is_deal]);

        return back()->with('success', $product->is_deal ? "{$product->name} is now in Today's deals." : "{$product->name} removed from Today's deals.");
    }

    public function toggle(Product $product)
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('success', $product->is_active ? 'Product re-activated.' : 'Product hidden from new orders.');
    }

    public function deleteImage(ProductImage $image)
    {
        $this->images->delete($image);

        return back()->with('success', 'Photo removed.');
    }

    public function mainImage(ProductImage $image)
    {
        $this->images->makeMain($image);

        return back()->with('success', 'Main photo changed.');
    }

    /** Where low-stock texts are sent: the signed-in manager's own phone. */
    public function alertPhone(Request $request)
    {
        $request->merge(['phone' => Phone::clean($request->input('phone'))]);
        $data = $request->validate(['phone' => ['required', 'regex:/^0\d{9}$/', Rule::unique('users', 'phone')->ignore($request->user()->id)]]);
        $request->user()->update($data);

        return back()->with('success', 'Low-stock alerts will be texted to ' . $data['phone'] . '.');
    }

    // ----------------------------------------------------------------------------------------

    private function categories()
    {
        return Product::whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
    }

    private function clean(?string $v): ?string
    {
        $v = trim((string) $v);

        return $v === '' ? null : mb_substr($v, 0, 40);
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'sku' => ['nullable', 'string', 'max:40', Rule::unique('products', 'sku')->ignore($product?->id)],
            'category' => 'nullable|string|max:60',
            'description' => 'nullable|string|max:3000',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'required|integer|min:0|max:100000',
            'notes' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:' . ProductImageService::MAX_PER_PRODUCT,
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:6144',
        ]);
        unset($data['images']);
        $data['is_active'] = $product ? $product->is_active : true;
        $data['show_in_store'] = $request->boolean('show_in_store');
        $data['is_deal'] = $request->boolean('is_deal');

        return $data;
    }

    /** New variant rows from the form: size, colour, optional price and starting stock. */
    private function variantRows(Request $request): array
    {
        $rows = [];
        foreach ((array) $request->input('new', []) as $r) {
            $size = $this->clean($r['size'] ?? null);
            $color = $this->clean($r['color'] ?? null);
            if (! $size && ! $color) {
                continue;
            }
            $rows[] = ['size' => $size, 'color' => $color, 'sku' => $this->clean($r['sku'] ?? null),
                'price' => ($r['price'] ?? '') === '' ? null : max(0, (float) $r['price']), 'stock' => max(0, (int) ($r['stock'] ?? 0))];
        }
        $this->assertNoDuplicateVariants(array_map(fn ($r) => [$r['size'], $r['color']], $rows));

        return $rows;
    }

    private function assertNoDuplicateVariants(array $pairs): void
    {
        $seen = [];
        foreach ($pairs as [$size, $color]) {
            $key = mb_strtolower(trim((string) $size)) . '|' . mb_strtolower(trim((string) $color));
            if (isset($seen[$key])) {
                throw ValidationException::withMessages(['new' => 'The same size and colour is listed twice.']);
            }
            $seen[$key] = true;
        }
    }

    private function saveImages(Product $product, Request $request): void
    {
        foreach ((array) $request->file('images', []) as $file) {
            $this->images->add($product, $file);
        }
    }
}
