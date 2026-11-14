@extends('layouts.app')
@section('title', $customer->name . ' · Delivery history · Drezwel Go')
@php
    $input = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm w-full focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#42a5dc]';
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $owed = max($stats['spend'] - $stats['paid'], 0);
@endphp
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Customer history" :title="$customer->name" :subtitle="$customer->phone . ($customer->location ? ' · ' . $customer->location : '')">
    <x-slot:chips>
        <span><i class="fas fa-box"></i> {{ $stats['orders'] }} orders</span>
        <span><i class="fas fa-calendar"></i> {{ $stats['last'] ? 'Last order ' . \Carbon\Carbon::parse($stats['last'])->format('d M Y') : 'No orders yet' }}</span>
    </x-slot:chips>
    <a href="{{ route('manager.customers.index') }}"><i class="fas fa-arrow-left"></i> Customers</a>
    <a href="tel:{{ $customer->phone }}"><i class="fas fa-phone"></i> Call</a>
    <a href="{{ route('manager.deliveries.create', ['name' => $customer->name, 'phone' => $customer->phone, 'location' => $customer->location]) }}" class="primary"><i class="fas fa-plus"></i> New order</a>
</x-dashboard-hero>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <x-stat-tile color="blue" icon="fa-box" title="Orders" :value="$stats['orders']" :sub="$stats['delivered'] . ' delivered'" />
    <x-stat-tile color="orange" icon="fa-hourglass-half" title="In progress" :value="$stats['pending']" sub="Not delivered yet" />
    <x-stat-tile color="green" icon="fa-sack-dollar" title="Lifetime spend" :value="$m($stats['spend'])" :sub="'Paid ' . $m($stats['paid'])" />
    <x-stat-tile :color="$owed > 0 ? 'red' : 'green'" icon="fa-hand-holding-dollar" title="Balance due" :value="$m($owed)" :sub="$owed > 0 ? 'Still owed' : 'All settled'" :href="$owed > 0 ? route('manager.debtors') : null" />
</div>

<form method="POST" action="{{ route('manager.customers.update', $customer) }}" class="bg-white rounded-xl p-4 grid md:grid-cols-4 gap-2 mb-5 border-l-4 border-l-[#8cc152]">@csrf @method('PUT')
    <input name="name" value="{{ $customer->name }}" required class="{{ $input }}">
    <input name="location" value="{{ $customer->location }}" placeholder="Default location" class="{{ $input }}">
    <input name="notes" value="{{ $customer->notes }}" placeholder="Notes" class="{{ $input }}">
    <button class="rounded-lg bg-[#8cc152] hover:bg-[#74a83b] text-white px-4 py-2 text-sm font-semibold transition">Save details</button>
</form>

<form method="GET" class="bg-white rounded-xl p-3 grid grid-cols-2 md:grid-cols-4 gap-2 mb-4 text-sm">
    <select name="status" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><option value="">All statuses</option>
        @foreach(\App\Models\Delivery::STATUSES as $k => $l)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach</select>
    <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-2" title="From">
    <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-2" title="To">
    <button class="rounded-lg bg-[#42a5dc] hover:bg-[#2c8fc6] text-white px-4 py-2 font-semibold transition"><i class="fas fa-filter"></i> Filter</button>
</form>

<div class="bg-white rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#42a5dc] font-semibold text-sm">Delivery history</div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Reference</th><th>Date</th><th>Package</th><th>Location</th><th>Rider</th><th class="text-right">Total</th><th class="text-right">Paid</th><th class="text-right">Balance</th><th>Delivered at</th><th class="p-3">Status</th></tr></thead>
        @forelse($deliveries as $d)
            <tr class="border-t border-slate-100 hover:bg-sky-50/50">
                <td class="p-3"><a class="text-[#2c8fc6] font-semibold hover:underline" href="{{ route('manager.deliveries.show', $d) }}">{{ $d->reference }}</a></td>
                <td class="whitespace-nowrap">{{ $d->delivery_date->format('d M Y') }}</td>
                <td>{{ $d->package_description }}<div class="text-xs text-slate-400">{{ \App\Models\Delivery::TYPES[$d->order_type] ?? '' }}</div></td>
                <td class="text-slate-600">{{ $d->location }}</td>
                <td>{{ $d->rider?->name ?? '—' }}</td>
                <td class="text-right">{{ number_format($d->amount_due, 2) }}</td>
                <td class="text-right text-slate-500">{{ number_format($d->amount_paid, 2) }}</td>
                <td class="text-right {{ $d->balance > 0 ? 'text-red-600 font-medium' : 'text-green-600' }}">{{ number_format($d->balance, 2) }}</td>
                <td class="whitespace-nowrap text-slate-500">{{ $d->delivered_at?->format('d M, g:ia') ?? '—' }}</td>
                <td class="p-3"><x-badge :status="$d->status"/>@if($d->undelivered_reason)<div class="text-xs text-red-600 mt-1">{{ $d->undelivered_reason }}</div>@endif</td>
            </tr>
        @empty <tr><td colspan="10" class="p-10 text-center text-slate-400"><i class="fas fa-clock-rotate-left text-3xl mb-2 block"></i>No deliveries match.</td></tr> @endforelse
    </table></div>
</div>
<div class="mt-4">{{ $deliveries->links() }}</div>
@endsection
