@extends('layouts.app')
@section('title', $rider->name . ' · Delivery history · Drezwel Go')
@php $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2); @endphp
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Rider history" :title="$rider->name" :subtitle="($rider->phone ?: 'No phone') . ($rider->email ? ' · ' . $rider->email : '')">
    <x-slot:chips>
        <span><i class="fas fa-box"></i> {{ $stats['total'] }} deliveries</span>
        <span><i class="fas fa-bullseye"></i> {{ $stats['rate'] }}% delivered</span>
        <span class="{{ $custody['settled'] ? '' : '!bg-amber-400/30' }}"><i class="fas fa-wallet"></i> {{ $custody['settled'] ? 'Cleared' : ($custody['cleared'] ? $custody['open_runs'] . ' open ' . ($custody['open_runs'] === 1 ? 'order' : 'orders') . ' to finish' : 'Holding ' . $m($custody['cash_held'])) }}</span>
    </x-slot:chips>
    <a href="{{ route('manager.riders.index') }}"><i class="fas fa-arrow-left"></i> Riders</a>
</x-dashboard-hero>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <x-stat-tile color="green" icon="fa-circle-check" title="Delivered" :value="$stats['delivered']" :sub="$m($stats['value']) . ' value'" />
    <x-stat-tile color="blue" icon="fa-route" title="Open runs" :value="$stats['open']" sub="Assigned or on the road" />
    <x-stat-tile color="red" icon="fa-triangle-exclamation" title="Undelivered" :value="$stats['undelivered']" sub="Failed attempts" />
    <x-stat-tile color="orange" icon="fa-motorcycle" title="Delivery fees received" :value="$m($stats['fees'])" :sub="'Paid back ' . $m($custody['fees_paid']) . ' · still owed ' . $m($custody['fees_owed'])" />
</div>

<form method="GET" class="bg-white rounded-xl p-3 grid grid-cols-2 md:grid-cols-4 gap-2 mb-4 text-sm">
    <select name="status" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><option value="">All statuses</option>
        @foreach(\App\Models\Delivery::STATUSES as $k => $l)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach</select>
    <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-2" title="From">
    <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-2" title="To">
    <button class="rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-4 py-2 font-semibold transition"><i class="fas fa-filter"></i> Filter</button>
</form>

<div class="bg-white rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm">Delivery history</div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Reference</th><th>Date</th><th>Customer</th><th>Location</th><th class="text-right">Customer pays</th><th class="text-right">Delivery fee</th><th class="text-right">Fee received</th><th>Delivered at</th><th class="p-3">Status</th></tr></thead>
        @forelse($deliveries as $d)
            <tr class="border-t border-slate-100 hover:bg-sky-50/50">
                <td class="p-3"><a class="text-[#1c3041] font-semibold hover:underline" href="{{ route('manager.deliveries.show', $d) }}">{{ $d->reference }}</a></td>
                <td class="whitespace-nowrap">{{ $d->delivery_date->format('d M Y') }}</td>
                <td><div class="font-medium">{{ $d->customer->name }}</div><div class="text-xs text-slate-400">{{ $d->customer->phone }}</div></td>
                <td class="text-slate-600">{{ $d->location }}</td>
                <td class="text-right">{{ number_format($d->amount_due, 2) }}</td>
                <td class="text-right {{ $d->status === 'cancelled' ? 'text-slate-300 line-through' : '' }}">{{ number_format($d->delivery_fee, 2) }}</td>
                <td class="text-right">
                    @if($d->status === 'delivered' && $d->delivery_fee > 0)
                        <span class="font-semibold text-[#1c3041]">{{ number_format($d->delivery_fee, 2) }}</span>
                        <div class="text-[11px] {{ $d->rider_fee_status === 'paid' ? 'text-green-600' : 'text-amber-600' }}">{{ $d->rider_fee_status === 'paid' ? 'paid back to rider' : 'not yet paid back' }}</div>
                    @elseif($d->delivery_fee > 0)
                        <span class="text-slate-300" title="Fee of {{ number_format($d->delivery_fee, 2) }} is received once the order is delivered">—</span>
                    @else
                        <span class="text-slate-300">—</span>
                    @endif
                </td>
                <td class="whitespace-nowrap text-slate-500">{{ $d->delivered_at?->format('d M, g:ia') ?? '—' }}</td>
                <td class="p-3"><x-badge :status="$d->status"/>@if($d->undelivered_reason)<div class="text-xs text-red-600 mt-1">{{ $d->undelivered_reason }}</div>@endif</td>
            </tr>
        @empty <tr><td colspan="9" class="p-10 text-center text-slate-400"><i class="fas fa-clock-rotate-left text-3xl mb-2 block"></i>No deliveries for this rider yet.</td></tr> @endforelse
        @if($deliveries->count())
            <tr class="border-t bg-slate-50 font-semibold"><td colspan="5" class="p-3 text-right">Total delivery fees{{ request()->hasAny(['status', 'from', 'to']) ? ' (this filter)' : '' }}</td><td class="text-right">{{ number_format($listCharged, 2) }}</td><td class="text-right text-[#1c3041]" title="Fees on delivered orders: the ones the rider has received">{{ number_format($listFees, 2) }}</td><td colspan="2"></td></tr>
            <tr class="bg-slate-50 text-xs text-slate-500"><td colspan="5" class="px-3 pb-3 text-right">Delivery fee = charged on each order. Fee received = on delivered orders only.</td><td></td><td></td><td colspan="2"></td></tr>
        @endif
    </table></div>
</div>
<div class="mt-4">{{ $deliveries->links() }}</div>
@endsection
