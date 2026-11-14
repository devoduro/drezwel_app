@extends('layouts.shop')
@section('title', 'Your cart · ' . \App\Models\Setting::get('business_name'))
@php $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2); $tel = \App\Models\Setting::phoneLink(); $count = $lines->sum('qty'); @endphp
@section('content')
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 py-3 space-y-3">
@if($errors->has('cart'))<div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first('cart') }}</div>@endif

@if($lines->isEmpty())
    <div class="rounded-xl bg-white p-12 text-center">
        <span class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-jm-bg text-4xl text-slate-300"><i class="fas fa-cart-shopping"></i></span>
        <h1 class="mt-4 text-xl font-bold">Your cart is empty!</h1>
        <p class="mt-1 text-jm-grey">Browse our categories and discover our best deals!</p>
        <a href="{{ route('shop.index') }}" class="mt-5 inline-block rounded-lg bg-jm-orange px-8 py-3 text-sm font-bold uppercase tracking-wide text-white shadow transition hover:bg-[#e07b10]">Start shopping</a>
    </div>
@else
    <div class="grid items-start gap-3 lg:grid-cols-[1fr_300px]">
        {{-- Items --}}
        <section class="overflow-hidden rounded-xl bg-white">
            <div class="border-b border-slate-200 px-4 py-3 text-[17px] font-bold">Cart ({{ $count }})</div>
            <div class="divide-y divide-slate-100">
                @foreach($lines as $l)
                    @php $p = $l->variant->product; $off = $p->discount_percent; @endphp
                    <div class="p-4">
                        <div class="flex gap-3 sm:gap-4">
                            <a href="{{ route('shop.product', $p->slug) }}" class="h-[88px] w-[88px] shrink-0 overflow-hidden rounded-lg bg-slate-100 sm:h-24 sm:w-24">
                                @if($p->primary_image_url)<img src="{{ $p->primary_image_url }}" alt="" class="h-full w-full object-cover">@else<span class="grid h-full place-items-center text-3xl text-slate-300"><i class="fas fa-shirt"></i></span>@endif
                            </a>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <a href="{{ route('shop.product', $p->slug) }}" class="line-clamp-2 text-[15px] leading-snug text-jm-dark hover:text-jm-orange">{{ $p->name }}</a>
                                        @unless($l->variant->is_plain)<div class="mt-0.5 text-[13px] text-jm-grey">{{ $l->variant->label }}</div>@endunless
                                        @if($l->variant->stock_qty <= 5)<div class="mt-0.5 text-[12px] text-jm-red">Few units left</div>@endif
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <div class="text-[17px] font-medium">{{ $m($l->price) }}</div>
                                        @if($off)<div class="text-[12px] text-jm-grey line-through">{{ $m($p->compare_at_price) }}</div><span class="rounded bg-[#fdecd5] px-1.5 py-0.5 text-[11px] font-bold text-jm-orange">-{{ $off }}%</span>@endif
                                    </div>
                                </div>
                                @if($l->short)<div class="mt-1 text-xs text-amber-700">Only {{ $l->qty }} available, so we adjusted the quantity.</div>@endif
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <form method="POST" action="{{ route('shop.cart.update') }}">@csrf<input type="hidden" name="variant_id" value="{{ $l->variant->id }}"><input type="hidden" name="qty" value="0">
                                <button class="inline-flex items-center gap-1.5 text-[13px] font-bold uppercase text-jm-orange hover:underline"><i class="far fa-trash-can"></i> Remove</button>
                            </form>
                            {{-- Quantity stepper: each button sends the new quantity --}}
                            <form method="POST" action="{{ route('shop.cart.update') }}" class="flex items-center gap-3">@csrf<input type="hidden" name="variant_id" value="{{ $l->variant->id }}">
                                <button name="qty" value="{{ $l->qty - 1 }}" class="grid h-9 w-9 place-items-center rounded bg-jm-orange text-lg font-bold text-white transition hover:bg-[#e07b10]" aria-label="Decrease">−</button>
                                <span class="w-6 text-center font-medium">{{ $l->qty }}</span>
                                <button name="qty" value="{{ $l->qty + 1 }}" @disabled($l->qty >= min(20, $l->variant->stock_qty)) class="grid h-9 w-9 place-items-center rounded bg-jm-orange text-lg font-bold text-white transition hover:bg-[#e07b10] disabled:cursor-not-allowed disabled:opacity-30" aria-label="Increase">+</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="border-t border-slate-200 px-4 py-3"><a href="{{ route('shop.index') }}" class="text-sm font-bold uppercase text-jm-orange hover:underline"><i class="fas fa-chevron-left text-[10px]"></i> Continue shopping</a></div>
        </section>

        {{-- Summary --}}
        <aside class="space-y-3 lg:sticky lg:top-36">
            <div class="overflow-hidden rounded-xl bg-white">
                <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Cart summary</div>
                <div class="space-y-2 p-4 text-[15px]">
                    <div class="flex items-center justify-between"><span>Subtotal</span><span class="text-[18px] font-medium">{{ $m($subtotal) }}</span></div>
                    <div class="flex items-center justify-between text-sm text-jm-grey"><span>Delivery</span><span>{{ $fee > 0 ? $m($fee) : 'Confirmed by phone' }}</span></div>
                    @if($fee > 0)<div class="flex items-center justify-between border-t pt-2"><span class="font-bold">Total</span><span class="text-[18px] font-bold">{{ $m($total) }}</span></div>@endif
                    <p class="pt-1 text-xs text-jm-grey">{{ $fee > 0 ? '' : 'Delivery fees not included yet. ' }}You pay when your order arrives.</p>
                </div>
                <div class="border-t border-slate-200 p-3">
                    <a href="{{ route('shop.checkout') }}" class="flex items-center justify-between rounded-lg bg-jm-orange px-4 py-3 text-[15px] font-bold uppercase tracking-wide text-white shadow transition hover:bg-[#e07b10]"><span>Checkout</span><span>({{ $m($total) }})</span></a>
                </div>
            </div>
            @if($tel)
                <div class="rounded-xl bg-white p-4 text-sm"><div class="font-bold"><i class="fas fa-phone-volume text-jm-orange"></i> Order by phone</div><a class="font-bold text-jm-orange" href="tel:{{ $tel }}">{{ \App\Models\Setting::get('phone') }}</a><div class="text-jm-grey">We can place the order for you.</div></div>
            @endif
        </aside>
    </div>
@endif
</div>
@endsection
