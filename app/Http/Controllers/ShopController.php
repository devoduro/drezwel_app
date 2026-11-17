<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryZone;
use App\Models\Region;
use App\Services\DeliveryFees;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Services\SalesService;
use App\Services\SpesoClient;
use App\Services\SpesoPayments;
use App\Support\MomoApproval;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Throwable;
use Illuminate\Validation\ValidationException;

/** The public online store: browse, cart, and checkout (pay on delivery). */
class ShopController extends Controller
{
    public function __construct(private SalesService $sales, private SpesoPayments $payments) {}

    public function index(Request $request)
    {
        $term = trim((string) $request->input('q'));
        $base = fn () => Product::inStore()->with(['images', 'variants'])->whereHas('variants', fn ($q) => $q->where('is_active', true));
        $categoryCounts = Product::inStore()->whereNotNull('category')->where('category', '!=', '')
            ->selectRaw('category, COUNT(*) as n')->groupBy('category')->orderBy('category')->pluck('n', 'category');

        $data = ['categories' => $categoryCounts->keys(), 'categoryCounts' => $categoryCounts, 'products' => null, 'sections' => collect()];
        $filtered = $term !== '' || $request->filled('category') || $request->boolean('deals') || $request->filled('min_price') || $request->filled('max_price') || $request->filled('sort');

        if ($filtered) {
            // Searching, filtering or sorting: a plain paged grid, like a Jumia listing.
            $sort = $request->input('sort');
            $data['products'] = $base()
                ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('category', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%")))
                ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
                ->when($request->boolean('deals'), fn ($q) => $q->onOffer())
                ->when($request->filled('min_price'), fn ($q) => $q->where('selling_price', '>=', (float) $request->input('min_price')))
                ->when($request->filled('max_price'), fn ($q) => $q->where('selling_price', '<=', (float) $request->input('max_price')))
                ->orderByRaw('stock_qty <= 0')
                ->when($sort === 'price_asc', fn ($q) => $q->orderBy('selling_price'))
                ->when($sort === 'price_desc', fn ($q) => $q->orderByDesc('selling_price'))
                ->latest('id')->paginate(20)->withQueryString();

            return view('shop.index', $data);
        }

        // The shop front: Today's deals, New arrivals, then a sideways row for each category.
        $sections = collect();
        $deals = $base()->onOffer()->where('stock_qty', '>', 0)->latest('id')->limit(12)->get();
        if ($deals->isNotEmpty()) {
            $sections->push(['title' => "Today's deals", 'products' => $deals, 'url' => route('shop.index', ['deals' => 1]), 'deal' => true]);
        }
        $new = $base()->where('stock_qty', '>', 0)->latest('id')->limit(12)->get();
        if ($new->isNotEmpty()) {
            $sections->push(['title' => 'New arrivals', 'products' => $new, 'url' => route('shop.index', ['sort' => 'new'])]);
        }
        foreach ($categoryCounts->keys() as $cat) {
            $items = $base()->where('category', $cat)->orderByRaw('stock_qty <= 0')->latest('id')->limit(12)->get();
            if ($items->isNotEmpty()) {
                $sections->push(['title' => $cat, 'products' => $items, 'url' => route('shop.index', ['category' => $cat])]);
            }
        }
        $other = $base()->where(fn ($q) => $q->whereNull('category')->orWhere('category', ''))->latest('id')->limit(12)->get();
        if ($other->isNotEmpty()) {
            $sections->push(['title' => 'More to explore', 'products' => $other, 'url' => null]);
        }
        $data['sections'] = $sections;

        return view('shop.index', $data);
    }

    public function product(string $slug)
    {
        $product = Product::inStore()->with(['images', 'variants' => fn ($q) => $q->where('is_active', true)])->where('slug', $slug)->firstOrFail();
        // Up to 7 suggestions for a sideways-scrolling row: same category first, then other in-stock items,
        // then (if the shop is small) sold-out ones, so the row is never left with a lone card.
        $suggest = fn () => Product::inStore()->with(['images', 'variants'])->where('id', '!=', $product->id)->inRandomOrder();
        $related = $product->category ? $suggest()->where('stock_qty', '>', 0)->where('category', $product->category)->limit(7)->get() : collect();
        foreach ([true, false] as $inStockOnly) {
            if ($related->count() < 7) {
                $related = $related->concat($suggest()->whereNotIn('id', $related->pluck('id'))->when($inStockOnly, fn ($q) => $q->where('stock_qty', '>', 0))->limit(7 - $related->count())->get());
            }
        }

        return view('shop.product', [
            'product' => $product,
            'related' => $related,
            'variants' => $product->variants->map(fn ($v) => ['id' => $v->id, 'size' => $v->size, 'color' => $v->color, 'price' => $v->selling_price, 'stock' => $v->stock_qty])->values(),
        ]);
    }

    // ---- cart (kept in the session: variant id => quantity)

    public function cart(Request $request)
    {
        return view('shop.cart', $this->cartData($request));
    }

    public function add(Request $request)
    {
        $data = $request->validate(['variant_id' => 'required|integer', 'qty' => 'nullable|integer|min:1|max:20']);
        $variant = ProductVariant::with('product')->find($data['variant_id']);
        if (! $variant || ! $variant->is_active || ! $variant->product->is_active || ! $variant->product->show_in_store) {
            return back()->withErrors(['variant_id' => 'That item is not available.']);
        }
        if ($variant->stock_qty <= 0) {
            return back()->withErrors(['variant_id' => 'Sorry, that size/colour is sold out.']);
        }

        $cart = $request->session()->get('cart', []);
        $cart[$variant->id] = min(($cart[$variant->id] ?? 0) + (int) ($data['qty'] ?? 1), $variant->stock_qty, 20);
        $request->session()->put('cart', $cart);

        // The "+" on a product tile keeps the customer browsing; the product page's button goes to the cart.
        if ($request->boolean('stay')) {
            return back()->with('success', "{$variant->product->name} added to your cart.")->with('cart_added', true);
        }

        return redirect()->route('shop.cart')->with('success', "{$variant->product->name} added to your cart.");
    }

    public function update(Request $request)
    {
        $data = $request->validate(['variant_id' => 'required|integer', 'qty' => 'required|integer|min:0|max:20']);
        $cart = $request->session()->get('cart', []);
        if (isset($cart[$data['variant_id']])) {
            $v = ProductVariant::find($data['variant_id']);
            $qty = $v ? min($data['qty'], $v->stock_qty) : 0;
            $qty > 0 ? $cart[$data['variant_id']] = $qty : $cart = array_diff_key($cart, [$data['variant_id'] => 1]);
            $request->session()->put('cart', $cart);
        }

        return redirect()->route('shop.cart');
    }

    public function checkout(Request $request)
    {
        $data = $this->cartData($request);
        if ($data['lines']->isEmpty()) {
            return redirect()->route('shop.cart');
        }

        return view('shop.checkout', $data + ['regions' => DeliveryFees::catalogue()]);
    }

    public function placeOrder(Request $request)
    {
        abort_if($request->filled('company_website'), 422); // honeypot: real customers never see this field
        if (Setting::get('store_open') !== '1') {
            return back()->withErrors(['store' => 'The store is not taking online orders right now. Please call us.']);
        }

        $request->merge(['phone' => Phone::clean($request->input('phone'))]);
        if ($request->input('zone_id') === 'other') {
            $request->merge(['zone_id' => null]);
        }
        $byRegion = $request->filled('region_id');
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'phone' => ['required', 'regex:/^0\d{9}$/'],
            'region_id' => 'nullable|exists:regions,id',
            'zone_id' => 'nullable|integer',
            'address' => $byRegion ? 'required|string|max:200' : 'nullable|string|max:200',
            'location' => $byRegion ? 'nullable|string|max:200' : 'required|string|max:200',
            'notes' => 'nullable|string|max:500',
            'pay_method' => ['nullable', 'in:cod,speso'],
        ] + $this->momoRules($request, $request->input('pay_method') === 'speso'),
            ['phone.regex' => 'Enter a 10-digit Ghana phone number, e.g. 0241234567.', 'location.required' => 'Tell us where to deliver.', 'address.required' => 'Tell us the street, landmark or house number.', 'momo_number.regex' => 'Enter the 10-digit mobile money number, e.g. 0241234567.']);
        $online = ($data['pay_method'] ?? 'cod') === 'speso';
        if ($online && ! SpesoClient::configured()) {
            return back()->withInput()->withErrors(['pay_method' => 'Online payment is not available right now. Please choose pay on delivery.']);
        }

