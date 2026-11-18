{{-- Product card, Jumia-style: white rounded card, name, bold price, struck-through old price with a -% badge, orange "Add to cart" on hover. --}}
@php
    [$min, $max] = $p->priceRange();
    $soldOut = $p->stock_qty <= 0;
    $off = $p->discount_percent;
    $active = $p->variants->where('is_active', true);
    // One plain variant in stock: the button adds it straight to the cart. Otherwise the customer must pick a size/colour first.
    $quick = $active->count() === 1 && $active->first()->is_plain && $active->first()->stock_qty > 0 ? $active->first() : null;
    $open = \App\Models\Setting::get('store_open') === '1';
    $low = ! $soldOut && $p->stock_qty <= $p->reorder_level;
    $bar = $low ? max(8, min(100, (int) round($p->stock_qty / max($p->reorder_level * 2, 1) * 100))) : 0;
@endphp
<article class="group relative flex flex-col rounded-xl bg-white {{ ($row ?? false) ? 'w-[158px] sm:w-[186px] shrink-0 snap-start' : '' }} transition hover:shadow-[0_4px_16px_rgba(0,0,0,.14)]">
    <a href="{{ route('shop.product', $p->slug) }}" class="block p-2.5 pb-1" aria-label="{{ $p->name }}">
        <div class="relative aspect-[4/5] overflow-hidden rounded-lg bg-slate-100">
            @if($p->primary_image_url)
                <img src="{{ $p->primary_image_url }}" alt="{{ $p->name }}" loading="lazy" class="h-full w-full object-cover {{ $soldOut ? 'opacity-40' : '' }}">
            @else
                <div class="grid h-full w-full place-items-center text-4xl text-slate-300"><i class="fas fa-shirt"></i></div>
            @endif
            @if($soldOut)<span class="absolute left-0 top-2 rounded-r bg-jm-dark px-2 py-0.5 text-[11px] font-bold text-white">SOLD OUT</span>@endif
        </div>
        <div class="mt-2 line-clamp-2 min-h-[2.5rem] text-[14px] leading-5 text-[#535357]">{{ $p->name }}</div>
        <div class="mt-1 text-[18px] font-medium leading-tight text-jm-dark">{{ 'GH₵ ' . number_format((float) $p->selling_price, 2) }}</div>
        @if($off)
            <div class="mt-0.5 flex items-center gap-2"><span class="text-[12px] text-jm-grey line-through">GH₵ {{ number_format($p->compare_at_price, 2) }}</span><span class="rounded bg-[#e4eaf0] px-1.5 py-0.5 text-[12px] font-bold text-jm-primary">-{{ $off }}%</span></div>
        @endif
        @if($low)
            <div class="mt-2 text-[12px] text-[#535357]">{{ $p->stock_qty }} {{ $p->stock_qty === 1 ? 'item' : 'items' }} left</div>
            <div class="mt-1 h-1 overflow-hidden rounded-full bg-slate-200"><div class="h-full rounded-full bg-jm-primary" style="width: {{ $bar }}%"></div></div>
        @endif
    </a>

    {{-- Orange button slides in on hover (always visible on touch screens) --}}
    <div class="px-2.5 pb-2.5 pt-1 lg:absolute lg:inset-x-0 lg:bottom-0 lg:translate-y-2 lg:bg-white lg:opacity-0 lg:shadow-[0_6px_16px_rgba(0,0,0,.14)] lg:rounded-b-xl lg:transition lg:group-hover:translate-y-0 lg:group-hover:opacity-100 lg:z-10">
        @if($soldOut)
            <a href="{{ route('shop.product', $p->slug) }}" class="block rounded-lg bg-slate-200 py-2 text-center text-[13px] font-bold uppercase text-slate-500">Sold out</a>
        @elseif($quick && $open)
            <form method="POST" action="{{ route('shop.cart.add') }}">@csrf
                <input type="hidden" name="variant_id" value="{{ $quick->id }}"><input type="hidden" name="stay" value="1">
                <button class="flex w-full items-center justify-center gap-2 rounded-lg bg-jm-primary py-2 text-[13px] font-bold uppercase tracking-wide text-white transition hover:bg-[#14212d] active:scale-[.98]"><i class="fas fa-cart-shopping"></i> Add to cart</button>
            </form>
        @else
            <a href="{{ route('shop.product', $p->slug) }}" class="flex items-center justify-center gap-2 rounded-lg bg-jm-primary py-2 text-[13px] font-bold uppercase tracking-wide text-white transition hover:bg-[#14212d]"><i class="fas fa-sliders"></i> {{ $quick ? 'View' : 'Select options' }}</a>
        @endif
    </div>
    @if($off)<span class="absolute right-0 top-3 rounded-l bg-jm-primary px-1.5 py-0.5 text-[12px] font-bold text-white shadow">-{{ $off }}%</span>@endif
</article>
