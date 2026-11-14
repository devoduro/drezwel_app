@extends('layouts.app')
@section('title', 'Track your delivery · Drezwel Go')
@section('content')
<div class="text-center mb-5 mt-2">
    <img src="{{ asset('images/logo.png') }}" alt="Drezwel Go" class="mx-auto h-14 w-auto">
    <h1 class="mt-3 text-xl font-bold">Track your delivery</h1>
    <p class="text-sm text-slate-500">Use the reference from your SMS and the phone number you ordered with.</p>
</div>

<form method="GET" action="{{ route('track') }}" class="bg-white rounded-2xl shadow p-5 space-y-3">
    <input name="ref" value="{{ request('ref') }}" placeholder="Reference e.g. DEL-261114-7163" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 uppercase">
    <input name="phone" value="{{ request('phone') }}" placeholder="Phone number e.g. 0241234567" inputmode="tel" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
    <button class="w-full rounded-lg bg-gradient-to-br from-slate-900 to-slate-700 text-white py-2.5 font-semibold shadow">Track</button>
</form>

@if($searched && ! $delivery)
    <div class="mt-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">We could not find a delivery with those details. Check the reference and phone number.</div>
@endif

@if($delivery)
    @php
        $steps = ['pending' => 'Order received', 'assigned' => 'Rider assigned', 'out_for_delivery' => 'Out for delivery', 'delivered' => 'Delivered'];
        $order = array_keys($steps);
        $current = array_search($delivery->status, $order);
    @endphp
    <div class="mt-5 bg-white rounded-2xl shadow p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="text-xs text-slate-500">Reference</div>
                <div class="font-bold">{{ $delivery->reference }}</div>
            </div>
            <x-badge :status="$delivery->status" />
        </div>
        <div class="mt-3 text-sm text-slate-600">{{ $delivery->package_description }} · {{ $delivery->location }}</div>

        @if(in_array($delivery->status, ['undelivered', 'cancelled']))
            <div class="mt-4 rounded-lg bg-slate-100 px-4 py-3 text-sm">
                {{ $delivery->status === 'cancelled' ? 'This order was cancelled.' : 'We could not complete this delivery. We will contact you to reschedule.' }}
            </div>
        @else
            <ol class="mt-5 space-y-4">
                @foreach($steps as $key => $label)
                    @php $i = array_search($key, $order); $done = $current !== false && $i <= $current; @endphp
                    <li class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full grid place-items-center text-xs {{ $done ? 'bg-green-500 text-white' : 'bg-slate-200 text-slate-500' }}">{{ $done ? '✓' : $i + 1 }}</span>
                        <span class="{{ $done ? 'font-medium' : 'text-slate-400' }}">{{ $label }}</span>
                    </li>
                @endforeach
            </ol>
        @endif

        @if($delivery->rider && in_array($delivery->status, ['assigned', 'out_for_delivery']))
            <div class="mt-5 rounded-lg bg-blue-50 border border-blue-100 px-4 py-3 text-sm">
                Your rider: <strong>{{ $delivery->rider->name }}</strong>
                @if($delivery->rider->phone) · <a class="text-brand-accent" href="tel:{{ $delivery->rider->phone }}">{{ $delivery->rider->phone }}</a>@endif
            </div>
        @endif
        @if($delivery->status === 'out_for_delivery')
            <div class="mt-3 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm">
                We texted you a <strong>4-digit code</strong>. Give it to the rider <strong>only when you have received your order</strong>.
            </div>
        @endif

        @if($delivery->balance > 0 && $delivery->status !== 'cancelled')
            <div class="mt-3 text-sm">Balance to pay: <strong>GH₵ {{ number_format($delivery->balance, 2) }}</strong></div>
        @endif

        <h2 class="mt-6 text-sm font-semibold text-slate-500 uppercase tracking-wide">Updates</h2>
        <ul class="mt-2 space-y-2 text-sm">
            @foreach($delivery->events->whereIn('type', ['created', 'assigned', 'started', 'delivered', 'undelivered', 'reopened', 'cancelled'])->reverse() as $e)
                <li class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                    <span>{{ match($e->type) {
                        'created' => 'Order received', 'assigned' => 'Rider assigned', 'started' => 'Rider is on the way',
                        'delivered' => 'Delivered', 'undelivered' => 'Delivery attempt failed', 'reopened' => 'Rescheduled', 'cancelled' => 'Cancelled', default => $e->type } }}</span>
                    <span class="text-slate-400 whitespace-nowrap">{{ $e->created_at->format('d M, g:ia') }}</span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
@endsection