        // Where to, and what it costs: the fee comes from the region / town the customer picked, never from the browser.
        $location = $data['location'] ?? '';
        $fee = (float) Setting::get('default_delivery_fee', '0');
        if ($byRegion) {
            $region = Region::findOrFail($data['region_id']);
            $zone = ! empty($data['zone_id']) ? DeliveryZone::orderable()->where('region_id', $region->id)->find($data['zone_id']) : null;
            if (! empty($data['zone_id']) && ! $zone) {
                return back()->withInput()->withErrors(['zone_id' => 'Please choose a town from the list.']);
            }
            $fee = DeliveryFees::feeFor($region, $zone);
            $location = trim($data['address']) . ', ' . ($zone ? $zone->name . ', ' : '') . $region->name;
        }

        $cart = $request->session()->get('cart', []);
        $lines = collect($cart)->map(fn ($qty, $id) => ['variant_id' => (int) $id, 'qty' => (int) $qty])->values()->all();

        try {
            $delivery = $this->sales->placeOrder(
                ['name' => $data['name'], 'phone' => $data['phone'], 'location' => mb_substr($location, 0, 200)],
                $lines,
                ['source' => 'online', 'delivery_fee' => $fee, 'notes' => $data['notes'] ?? null, 'update_customer' => false],
            );
        } catch (ValidationException $e) {
            // Stock ran out while they were checking out: send them back to the cart with the reason.
            return redirect()->route('shop.cart')->withErrors(['cart' => collect($e->errors())->flatten()->first()]);
        }

