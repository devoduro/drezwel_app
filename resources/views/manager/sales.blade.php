@extends('layouts.app')
@section('title', 'Sales · Drezwel Go')
@php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $field = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]';
    $range = ($from || $to) ? ($from?->format('d M Y') ?? '…') . ' – ' . ($to?->format('d M Y') ?? '…') : 'All records';
    $qs = request()->only(['period', 'from', 'to']);
    $head = 'px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm text-slate-800';
    $badge = ['online' => 'bg-purple-100 text-purple-700', 'phone' => 'bg-sky-100 text-sky-700', 'pos' => 'bg-emerald-100 text-emerald-700', 'manager' => 'bg-slate-100 text-slate-600'];
@endphp
@section('content')
<x-dashboard-hero class="mb-6" :eyebrow="$range" title="Sales" subtitle="What you sold, through which channel, and how you were paid.">
    <x-slot:chips>
        <span><i class="fas fa-calendar-days"></i> {{ $periods[$period] ?? 'Last 30 days' }}</span>
        <span><i class="fas fa-receipt"></i> {{ $kpi['orders'] }} sales</span>
        <span><i class="fas fa-coins"></i> {{ $m($kpi['billed']) }}</span>
    </x-slot:chips>
    <form method="GET" class="flex flex-wrap items-center gap-2 text-sm">
        @foreach(request()->only('source', 'method', 'q') as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
        <select name="period" onchange="this.form.submit()" class="rounded-xl border-0 bg-white/10 text-white ring-1 ring-white/20 px-3 py-2.5 focus:outline-none">
            @foreach($periods as $k => $l)<option class="text-slate-800" value="{{ $k }}" @selected($period === $k)>{{ $l }}</option>@endforeach
        </select>
        @if($period === 'custom')
            <input type="date" name="from" value="{{ request('from') }}" class="rounded-xl bg-white text-slate-800 px-2 py-2">
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-xl bg-white text-slate-800 px-2 py-2">
            <button class="rounded-xl bg-white text-slate-800 px-3 py-2 font-semibold">Apply</button>
        @endif
        <a href="{{ route('manager.reports.export', ['type' => 'orders'] + $qs) }}" class="rounded-xl bg-white text-slate-800 px-4 py-2.5 font-semibold hover:bg-gray-100 transition"><i class="fas fa-file-csv"></i> Download CSV</a>
    </form>
</x-dashboard-hero>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <x-stat-tile color="blue" icon="fa-receipt" title="Total sales" :value="$m($kpi['billed'])" :sub="$kpi['orders'] . ' orders · avg ' . $m($kpi['average'])" />
    <x-stat-tile color="green" icon="fa-money-bill-wave" title="Money received" :value="$m($kpi['collected'])" sub="Paid so far" />
    <x-stat-tile color="red" icon="fa-hand-holding-dollar" title="Still owed" :value="$m($kpi['outstanding'])" sub="Unpaid balances" :href="route('manager.debtors')" />
    <x-stat-tile :color="$kpi['profit'] >= 0 ? 'purple' : 'red'" icon="fa-chart-line" title="Product profit" :value="$m($kpi['profit'])" sub="Sales of products minus their cost" />
</div>

<div class="grid gap-5 lg:grid-cols-2 mb-6">
    <section class="bg-white rounded-xl overflow-hidden">
        <div class="{{ $head }}">Sales by channel</div>
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="px-4 py-2">Channel</th><th class="text-right">Orders</th><th class="text-right">Sales</th><th class="text-right px-4">Received</th></tr></thead>
            @foreach($sources as $k => $l)
                @php $r = $bySource[$k] ?? null; @endphp
                <tr class="border-t border-slate-100"><td class="px-4 py-2"><a class="rounded-full px-2 py-0.5 text-xs font-medium {{ $badge[$k] }}" href="{{ route('manager.sales', array_merge(request()->except('page', 'source'), ['source' => $k])) }}">{{ $l }}</a></td>
                    <td class="text-right">{{ $r->n ?? 0 }}</td><td class="text-right">{{ $m($r->value ?? 0) }}</td><td class="text-right px-4">{{ $m($r->paid ?? 0) }}</td></tr>
            @endforeach
        </table>
    </section>
    <section class="bg-white rounded-xl overflow-hidden">
        <div class="{{ $head }}">How customers paid</div>
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="px-4 py-2">Method</th><th class="text-right">Payments</th><th class="text-right px-4">Amount</th></tr></thead>
            @foreach(\App\Models\Payment::METHODS as $k => $l)
                <tr class="border-t border-slate-100"><td class="px-4 py-2"><a class="hover:underline" href="{{ route('manager.sales', array_merge(request()->except('page', 'method'), ['method' => $k])) }}">{{ $l }}</a></td><td class="text-right">{{ $byMethod[$k]->n ?? 0 }}</td><td class="text-right px-4">{{ $m($byMethod[$k]->total ?? 0) }}</td></tr>
            @endforeach
            <tr class="border-t bg-slate-50 font-semibold"><td class="px-4 py-2">Total</td><td class="text-right">{{ $byMethod->sum('n') }}</td><td class="text-right px-4">{{ $m($byMethod->sum('total')) }}</td></tr>
        </table>
    </section>
</div>

<form method="GET" class="mb-4 grid grid-cols-2 gap-2 rounded-xl bg-white p-3 text-sm md:grid-cols-5">
    @foreach($qs as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
    <div class="relative col-span-2"><i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input name="q" value="{{ request('q') }}" placeholder="Search name, phone, reference…" class="{{ $field }} w-full pl-9"></div>
    <select name="source" class="{{ $field }}"><option value="">All channels</option>@foreach($sources as $k => $l)<option value="{{ $k }}" @selected(request('source') === $k)>{{ $l }}</option>@endforeach</select>
    <select name="method" class="{{ $field }}"><option value="">Any payment method</option>@foreach(\App\Models\Payment::METHODS as $k => $l)<option value="{{ $k }}" @selected(request('method') === $k)>{{ $l }}</option>@endforeach</select>
    <button class="rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-4 py-2 font-semibold transition"><i class="fas fa-filter"></i> Filter</button>
</form>

<div class="bg-white rounded-xl overflow-hidden">
    <div class="{{ $head }}">Sales ({{ $sales->total() }})</div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Date</th><th>Invoice</th><th>Customer</th><th>Channel</th><th>What was sold</th><th class="text-right">Total</th><th class="text-right">Paid</th><th>Paid by</th><th class="text-right p-3">Balance</th></tr></thead>
        @forelse($sales as $s)
            <tr class="border-t border-slate-100 hover:bg-sky-50/50 align-top">
                <td class="p-3 whitespace-nowrap text-slate-600">{{ $s->delivery_date->format('d M Y') }}</td>
                <td><a class="font-semibold text-[#1c3041] hover:underline" target="_blank" href="{{ route('manager.deliveries.invoice', $s) }}">{{ $s->reference }}</a></td>
                <td>{{ $s->customer->phone === \App\Services\SalesService::WALK_IN_PHONE ? 'Walk-in customer' : $s->customer->name }}</td>
                <td><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $badge[$s->source] ?? $badge['manager'] }}">{{ $sources[$s->source] ?? $s->source }}</span></td>
                <td class="max-w-[240px] truncate text-slate-600" title="{{ $s->package_description }}">{{ $s->package_description }}</td>
                <td class="text-right">{{ number_format($s->amount_due, 2) }}</td>
                <td class="text-right text-slate-600">{{ number_format($s->amount_paid, 2) }}</td>
                <td class="text-xs text-slate-500">{{ $s->payments->map(fn ($p) => \App\Models\Payment::METHODS[$p->method] ?? $p->method)->unique()->implode(', ') ?: '—' }}</td>
                <td class="text-right p-3 {{ $s->balance > 0 ? 'text-red-600 font-medium' : 'text-green-600' }}">{{ number_format($s->balance, 2) }}</td>
            </tr>
        @empty <tr><td colspan="9" class="p-10 text-center text-slate-400"><i class="fas fa-receipt text-3xl mb-2 block"></i>No sales in this period.</td></tr> @endforelse
    </table></div>
</div>
<div class="mt-4">{{ $sales->links() }}</div>
@endsection
