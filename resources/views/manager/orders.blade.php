@extends('layouts.app')
@section('title', 'Orders · Drezwel Go')
@php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $field = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]';
    $keep = request()->except('status', 'page');
    $tabs = ['all' => 'All'] + \App\Models\Delivery::STATUSES;
    $allCount = $counts->sum();
    $badge = ['online' => 'bg-purple-100 text-purple-700', 'phone' => 'bg-sky-100 text-sky-700', 'pos' => 'bg-emerald-100 text-emerald-700', 'manager' => 'bg-slate-100 text-slate-600'];
@endphp
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Customer orders" title="Orders" subtitle="Everything customers have ordered: from the website, by phone, at the shop counter or entered by your team.">
    <x-slot:chips>
        <span><i class="fas fa-box"></i> {{ $today['orders'] }} today</span>
        <span><i class="fas fa-globe"></i> {{ $today['web'] }} from the website today</span>
    </x-slot:chips>
    <a href="{{ route('manager.pos') }}"><i class="fas fa-cash-register"></i> Point of sale</a>
    <a href="{{ route('manager.deliveries.create') }}" class="primary"><i class="fas fa-plus"></i> New order</a>
</x-dashboard-hero>

@if($waiting > 0)
    <a href="{{ route('manager.orders', ['status' => 'pending']) }}" class="mb-5 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-purple-200 bg-purple-50 px-4 py-3 text-sm text-purple-900 hover:bg-purple-100">
        <span><i class="fas fa-bell"></i> <strong>{{ $waiting }}</strong> new website / phone {{ $waiting === 1 ? 'order is' : 'orders are' }} waiting for a rider.</span>
        <span class="font-semibold">Review them <i class="fas fa-arrow-right"></i></span>
    </a>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <x-stat-tile color="blue" icon="fa-box" title="Orders today" :value="$today['orders']" :sub="$m($today['value']) . ' billed'" />
    <x-stat-tile color="purple" icon="fa-globe" title="Website orders today" :value="$today['web']" sub="Placed online" />
    <x-stat-tile color="orange" icon="fa-hourglass-half" title="In progress" :value="$today['in_progress']" sub="Not delivered yet" :href="route('manager.orders', ['status' => 'pending'])" />
    <x-stat-tile :color="$waiting ? 'red' : 'green'" icon="fa-bell" title="Waiting for a rider" :value="$waiting" sub="New website & phone orders" />
</div>

{{-- status tabs --}}
<div class="no-print mb-3 flex flex-wrap gap-2 text-sm">
    @foreach($tabs as $k => $label)
        @php $n = $k === 'all' ? $allCount : ($counts[$k] ?? 0); @endphp
        <a href="{{ route('manager.orders', $keep + ['status' => $k]) }}" class="rounded-full border px-3.5 py-1.5 font-medium {{ $status === $k ? 'border-[#1c3041] bg-[#1c3041] text-white' : 'bg-white text-slate-600 hover:border-[#1c3041]' }}">{{ $label }} <span class="{{ $status === $k ? 'text-white/70' : 'text-slate-400' }}">{{ $n }}</span></a>
    @endforeach
</div>

<form method="GET" class="mb-4 grid grid-cols-2 gap-2 rounded-xl bg-white p-3 text-sm md:grid-cols-6">
    <input type="hidden" name="status" value="{{ $status }}">
    <div class="relative col-span-2"><i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input name="q" value="{{ request('q') }}" placeholder="Search name, phone, reference…" class="{{ $field }} w-full pl-9"></div>
    <select name="source" class="{{ $field }}"><option value="">All channels</option>@foreach($sources as $k => $l)<option value="{{ $k }}" @selected(request('source') === $k)>{{ $l }}</option>@endforeach</select>
    <input type="date" name="from" value="{{ request('from') }}" class="{{ $field }}" title="From">
    <input type="date" name="to" value="{{ request('to') }}" class="{{ $field }}" title="To">
    <button class="rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-4 py-2 font-semibold transition"><i class="fas fa-filter"></i> Filter</button>
</form>

<div class="bg-white rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm">{{ $tabs[$status] ?? 'Orders' }} · {{ $orders->total() }}</div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Order</th><th>Placed</th><th>Customer</th><th>Items</th><th>Channel</th><th>Rider</th><th class="text-right">Total</th><th class="text-right">Balance</th><th class="p-3">Status</th><th></th></tr></thead>
        @forelse($orders as $o)
            <tr class="border-t border-slate-100 hover:bg-sky-50/50 align-top">
                <td class="p-3"><a class="font-semibold text-[#1c3041] hover:underline" href="{{ route('manager.deliveries.show', $o) }}">{{ $o->reference }}</a></td>
                <td class="whitespace-nowrap text-slate-600">{{ $o->created_at->format('d M, g:ia') }}</td>
                <td><div class="font-medium">{{ $o->customer->phone === \App\Services\SalesService::WALK_IN_PHONE ? 'Walk-in customer' : $o->customer->name }}</div><div class="text-xs text-slate-400">{{ $o->location }}</div></td>
                <td class="max-w-[220px] text-slate-600"><div class="truncate" title="{{ $o->package_description }}">{{ $o->package_description }}</div></td>
                <td><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $badge[$o->source] ?? $badge['manager'] }}">{{ $sources[$o->source] ?? $o->source }}</span></td>
                <td>{{ $o->rider?->name ?? '—' }}</td>
                <td class="text-right">{{ number_format($o->amount_due, 2) }}</td>
                <td class="text-right {{ $o->balance > 0 && $o->status !== 'cancelled' ? 'text-red-600 font-medium' : 'text-green-600' }}">{{ number_format($o->status === 'cancelled' ? 0 : $o->balance, 2) }}</td>
                <td class="p-3"><x-badge :status="$o->status"/></td>
                <td class="p-3 whitespace-nowrap text-right text-xs">
                    <a class="text-[#1c3041] hover:underline" href="{{ route('manager.deliveries.show', $o) }}">Open</a> ·
                    <a class="text-[#1c3041] hover:underline" target="_blank" href="{{ route('manager.deliveries.invoice', $o) }}">Invoice</a>
                </td>
            </tr>
        @empty <tr><td colspan="10" class="p-10 text-center text-slate-400"><i class="fas fa-box-open text-3xl mb-2 block"></i>No orders match.</td></tr> @endforelse
    </table></div>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
