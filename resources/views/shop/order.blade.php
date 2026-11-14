@extends('layouts.shop')
@section('title', 'Order received · ' . \App\Models\Setting::get('business_name'))
@php
    use Illuminate\Support\Facades\URL;
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $tel = \App\Models\Setting::phoneLink();
    $invoice = URL::temporarySignedRoute('invoice.public', now()->addDays(30), ['reference' => $delivery->reference]);
    $pay = $delivery->payment_status;                    // null (pay on delivery) | awaiting | paid | failed | abandoned
    $ref = ['reference' => $delivery->reference];
    $field = 'mt-1 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-[15px] focus:border-jm-primary focus:outline-none focus:ring-1 focus:ring-jm-primary';
@endphp
@section('content')
<div class="max-w-[1180px] mx-auto px-4 py-6">
<div class="mx-auto max-w-2xl">

    @if($pay === 'awaiting')
        <div class="rounded-xl bg-white p-6 text-center" id="waiting">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-[#e4eaf0] text-2xl text-jm-primary"><i class="fas fa-mobile-screen-button fa-beat"></i></div>
            <h1 class="mt-3 text-2xl font-bold">Approve the payment on your phone</h1>
            <p class="mt-1 text-slate-500">We sent a prompt for <strong class="text-slate-800">{{ $m($delivery->balance) }}</strong> to <strong class="text-slate-800">{{ $delivery->payment_mobile }}</strong>. Enter your MoMo PIN to pay for order <strong class="text-slate-800">{{ $delivery->reference }}</strong>.</p>
            <p class="mt-2 text-sm text-slate-400">{{ \App\Support\MomoApproval::hint($delivery->payment_network) }}</p>
            <p class="mt-4 text-sm text-jm-grey"><i class="fas fa-circle-notch fa-spin"></i> Waiting for your payment… this page updates by itself.</p>
        </div>
        <script>
            (function () {
                var url = @json(URL::temporarySignedRoute('shop.order.status', now()->addHours(2), $ref)), stop = false;
                function check() {
                    if (stop) return;
                    fetch(url, {headers: {'Accept': 'application/json'}}).then(function (r) { return r.json(); }).then(function (d) {
                        if (d.state !== 'awaiting') { stop = true; location.reload(); }
                    }).catch(function () {});
                }
                setInterval(check, 4000);
            })();
        </script>
    @elseif($pay === 'failed')
        <div class="rounded-xl bg-white p-6 text-center">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-red-100 text-2xl text-red-600"><i class="fas fa-triangle-exclamation"></i></div>
            <h1 class="mt-3 text-2xl font-bold">Payment not completed</h1>
            <p class="mt-1 text-slate-500">No money was taken for order <strong class="text-slate-800">{{ $delivery->reference }}</strong>. Your items are held for a short while. Try again or pay the rider on delivery.</p>
            @if(session('pay_error'))<p class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ session('pay_error') }}</p>@endif
            @error('momo_number')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            @if(\App\Services\SpesoClient::configured())
                <form method="POST" action="{{ URL::temporarySignedRoute('shop.order.pay', now()->addHours(2), $ref) }}" class="mt-4 grid gap-3 text-left sm:grid-cols-2">@csrf
                    <label class="block text-sm font-medium">Mobile money number<input name="momo_number" value="{{ old('momo_number', $delivery->payment_mobile) }}" inputmode="tel" class="{{ $field }}"></label>
                    <label class="block text-sm font-medium">Network
                        <select name="network" class="{{ $field }}">@foreach(\App\Support\MomoApproval::NETWORKS as $k => $l)<option value="{{ $k }}" @selected(old('network', $delivery->payment_network) === $k)>{{ $l }}</option>@endforeach</select></label>
                    <button class="h-12 rounded-lg bg-jm-primary text-[15px] font-bold uppercase tracking-wide text-white hover:bg-[#14212d] sm:col-span-2">Pay {{ $m($delivery->balance) }} now</button>
                </form>
            @endif
            <form method="POST" action="{{ URL::temporarySignedRoute('shop.order.cod', now()->addHours(2), $ref) }}" class="mt-3">@csrf
                <button class="text-sm font-bold uppercase text-jm-primary hover:underline">Pay on delivery instead</button>
            </form>
        </div>
    @elseif($pay === 'abandoned' || $delivery->status === 'cancelled')
        <div class="rounded-xl bg-white p-6 text-center">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-slate-100 text-2xl text-slate-500"><i class="fas fa-ban"></i></div>
            <h1 class="mt-3 text-2xl font-bold">This order was cancelled</h1>
            <p class="mt-1 text-slate-500">Order <strong class="text-slate-800">{{ $delivery->reference }}</strong> {{ $pay === 'abandoned' ? 'was not paid in time, so we released the items.' : 'has been cancelled.' }} You are welcome to order again.</p>
            <a href="{{ route('shop.index') }}" class="mt-4 inline-block rounded-xl bg-jm-primary px-5 py-2.5 text-sm font-semibold text-white">Back to the shop</a>
        </div>
    @else
        <div class="rounded-xl bg-white p-6 text-center">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-green-100 text-2xl text-green-600"><i class="fas fa-check"></i></div>
            <h1 class="mt-3 text-2xl font-bold">Thank you, {{ $delivery->customer->name }}!</h1>
            @if($pay === 'paid')
                <p class="mt-1 text-slate-500">Payment received. Your order <strong class="text-slate-800">{{ $delivery->reference }}</strong> is confirmed and we will call you to arrange delivery.</p>
            @else
                <p class="mt-1 text-slate-500">Your order <strong class="text-slate-800">{{ $delivery->reference }}</strong> has been received. We will call you to confirm delivery.</p>
            @endif
            <p class="mt-1 text-sm text-slate-400">A confirmation SMS is on its way to {{ $delivery->customer->phone }}.</p>
        </div>
    @endif

    <div class="mt-4 rounded-xl bg-white p-5">
        <h2 class="font-semibold mb-3">Order summary</h2>
        <div class="divide-y text-sm">
            @foreach($delivery->items as $i)
                <div class="flex justify-between gap-3 py-2"><span>{{ $i->product->name }}@if($i->variant && ! $i->variant->is_plain)<span class="text-slate-500"> ({{ $i->variant->label }})</span>@endif × {{ $i->qty }}</span><span class="font-medium">{{ $m($i->line_total) }}</span></div>
            @endforeach
        </div>
        <div class="mt-2 border-t pt-3 text-sm space-y-1">
            <div class="flex justify-between"><span>Items</span><span>{{ $m($delivery->total_amount) }}</span></div>
            <div class="flex justify-between"><span>Delivery</span><span>{{ $delivery->delivery_fee > 0 ? $m($delivery->delivery_fee) : 'To be confirmed' }}</span></div>
            @if($pay === 'paid')
                <div class="flex justify-between font-semibold text-green-700"><span><i class="fas fa-circle-check"></i> Paid online (Mobile Money)</span><span>{{ $m($delivery->amount_paid) }}</span></div>
                <div class="flex justify-between text-lg font-bold pt-1"><span>Left to pay</span><span>{{ $m($delivery->balance) }}</span></div>
            @else
                <div class="flex justify-between text-lg font-bold pt-1"><span>{{ $pay === 'awaiting' || $pay === 'failed' ? 'Total to pay' : 'Pay on delivery' }}</span><span>{{ $m($delivery->amount_due) }}</span></div>
            @endif
        </div>
        <div class="mt-3 text-sm text-slate-500"><i class="fas fa-location-dot w-5"></i> {{ $delivery->location }}</div>
    </div>

    <div class="mt-4 flex flex-wrap justify-center gap-3 text-sm font-semibold">
        @unless(in_array($pay, ['awaiting', 'failed', 'abandoned'], true))
            <a href="{{ $invoice }}" target="_blank" class="rounded-xl bg-jm-primary text-white px-5 py-2.5"><i class="fas fa-print"></i> Print invoice</a>
            <a href="{{ route('track', ['ref' => $delivery->reference, 'phone' => $delivery->customer->phone]) }}" class="rounded-xl bg-white px-5 py-2.5"><i class="fas fa-truck-fast"></i> Track my order</a>
        @endunless
        <a href="{{ route('shop.index') }}" class="rounded-xl bg-white px-5 py-2.5">Keep shopping</a>
        @if($tel)<a href="tel:{{ $tel }}" class="rounded-xl bg-white px-5 py-2.5"><i class="fas fa-phone"></i> Call us</a>@endif
    </div>
</div>
</div>
@endsection
