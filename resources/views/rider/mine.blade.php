@extends('layouts.app')
@section('title', 'My Deliveries · Drezwel Go')
@section('content')
<x-dashboard-hero class="mb-4 !p-5" eyebrow="{{ now()->format('l, d M') }}" title="My Deliveries" subtitle="Start a delivery when you set off and ask the customer for their code.">
    <x-slot:chips>
        <span><i class="fas fa-route"></i> {{ $deliveries->count() }} to do</span>
        <span><i class="fas fa-motorcycle"></i> {{ $deliveries->where('status', 'out_for_delivery')->count() }} on the road</span>
    </x-slot:chips>
</x-dashboard-hero>
@if($custody['cash_held'] > 0 || $custody['items_pending'] > 0)
    <x-stat-tile class="mb-4" color="orange" icon="fa-wallet" title="Hand in to manager" :value="'GH₵ ' . number_format($custody['cash_held'], 2)" :sub="$custody['items_pending'] . ' item(s) to return'" />
@endif
<div class="space-y-3">
    @forelse($deliveries as $d) @include('rider._card', ['d' => $d]) @empty
        <div class="bg-white rounded-xl p-8 text-center text-slate-500"><i class="fas fa-mug-hot text-3xl mb-2 block text-slate-300"></i>Nothing assigned yet.<br><a class="text-[#2c8fc6] font-semibold" href="{{ route('rider.available') }}">Pick an order →</a></div>
    @endforelse
</div>
@endsection