        $request->session()->forget('cart');
        $to = redirect($this->orderUrl($delivery));

        if (! $online) {
            $delivery->update(['payment_method' => 'cod']);
            $this->sales->notifyOnlineOrder($delivery);

            return $to;
        }

        // Pay now: the order exists and holds its stock, but nobody is told until the money arrives.
        try {
            $this->payments->request($delivery, Phone::clean($data['momo_number']), $data['network']);
        } catch (Throwable $e) {
            return $to->with('pay_error', 'We could not send the payment prompt. Check the number and try again, or pay on delivery.');
        }

        return $to;
    }

    /** Pay (or pay again) with mobile money for an order that is not paid yet. */
    public function pay(Request $request, string $reference)
    {
        $delivery = $this->onlineOrder($reference);
        abort_unless($delivery->status === 'pending' && in_array($delivery->payment_status, ['failed', 'awaiting'], true), 404);
        if (! SpesoClient::configured()) {
            return back()->with('pay_error', 'Online payment is not available right now. Please choose pay on delivery.');
        }

        $data = $request->validate($this->momoRules($request, true), ['momo_number.regex' => 'Enter the 10-digit mobile money number, e.g. 0241234567.']);
        // A prompt may already have been approved without us knowing: ask Speso before sending another.
        if ($this->payments->verify($delivery) === 'paid') {
            return redirect($this->orderUrl($delivery));
        }

        try {
            $this->payments->request($delivery->refresh(), Phone::clean($data['momo_number']), $data['network']);
        } catch (Throwable $e) {
            return redirect($this->orderUrl($delivery))->with('pay_error', 'We could not send the payment prompt. Check the number and try again, or pay on delivery.');
        }

        return redirect($this->orderUrl($delivery));
    }

    public function payOnDelivery(string $reference)
    {
        $delivery = $this->onlineOrder($reference);
        // Make sure a payment is not already on its way before switching to cash.
        if ($this->payments->verify($delivery) !== 'paid') {
            $this->payments->switchToCashOnDelivery($delivery);
        }

        return redirect($this->orderUrl($delivery));
    }

    /** Polled by the waiting page: asks Speso (so it works without webhooks) and reports the state. */
    public function paymentStatus(string $reference)
    {
        $delivery = $this->onlineOrder($reference);
        $state = $delivery->payment_status;
        if ($state === 'awaiting') {
            // One Speso lookup per order every few seconds, however many tabs are open.
            Cache::remember("speso-poll:{$delivery->id}", 4, fn () => $this->payments->verify($delivery));
            $state = $delivery->fresh()->payment_status;
        }

        return response()->json(['state' => $state ?? 'none', 'status' => $delivery->fresh()->status]);
    }

    private function onlineOrder(string $reference): Delivery
    {
        return Delivery::with('customer')->where('reference', $reference)->where('source', 'online')->firstOrFail();
    }

    private function orderUrl(Delivery $d): string
    {
        return URL::temporarySignedRoute('shop.order', now()->addDays(7), ['reference' => $d->reference]);
    }

    private function momoRules(Request $request, bool $required): array
    {
        $request->merge(['momo_number' => Phone::clean($request->input('momo_number') ?: $request->input('phone'))]);

        return [
            'momo_number' => [$required ? 'required' : 'nullable', 'regex:/^0\d{9}$/'],
            'network' => [$required ? 'required' : 'nullable', 'in:' . implode(',', array_keys(MomoApproval::NETWORKS))],
        ];
    }

    /** "Thank you" page. The link is signed, so only the person who just ordered (or got the link) can open it. */
    public function order(string $reference)
    {
        $delivery = Delivery::with(['customer', 'items.product', 'items.variant'])->where('reference', $reference)->where('source', 'online')->firstOrFail();

        return view('shop.order', ['delivery' => $delivery]);
    }

    private function cartData(Request $request): array
    {
        $cart = $request->session()->get('cart', []);
        $variants = ProductVariant::with(['product.images'])->whereIn('id', array_keys($cart))->get()->keyBy('id');

        $lines = collect($cart)->map(function ($qty, $id) use ($variants) {
            $v = $variants[$id] ?? null;
            if (! $v || ! $v->is_active || ! $v->product->is_active) {
                return null;
            }

            return (object) ['variant' => $v, 'qty' => min((int) $qty, max($v->stock_qty, 0)), 'price' => $v->selling_price, 'short' => $qty > $v->stock_qty];
        })->filter(fn ($l) => $l && $l->qty > 0)->values();

        $subtotal = $lines->sum(fn ($l) => $l->qty * $l->price);
        $fee = (float) Setting::get('default_delivery_fee', '0');

        return ['lines' => $lines, 'subtotal' => $subtotal, 'fee' => $fee, 'total' => $subtotal + $fee];
    }
}
