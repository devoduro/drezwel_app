@extends('layouts.shop')
@section('title', 'Checkout · ' . \App\Models\Setting::get('business_name'))
@php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $field = 'mt-1 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-[15px] focus:border-jm-primary focus:outline-none focus:ring-1 focus:ring-jm-primary';
    $open = \App\Models\Setting::get('store_open') === '1';
    $tel = \App\Models\Setting::phoneLink();
    $count = $lines->sum('qty');
    $speso = \App\Services\SpesoClient::configured();
    $step = 'grid h-6 w-6 shrink-0 place-items-center rounded-full bg-jm-primary text-[13px] font-bold text-white';
@endphp
@section('content')
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 py-3 space-y-3">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">Checkout</h1>
        <a href="{{ route('shop.cart') }}" class="text-sm font-bold uppercase text-jm-primary hover:underline"><i class="fas fa-chevron-left text-[10px]"></i> Back to cart</a>
    </div>
    @if(! $open)<div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">Online ordering is paused. Please call {{ \App\Models\Setting::get('phone') ?: 'us' }} to place your order.</div>@endif

    <form method="POST" action="{{ route('shop.checkout.place') }}" class="grid items-start gap-3 lg:grid-cols-[1fr_320px]">@csrf
        <div class="space-y-3">
            {{-- 1. Customer address --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3"><span class="{{ $step }}">1</span><h2 class="text-[15px] font-bold uppercase tracking-wide">Customer address</h2></div>
                <div class="grid gap-4 p-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium">Full name<input name="name" value="{{ old('name') }}" required autocomplete="name" class="{{ $field }}"></label>
                    <label class="block text-sm font-medium">Phone number<input name="phone" value="{{ old('phone') }}" required inputmode="tel" autocomplete="tel" placeholder="0241234567" class="{{ $field }}"></label>
                    <label class="block text-sm font-medium sm:col-span-2">Delivery address<input name="location" value="{{ old('location') }}" required autocomplete="street-address" placeholder="Area, street, landmark, house number" class="{{ $field }}"></label>
                    <label class="block text-sm font-medium sm:col-span-2">Additional information (optional)<textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[15px] focus:border-jm-primary focus:outline-none focus:ring-1 focus:ring-jm-primary" placeholder="Anything the rider should know?">{{ old('notes') }}</textarea></label>
                    <p class="text-xs text-jm-grey sm:col-span-2"><i class="fas fa-circle-info"></i> We text your order updates and delivery code to this number, and the rider will call it.</p>
                </div>
                <div style="position:absolute;left:-9999px" aria-hidden="true"><label>Company website<input name="company_website" tabindex="-1" autocomplete="off"></label></div>
            </section>

            {{-- 2. Delivery details --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3"><span class="{{ $step }}">2</span><h2 class="text-[15px] font-bold uppercase tracking-wide">Delivery details</h2></div>
                <div class="p-4">
                    <div class="flex items-start gap-3 rounded-lg border-2 border-jm-primary bg-[#f3f6f9] p-3">
                        <i class="fas fa-truck-fast mt-0.5 text-xl text-jm-primary"></i>
                        <div class="text-sm"><div class="font-bold">Door delivery</div>
                            <div class="text-[#535357]">Delivery fee: <strong>{{ $fee > 0 ? $m($fee) : 'confirmed when we call you' }}</strong></div>
                            <div class="text-jm-grey">Accra by rider. For other regions we send by station bus or parcel office. We call to confirm before dispatch.</div></div>
                    </div>

                    <div class="mt-4 text-[13px] font-bold uppercase tracking-wide text-jm-grey">Shipment ({{ $count }} {{ $count === 1 ? 'item' : 'items' }})</div>
                    <div class="mt-2 divide-y divide-slate-100 rounded-lg border border-slate-200">
                        @foreach($lines as $l)
                            @php $p = $l->variant->product; @endphp
                            <div class="flex items-center gap-3 p-3">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                    @if($p->primary_image_url)<img src="{{ $p->primary_image_url }}" alt="" class="h-full w-full object-cover">@else<span class="grid h-full place-items-center text-xl text-slate-300"><i class="fas fa-shirt"></i></span>@endif
                                </div>
                                <div class="min-w-0 flex-1 text-sm"><div class="line-clamp-2 leading-snug">{{ $p->name }}</div>@unless($l->variant->is_plain)<div class="text-jm-grey">{{ $l->variant->label }}</div>@endunless<div class="text-jm-grey">Qty: {{ $l->qty }}</div></div>
                                <div class="shrink-0 text-right text-sm font-medium">{{ $m($l->qty * $l->price) }}</div>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('shop.cart') }}" class="mt-3 inline-block text-sm font-bold uppercase text-jm-primary hover:underline">Modify cart</a>
                </div>
            </section>

            {{-- 3. Payment method --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3"><span class="{{ $step }}">3</span><h2 class="text-[15px] font-bold uppercase tracking-wide">Payment method</h2></div>
                @php $payNow = $speso && old('pay_method') === 'speso'; @endphp
                <div class="space-y-3 p-4">
                    @error('pay_method')<div class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</div>@enderror
                    @if($speso)
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border-2 p-3 {{ $payNow ? 'border-jm-primary bg-[#f3f6f9]' : 'border-slate-200' }}" data-pay-card>
                            <input type="radio" name="pay_method" value="speso" @checked($payNow) class="mt-1 accent-[#1c3041]">
                            <div class="text-sm"><div class="font-bold">Pay now with Mobile Money <span class="ml-1 rounded bg-green-100 px-1.5 py-0.5 text-[11px] font-bold text-green-700">Secure</span></div>
                                <div class="text-[#535357]">We send an approval prompt to your phone. Approve it with your MoMo PIN and your order is paid.</div></div>
                        </label>
                        <div id="momo-fields" class="{{ $payNow ? '' : 'hidden' }} grid gap-3 rounded-lg bg-jm-bg p-3 sm:grid-cols-2">
                            <label class="block text-sm font-medium">Mobile money number<input name="momo_number" value="{{ old('momo_number') }}" inputmode="tel" placeholder="Same as above if blank" class="{{ $field }}"></label>
                            <label class="block text-sm font-medium">Network
                                <select name="network" class="{{ $field }}">@foreach(\App\Support\MomoApproval::NETWORKS as $k => $l)<option value="{{ $k }}" @selected(old('network') === $k)>{{ $l }}</option>@endforeach</select></label>
                            @error('momo_number')<p class="text-sm text-red-600 sm:col-span-2">{{ $message }}</p>@enderror
                        </div>
                    @endif
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border-2 p-3 {{ $payNow ? 'border-slate-200' : 'border-jm-primary bg-[#f3f6f9]' }}" data-pay-card>
                        <input type="radio" name="pay_method" value="cod" @checked(! $payNow) class="mt-1 accent-[#1c3041]">
                        <div class="text-sm"><div class="font-bold">Pay on delivery <span class="ml-1 rounded bg-green-100 px-1.5 py-0.5 text-[11px] font-bold text-green-700">Available</span></div>
                            <div class="text-[#535357]">Pay the rider in cash or MoMo when your order arrives. Check your items first.</div></div>
                    </label>
                    <div class="rounded-lg bg-jm-bg p-3 text-xs text-[#535357]"><i class="fas fa-shield-halved text-jm-primary"></i> When your rider sets off, you get a 4-digit code by SMS. Give it to the rider <strong>only after you have received your order</strong>.</div>
                </div>
                <script>
                    (function () {
                        var radios = document.querySelectorAll('input[name=pay_method]'), box = document.getElementById('momo-fields'), btn = document.getElementById('confirm-btn');
                        function sync() {
                            var now = document.querySelector('input[name=pay_method]:checked').value === 'speso';
                            if (box) box.classList.toggle('hidden', !now);
                            if (btn) btn.textContent = now ? 'Pay {{ $m($total) }} now' : 'Confirm order';
                            document.querySelectorAll('[data-pay-card]').forEach(function (c) {
                                var on = c.querySelector('input').checked;
                                c.classList.toggle('border-jm-primary', on); c.classList.toggle('bg-[#f3f6f9]', on); c.classList.toggle('border-slate-200', !on);
                            });
                        }
                        radios.forEach(function (r) { r.addEventListener('change', sync); });
                        sync();
                    })();
                </script>
            </section>
        </div>

        {{-- Order summary --}}
        <aside class="space-y-3 lg:sticky lg:top-36">
            <div class="overflow-hidden rounded-xl bg-white">
                <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Order summary</div>
                <div class="space-y-2 p-4 text-[15px]">
                    <div class="flex justify-between"><span>Item's total ({{ $count }})</span><span>{{ $m($subtotal) }}</span></div>
                    <div class="flex justify-between"><span>Delivery fees</span><span>{{ $fee > 0 ? $m($fee) : 'To be confirmed' }}</span></div>
                    <div class="flex items-center justify-between border-t pt-3"><span class="font-bold">Total</span><span class="text-[20px] font-bold">{{ $m($total) }}</span></div>
                </div>
                <div class="border-t border-slate-200 p-3">
                    <button id="confirm-btn" @disabled(! $open) class="h-12 w-full rounded-lg bg-jm-primary text-[15px] font-bold uppercase tracking-wide text-white shadow transition hover:bg-[#14212d] disabled:cursor-not-allowed disabled:opacity-40">Confirm order</button>
                    <a href="{{ route('shop.cart') }}" class="mt-2 block text-center text-sm font-bold uppercase text-jm-primary hover:underline">Modify cart</a>
                    <p class="mt-3 text-center text-[11px] leading-snug text-jm-grey">By placing your order you accept our <a class="text-jm-primary hover:underline" href="{{ route('site.page', 'terms') }}" target="_blank">Terms &amp; conditions</a> and <a class="text-jm-primary hover:underline" href="{{ route('site.page', 'return-policy') }}" target="_blank">Return policy</a>.</p>
                </div>
            </div>
            @if($tel)<div class="rounded-xl bg-white p-4 text-sm"><div class="font-bold"><i class="fas fa-headset text-jm-primary"></i> Need help?</div><div class="text-jm-grey">Call <a class="font-bold text-jm-primary" href="tel:{{ $tel }}">{{ \App\Models\Setting::get('phone') }}</a></div></div>@endif
        </aside>
    </form>
</div>
@endsection
