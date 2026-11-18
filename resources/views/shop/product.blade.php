@extends('layouts.shop')
@section('title', $product->name . ' · ' . \App\Models\Setting::get('business_name'))
@section('description', \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 150))
@if($product->primary_image_url)@section('og_image', $product->primary_image_url)@endif
@php
    $tel = \App\Models\Setting::phoneLink();
    $sizes = $product->variants->pluck('size')->filter()->unique()->values();
    $colors = $product->variants->pluck('color')->filter()->unique()->values();
    [$min, $max] = $product->priceRange();
    $open = \App\Models\Setting::get('store_open') === '1';
    $off = $product->discount_percent;
@endphp
@section('content')
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 py-3 pb-28 lg:pb-6 space-y-3">
    <nav class="text-[13px] text-jm-grey">
        <a class="hover:text-jm-primary" href="{{ route('home') }}">Home</a> <i class="fas fa-chevron-right mx-1 text-[8px]"></i>
        <a class="hover:text-jm-primary" href="{{ route('shop.index') }}">Shop</a>
        @if($product->category) <i class="fas fa-chevron-right mx-1 text-[8px]"></i> <a class="hover:text-jm-primary" href="{{ route('shop.index', ['category' => $product->category]) }}">{{ $product->category }}</a>@endif
        <i class="fas fa-chevron-right mx-1 text-[8px]"></i> <span class="text-jm-dark">{{ $product->name }}</span>
    </nav>

    <div class="grid gap-3 lg:grid-cols-[1fr_270px]">
        {{-- Main card: photos + details --}}
        <section class="grid gap-6 rounded-xl bg-white p-4 sm:p-5 md:grid-cols-[minmax(0,420px)_1fr]">
            <div>
                <div class="relative aspect-square overflow-hidden rounded-lg bg-white ring-1 ring-slate-200">
                    @if($product->images->count())
                        <img id="mainPhoto" src="{{ $product->images->first()->url }}" alt="{{ $product->name }}" class="h-full w-full cursor-zoom-in object-contain" title="Click to view full size">
                        <button type="button" id="zoomBtn" class="absolute bottom-2 right-2 grid h-9 w-9 place-items-center rounded-full bg-white/90 text-jm-dark shadow" aria-label="View full size"><i class="fas fa-expand"></i></button>
                    @else
                        <div class="grid h-full place-items-center text-7xl text-slate-300"><i class="fas fa-shirt"></i></div>
                    @endif
                    @if($off)<span class="absolute right-0 top-3 rounded-l bg-jm-primary px-2 py-1 text-sm font-bold text-white shadow">-{{ $off }}%</span>@endif
                </div>
                @if($product->images->count() > 1)
                    <div class="no-scrollbar mt-2 flex gap-2 overflow-x-auto">
                        @foreach($product->images as $img)
                            <button type="button" class="thumb h-[68px] w-[68px] shrink-0 overflow-hidden rounded-lg border-2 {{ $loop->first ? 'border-jm-primary' : 'border-slate-200' }}" data-src="{{ $img->url }}"><img src="{{ $img->url }}" alt="" class="h-full w-full object-cover"></button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="min-w-0">
                <h1 class="text-[20px] sm:text-[22px] font-medium leading-snug text-jm-dark">{{ $product->name }}</h1>
                @if($product->category)<div class="mt-1 text-sm"><a class="text-jm-primary hover:underline" href="{{ route('shop.index', ['category' => $product->category]) }}">Similar products from {{ $product->category }}</a></div>@endif
                <hr class="my-3 border-slate-200">

                <div class="text-[26px] sm:text-[28px] font-medium leading-tight text-jm-dark" id="price">{{ $min == $max ? 'GH₵ ' . number_format($min, 2) : 'GH₵ ' . number_format($min, 2) . ' – ' . number_format($max, 2) }}</div>
                @if($off)
                    <div class="mt-1 flex items-center gap-2"><span class="text-base text-jm-grey line-through">GH₵ {{ number_format($product->compare_at_price, 2) }}</span><span class="rounded bg-[#e4eaf0] px-2 py-0.5 text-sm font-bold text-jm-primary">-{{ $off }}%</span></div>
                @endif
                <div id="stockNote" class="mt-2 text-sm font-medium"></div>
                <div class="mt-1 text-xs text-jm-grey">Delivery fee, if any, is confirmed when we call you.</div>

                <form method="POST" action="{{ route('shop.cart.add') }}" id="buyForm">@csrf
                    <input type="hidden" name="variant_id" id="variantId" value="{{ $product->has_options ? '' : ($product->variants->first()->id ?? '') }}">

                    @if($sizes->count() || $colors->count())
                        <hr class="my-4 border-slate-200">
                        <div class="mb-3 text-[12px] font-bold uppercase tracking-wide text-jm-grey">Variation available</div>
                    @endif
                    @if($sizes->count())
                        <div class="mb-4">
                            <div class="mb-1.5 text-sm">Size: <span id="sizeName" class="font-bold"></span></div>
                            <div class="flex flex-wrap gap-2" id="sizeBtns">
                                @foreach($sizes as $s)<button type="button" data-size="{{ $s }}" class="opt-size min-w-12 rounded border-2 border-slate-200 bg-white px-3 py-2 text-sm font-medium transition" data-label="{{ $s }}">{{ $s }}</button>@endforeach
                            </div>
                        </div>
                    @endif
                    @if($colors->count())
                        <div class="mb-4">
                            <div class="mb-1.5 text-sm">Colour: <span id="colorName" class="font-bold"></span></div>
                            <div class="flex flex-wrap gap-2" id="colorBtns">
                                @foreach($colors as $c)<button type="button" data-color="{{ $c }}" class="opt-color rounded border-2 border-slate-200 bg-white px-3 py-2 text-sm font-medium transition" data-label="{{ $c }}">{{ $c }}</button>@endforeach
                            </div>
                        </div>
                    @endif
                    @error('variant_id')<div class="mb-2 text-sm text-red-600">{{ $message }}</div>@enderror

                    {{-- On a phone this bar sticks to the bottom of the screen, like Jumia's --}}
                    <div class="mt-5 flex items-center gap-3 max-lg:fixed max-lg:inset-x-0 max-lg:bottom-0 max-lg:z-30 max-lg:mt-0 max-lg:bg-white max-lg:p-3 max-lg:shadow-[0_-4px_14px_rgba(0,0,0,.15)]">
                        <div class="flex shrink-0 items-center rounded-lg border border-slate-300 bg-white">
                            <button type="button" id="minus" class="h-11 w-10 text-xl text-jm-primary" aria-label="Less">−</button>
                            <input type="number" name="qty" id="qty" value="1" min="1" max="20" class="h-11 w-11 border-0 text-center font-bold focus:outline-none" aria-label="Quantity">
                            <button type="button" id="plus" class="h-11 w-10 text-xl text-jm-primary" aria-label="More">+</button>
                        </div>
                        <button id="addBtn" class="flex h-11 flex-1 items-center justify-center gap-2 rounded-lg bg-jm-primary text-[15px] font-bold uppercase tracking-wide text-white shadow transition hover:bg-[#14212d] disabled:cursor-not-allowed disabled:opacity-40" disabled><i class="fas fa-cart-shopping"></i> <span id="addLabel">Add to cart</span></button>
                    </div>
                </form>
                @unless($open)<div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">Online ordering is paused. Please call us to order.</div>@endunless
            </div>
        </section>

        {{-- Delivery & returns --}}
        <aside class="space-y-3 text-sm">
            <div class="overflow-hidden rounded-xl bg-white">
                <div class="border-b border-slate-200 px-4 py-3 text-[13px] font-bold uppercase tracking-wide text-jm-dark">Delivery &amp; returns</div>
                <ul class="space-y-4 p-4">
                    <li class="flex gap-3"><i class="fas fa-truck-fast mt-0.5 w-5 text-jm-primary"></i><div><div class="font-medium">Door delivery</div><div class="text-jm-grey">Accra by rider. Other regions by station bus or parcel office.</div></div></li>
                    <li class="flex gap-3"><i class="fas fa-store mt-0.5 w-5 text-jm-primary"></i><div><div class="font-medium">Pick-up</div><div class="text-jm-grey">Collect from us{{ \App\Models\Setting::get('address') ? ': ' . \App\Models\Setting::get('address') : '' }}.</div></div></li>
                    <li class="flex gap-3"><i class="fas fa-hand-holding-dollar mt-0.5 w-5 text-jm-primary"></i><div><div class="font-medium">Pay on delivery</div><div class="text-jm-grey">Cash or MoMo when it arrives.</div></div></li>
                    <li class="flex gap-3"><i class="fas fa-rotate-left mt-0.5 w-5 text-jm-primary"></i><div><div class="font-medium">Returns</div><a class="text-jm-primary hover:underline" href="{{ route('site.page', 'return-policy') }}">Read our return policy</a></div></li>
                </ul>
            </div>
            @if($tel)
                <div class="rounded-xl bg-jm-primary p-4 text-white"><i class="fas fa-phone-volume text-xl"></i>
                    <div class="mt-1 font-bold">Prefer to order by phone?</div>
                    <a class="text-lg font-bold hover:underline" href="tel:{{ $tel }}">{{ \App\Models\Setting::get('phone') }}</a>
                    <div class="text-xs text-white/90">Quote: {{ $product->name }}</div></div>
            @endif
        </aside>
    </div>

    {{-- Product details --}}
    <section class="overflow-hidden rounded-xl bg-white">
        <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Product details</div>
        <div class="grid gap-6 p-4 sm:p-5 md:grid-cols-2">
            <div>
                @if($product->description)<p class="whitespace-pre-line leading-relaxed text-[#535357]">{{ $product->description }}</p>
                @else<p class="text-jm-grey">Call us for more details about this item.</p>@endif
            </div>
            <div>
                <div class="mb-2 text-[13px] font-bold uppercase tracking-wide text-jm-grey">Specifications</div>
                <dl class="divide-y divide-slate-100 rounded-lg bg-slate-50 text-sm">
                    @foreach(array_filter(['Category' => $product->category, 'SKU' => $product->sku, 'Sizes' => $sizes->implode(', '), 'Colours' => $colors->implode(', ')]) as $k => $v)
                        <div class="flex gap-4 px-3 py-2"><dt class="w-24 shrink-0 text-jm-grey">{{ $k }}</dt><dd class="font-medium">{{ $v }}</dd></div>
                    @endforeach
                </dl>
            </div>
        </div>
    </section>

    @if($related->count())
        @include('shop._row', ['title' => 'You may also like', 'products' => $related, 'url' => route('shop.index')])
    @endif
</div>

@if($product->images->count())
<div id="lightbox" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/90 p-4" role="dialog" aria-label="Product photo">
    <button type="button" id="lbClose" class="absolute right-4 top-4 grid h-11 w-11 place-items-center rounded-full bg-white/15 text-xl text-white hover:bg-white/25" aria-label="Close"><i class="fas fa-xmark"></i></button>
    <button type="button" id="lbPrev" class="absolute left-3 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white/15 text-white hover:bg-white/25" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
    <img id="lbImg" src="" alt="{{ $product->name }}" class="max-h-full max-w-full rounded-lg object-contain">
    <button type="button" id="lbNext" class="absolute right-3 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white/15 text-white hover:bg-white/25" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
    <div id="lbCount" class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-white/15 px-3 py-1 text-sm text-white"></div>
</div>
@endif

<script>
(function () {
    const variants = @json($variants), hasOptions = @json($product->has_options), open = @json($open);
    let size = null, color = null;
    const el = id => document.getElementById(id), qty = el('qty');
    const fmt = n => 'GH₵ ' + Number(n).toFixed(2);

    // photo gallery
    document.querySelectorAll('.thumb').forEach(b => b.addEventListener('click', () => {
        el('mainPhoto').src = b.dataset.src;
        document.querySelectorAll('.thumb').forEach(x => { x.classList.remove('border-jm-primary'); x.classList.add('border-slate-200'); });
        b.classList.remove('border-slate-200'); b.classList.add('border-jm-primary');
    }));

    // full-size viewer: click the photo to enlarge, arrows / swipe / Esc to browse and close
    const photos = @json($product->images->pluck('url')->values());
    if (photos.length) {
        let cur = 0;
        const box = el('lightbox'), img = el('lbImg');
        const show = i => { cur = (i + photos.length) % photos.length; img.src = photos[cur]; el('lbCount').textContent = photos.length > 1 ? (cur + 1) + ' / ' + photos.length : ''; };
        const openBox = () => { const i = photos.indexOf(el('mainPhoto').getAttribute('src')); show(i < 0 ? 0 : i); box.classList.remove('hidden'); box.classList.add('flex'); document.body.style.overflow = 'hidden'; };
        const closeBox = () => { box.classList.add('hidden'); box.classList.remove('flex'); document.body.style.overflow = ''; };
        el('mainPhoto').addEventListener('click', openBox); el('zoomBtn').addEventListener('click', openBox);
        el('lbClose').addEventListener('click', closeBox);
        box.addEventListener('click', e => { if (e.target === box) closeBox(); });
        el('lbPrev').addEventListener('click', () => show(cur - 1)); el('lbNext').addEventListener('click', () => show(cur + 1));
        if (photos.length < 2) { el('lbPrev').classList.add('hidden'); el('lbNext').classList.add('hidden'); }
        document.addEventListener('keydown', e => { if (box.classList.contains('hidden')) return; if (e.key === 'Escape') closeBox(); if (e.key === 'ArrowLeft') show(cur - 1); if (e.key === 'ArrowRight') show(cur + 1); });
        let x0 = null;
        box.addEventListener('touchstart', e => { x0 = e.touches[0].clientX; }, {passive: true});
        box.addEventListener('touchend', e => { if (x0 === null) return; const dx = e.changedTouches[0].clientX - x0; if (Math.abs(dx) > 50) show(cur + (dx < 0 ? 1 : -1)); x0 = null; });
    }

    const find = () => variants.find(v => (v.size || null) === size && (v.color || null) === color);
    const hasSizes = variants.some(v => v.size), hasColors = variants.some(v => v.color);
    // Is any variant with these options in stock?
    const available = (s, c) => variants.some(v => (s === undefined || (v.size || null) === s) && (c === undefined || (v.color || null) === c) && v.stock > 0);
    const chip = 'rounded border-2 px-3 py-2 text-sm font-medium transition ';

    function paint() {
        document.querySelectorAll('.opt-size').forEach(b => {
            const on = b.dataset.size === size, ok = available(b.dataset.size, hasColors ? color ?? undefined : undefined);
            b.className = 'opt-size min-w-12 ' + chip + (on ? 'border-jm-primary bg-[#e4eaf0] text-jm-primary' : ok ? 'border-slate-200 bg-white hover:border-jm-primary' : 'border-slate-100 bg-slate-50 text-slate-300 line-through');
        });
        document.querySelectorAll('.opt-color').forEach(b => {
            const on = b.dataset.color === color, ok = available(hasSizes ? size ?? undefined : undefined, b.dataset.color);
            b.className = 'opt-color ' + chip + (on ? 'border-jm-primary bg-[#e4eaf0] text-jm-primary' : ok ? 'border-slate-200 bg-white hover:border-jm-primary' : 'border-slate-100 bg-slate-50 text-slate-300 line-through');
        });
        if (el('sizeName')) el('sizeName').textContent = size || '';
        if (el('colorName')) el('colorName').textContent = color || '';

        const v = hasOptions ? ((hasSizes ? size : true) !== null && (hasColors ? color : true) !== null ? find() : null) : variants[0];
        const note = el('stockNote'), btn = el('addBtn');
        if (hasOptions && !v) {
            // nothing chosen yet: show what is left in total; it changes as soon as a size / colour is picked
            const left = variants.reduce((a, x) => a + Math.max(x.stock, 0), 0);
            el('variantId').value = '';
            note.textContent = left <= 0 ? 'Sold out' : left <= 5 ? 'Few units left: only ' + left : 'In stock';
            note.className = 'mt-2 text-sm font-medium ' + (left <= 0 ? 'text-jm-red' : left <= 5 ? 'text-jm-red' : 'text-green-700');
            btn.disabled = true; return;
        }
        if (!v) { btn.disabled = true; return; }
        el('variantId').value = v.id; el('price').textContent = fmt(v.price); qty.max = Math.min(20, v.stock);
        if (+qty.value > v.stock) qty.value = Math.max(1, v.stock);
        if (v.stock <= 0) { note.textContent = 'Sold out in this option'; note.className = 'mt-2 text-sm font-medium text-jm-red'; btn.disabled = true; }
        else { note.textContent = v.stock <= 5 ? 'Few units left: only ' + v.stock : 'In stock'; note.className = 'mt-2 text-sm font-medium ' + (v.stock <= 5 ? 'text-jm-red' : 'text-green-700'); btn.disabled = !open; }
        el('addLabel').textContent = open ? 'Add to cart' : 'Ordering paused';
    }
    document.querySelectorAll('.opt-size').forEach(b => b.addEventListener('click', () => { size = size === b.dataset.size ? null : b.dataset.size; paint(); }));
    document.querySelectorAll('.opt-color').forEach(b => b.addEventListener('click', () => { color = color === b.dataset.color ? null : b.dataset.color; paint(); }));
    el('minus').addEventListener('click', () => { qty.value = Math.max(1, +qty.value - 1); });
    el('plus').addEventListener('click', () => { qty.value = Math.min(+qty.max || 20, +qty.value + 1); });
    paint();
})();
</script>
@endsection
