@extends('layouts.app')
@section('title', 'Pick Up · Drezwel Go')
@section('content')
<x-dashboard-hero class="mb-4 !p-5" eyebrow="Open orders" title="Pick Up" subtitle="Tap an order for details, or pick it straight away.">
    <x-slot:chips><span><i class="fas fa-box"></i> {{ $deliveries->count() }} waiting</span></x-slot:chips>
</x-dashboard-hero>
<div class="space-y-3">
    @forelse($deliveries as $d)
        <div class="bg-white rounded-xl p-4 border-l-4 border-l-[#1c3041] transition hover:shadow-md">
            <a href="{{ route('rider.show', $d) }}" class="block">
                <div class="flex justify-between gap-2"><span class="font-semibold"><i class="fas fa-location-dot text-[#1c3041]"></i> {{ $d->location }}</span><span class="text-sm font-semibold">GH₵ {{ number_format($d->amount_due, 2) }}</span></div>
                <div class="text-xs text-slate-500 mt-0.5">{{ $d->reference }} · {{ $d->delivery_date->format('D d M') }} {{ $d->time_window }}</div>
                <div class="text-sm mt-1 text-slate-600">{{ $d->package_description }} · {{ $d->items_sent }} item(s)</div>
            </a>
            <form method="POST" action="{{ route('rider.claim', $d) }}" class="mt-3">@csrf<button class="w-full rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white py-2.5 font-semibold transition"><i class="fas fa-hand-pointer"></i> Pick this delivery</button></form>
        </div>
    @empty
        <div class="bg-white rounded-xl p-8 text-center text-slate-500"><i class="fas fa-box-open text-3xl mb-2 block text-slate-300"></i>No pending orders right now.</div>
    @endforelse
</div>
@endsection
