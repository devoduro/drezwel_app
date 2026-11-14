@extends('layouts.shop')
@section('title', 'Order received · ' . \App\Models\Setting::get('business_name'))
@php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $tel = \App\Models\Setting::phoneLink();
    $invoice = \Illuminate\Support\Facades\URL::temporarySignedRoute('invoice.public', now()->addDays(30), ['reference' => $delivery->reference]);
@endphp
@section('content')
<div class="max-w-[1180px] mx-auto px-4 py-6">
<div class="mx-auto max-w-2xl">
    <div class="rounded-xl bg-white p-6 text-center">
        <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-green-100 text-2xl text-green-600"><i class="fas fa-check"></i></div>
        <h1 class="mt-3 text-2xl font-bold">Thank you, {{ $delivery->customer->name }}!</h1>
        <p class="mt-1 text-slate-500">Your order <strong class="text-slate-800">{{ $delivery->reference }}</strong> has been received. We will call you to confirm delivery.</p>
        <p class="mt-1 text-sm text-slate-400">A confirmation SMS is on its way to {{ $delivery->customer->phone }}.</p>
    </div>

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
            <div class="flex justify-between text-lg font-bold pt-1"><span>Pay on delivery</span><span>{{ $m($delivery->amount_due) }}</span></div>
        </div>
        <div class="mt-3 text-sm text-slate-500"><i class="fas fa-location-dot w-5"></i> {{ $delivery->location }}</div>
    </div>

    <div class="mt-4 flex flex-wrap justify-center gap-3 text-sm font-semibold">
        <a href="{{ $invoice }}" target="_blank" class="rounded-xl bg-jm-orange text-white px-5 py-2.5"><i class="fas fa-print"></i> Print invoice</a>
        <a href="{{ route('track', ['ref' => $delivery->reference, 'phone' => $delivery->customer->phone]) }}" class="rounded-xl bg-white px-5 py-2.5"><i class="fas fa-truck-fast"></i> Track my order</a>
        <a href="{{ route('shop.index') }}" class="rounded-xl bg-white px-5 py-2.5">Keep shopping</a>
        @if($tel)<a href="tel:{{ $tel }}" class="rounded-xl bg-white px-5 py-2.5"><i class="fas fa-phone"></i> Call us</a>@endif
    </div>
</div>
</div>
@endsection
