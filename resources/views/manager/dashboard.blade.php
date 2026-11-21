@extends('layouts.app')
@section('title', 'Dashboard · Drezwel Go')
@php $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2); @endphp
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="{{ now()->format('l, d F Y') }}" title="Operations & Delivery" subtitle="Real-time delivery velocity, cash reconciliation and fleet performance.">
    <x-slot:chips>
        <span><i class="fas fa-calendar-days"></i> {{ $periods[$period] ?? 'All time' }}</span>
        <span><i class="fas fa-box"></i> {{ $kpi['orders'] }} orders</span>
        <span><i class="fas fa-coins"></i> {{ $m($kpi['revenue']) }}</span>
    </x-slot:chips>
    <form method="GET" class="flex flex-wrap gap-2 text-sm items-center">
        <select name="period" onchange="this.form.submit()" class="rounded-xl border-0 bg-white/10 text-white ring-1 ring-white/20 px-3 py-2.5 focus:outline-none">
            @foreach($periods as $k => $label)<option class="text-slate-800" value="{{ $k }}" @selected($period === $k)>{{ $label }}</option>@endforeach
        </select>
        @if($period === 'custom')
            <input type="date" name="from" value="{{ request('from') }}" class="rounded-xl bg-white text-slate-800 px-2 py-2">
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-xl bg-white text-slate-800 px-2 py-2">
            <button class="rounded-xl bg-white text-slate-800 px-3 py-2 font-semibold">Apply</button>
        @endif
        <a href="{{ route('manager.deliveries.create') }}" class="rounded-xl bg-white text-slate-800 px-4 py-2.5 font-semibold hover:bg-gray-100 transition"><i class="fas fa-plus"></i> New delivery</a>
    </form>
</x-dashboard-hero>

@if($lowProducts->count())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
        <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-red-800">
            <span class="font-semibold"><i class="fas fa-triangle-exclamation"></i> Stock alert: {{ $lowProducts->count() }} product(s) need restocking</span>
            <a class="font-semibold underline" href="{{ route('manager.products.index', ['level' => 'low']) }}">Manage stock</a>
        </div>
        <div class="mt-2 flex flex-wrap gap-2 text-sm">
            @foreach($lowProducts as $p)
                <a href="{{ route('manager.products.show', $p) }}" class="rounded-full bg-white px-3 py-1 ring-1 ring-red-200 hover:ring-red-400">
                    {{ $p->name }} · <strong class="{{ $p->stock_qty <= 0 ? 'text-red-600' : 'text-amber-600' }}">{{ $p->stock_qty <= 0 ? 'out' : $p->stock_qty . ' left' }}</strong></a>
            @endforeach
        </div>
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 mb-6">
    @php $salesLink = route('manager.sales', ['period' => $period] + request()->only('from', 'to')); @endphp
    <x-stat-tile color="blue" icon="fa-sack-dollar" title="Total sales (goods)" :value="$m($kpi['revenue'])" :sub="$kpi['orders'].' orders · delivery fees ' . $m($kpi['fees']) . ' go to riders'" :href="$salesLink" />
    <x-stat-tile color="green" icon="fa-money-bill-wave" title="Money collected" :value="$m($kpi['collected'])" sub="For the goods, fees excluded" :href="$salesLink" />
    <x-stat-tile color="red" icon="fa-hand-holding-dollar" title="Outstanding" :value="$m($kpi['outstanding'])" sub="Still owed for the goods" :href="route('manager.debtors')" />
    <x-stat-tile color="purple" icon="fa-circle-check" title="Delivered" :value="$kpi['delivered']" sub="Completed drop-offs" :href="route('manager.deliveries.index', ['status' => 'delivered'])" />
    <x-stat-tile color="orange" icon="fa-hourglass-half" title="Pending" :value="$kpi['pending']" :sub="$kpi['unassigned'].' unassigned · '.$kpi['on_the_road'].' on the road'" :href="route('manager.deliveries.index', ['status' => 'pending'])" />
    <x-stat-tile color="slate" icon="fa-bullseye" title="Fulfilment rate" :value="$kpi['rate'].'%'" sub="Target 85%" :badge="$kpi['rate'] >= 85 ? 'On target' : 'Below target'" :href="route('manager.deliveries.index')" />
</div>

<div class="grid xl:grid-cols-2 gap-5">
    <section class="bg-white rounded-xl shadow-sm overflow-hidden xl:col-span-2">
        <div class="px-4 py-3 bg-white text-slate-800 font-semibold text-sm border-b border-gray-200 border-l-4 border-l-[#1c3041]">Today's queue ({{ $todayQueue->count() }})</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Reference</th><th>Customer</th><th>Location</th><th>Rider</th><th class="text-right">Amount</th><th class="p-3">Status</th></tr></thead>
            @forelse($todayQueue as $d)
                <tr class="border-t"><td class="p-3"><a class="text-brand-accent" href="{{ route('manager.deliveries.show', $d) }}">{{ $d->reference }}</a></td>
                    <td>{{ $d->customer->name }}</td><td>{{ $d->location }}</td><td>{{ $d->rider?->name ?? '—' }}</td>
                    <td class="text-right">{{ $m($d->amount_due) }}</td><td class="p-3"><x-badge :status="$d->status"/></td></tr>
            @empty <tr><td colspan="6" class="p-4 text-slate-400">Nothing waiting for today.</td></tr> @endforelse
        </table></div>
        @if($upcoming->count())
            <div class="px-4 py-2 text-xs text-slate-500 bg-slate-50 border-t">Upcoming: {{ $upcoming->count() }} advance booking(s), next on {{ $upcoming->first()->delivery_date->format('d M') }}.</div>
        @endif
    </section>

    <section class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-white text-slate-800 font-semibold text-sm border-b border-gray-200 border-l-4 border-l-[#1c3041]">Daily delivery &amp; revenue velocity</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Date</th><th class="text-right">Orders</th><th class="text-right">Delivered</th><th class="text-right">Pending</th><th class="text-right">Revenue</th><th class="text-right p-3">Cash</th></tr></thead>
            @forelse($daily as $r)
                <tr class="border-t"><td class="p-3">{{ \Carbon\Carbon::parse($r->day)->format('D d M') }}</td><td class="text-right">{{ $r->orders }}</td><td class="text-right">{{ $r->delivered }}</td><td class="text-right">{{ $r->pending }}</td><td class="text-right">{{ $m($r->revenue) }}</td><td class="text-right p-3">{{ $m($r->cash) }}</td></tr>
            @empty <tr><td colspan="6" class="p-4 text-slate-400">No deliveries in this period.</td></tr> @endforelse
        </table></div>
    </section>

    <section class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-white text-slate-800 font-semibold text-sm border-b border-gray-200 border-l-4 border-l-[#1c3041]">Rider fulfilment performance</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Rider</th><th class="text-right">Assigned</th><th class="text-right">Delivered</th><th class="text-right">Pending</th><th class="text-right">Rate</th><th class="text-right p-3">Revenue</th></tr></thead>
            @foreach($riders as $r)
                <tr class="border-t"><td class="p-3 font-medium">{{ $r->rider->name }}</td><td class="text-right">{{ $r->assigned }}</td><td class="text-right">{{ $r->delivered }}</td><td class="text-right">{{ $r->pending }}</td><td class="text-right">{{ $r->rate }}%</td><td class="text-right p-3">{{ $m($r->revenue) }}</td></tr>
            @endforeach
        </table></div>
    </section>

    <section class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-white text-slate-800 font-semibold text-sm border-b border-gray-200 border-l-4 border-l-[#1c3041] flex justify-between"><span>Live rider cash &amp; items</span><a class="underline text-white/80" href="{{ route('manager.remittances') }}">Manage</a></div>
        <div class="px-4 py-3 text-sm">Total cash with riders: <strong class="{{ $cashHeld > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $m($cashHeld) }}</strong></div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Rider</th><th class="text-right">Cash held</th><th class="text-right">Items to return</th><th class="p-3">Status</th></tr></thead>
            @foreach($riders as $r)
                <tr class="border-t"><td class="p-3">{{ $r->rider->name }}</td><td class="text-right">{{ $m($r->custody['cash_held']) }}</td><td class="text-right">{{ $r->custody['items_pending'] }}</td>
                    <td class="p-3">{!! $r->custody['settled'] ? '<span class="text-green-600">Cleared</span>' : ($r->custody['cleared'] ? '<span class="text-amber-600">Has open orders</span>' : '<span class="text-amber-600">Outstanding</span>') !!}</td></tr>
            @endforeach
        </table></div>
    </section>

    <section class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-white text-slate-800 font-semibold text-sm border-b border-gray-200 border-l-4 border-l-[#1c3041]">Delivery mode &amp; order type</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Mode</th><th class="text-right">Orders</th><th class="text-right p-3">Revenue</th></tr></thead>
            @foreach(\App\Models\Delivery::MODES as $k => $label)
                <tr class="border-t"><td class="p-3">{{ $label }}</td><td class="text-right">{{ $modes[$k]->orders ?? 0 }}</td><td class="text-right p-3">{{ $m($modes[$k]->revenue ?? 0) }}</td></tr>
            @endforeach
            <thead class="text-left text-slate-500 text-xs border-t"><tr><th class="p-3">Order type</th><th class="text-right">Items sent</th><th class="text-right p-3">Items returned</th></tr></thead>
            @foreach(\App\Models\Delivery::TYPES as $k => $label)
                <tr class="border-t"><td class="p-3">{{ $label }} ({{ $types[$k]->orders ?? 0 }})</td><td class="text-right">{{ $types[$k]->sent ?? 0 }}</td><td class="text-right p-3">{{ $types[$k]->returned ?? 0 }}</td></tr>
            @endforeach
        </table></div>
    </section>

    <section class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-white text-slate-800 font-semibold text-sm border-b border-gray-200 border-l-4 border-l-[#1c3041]">Top purchasing customers</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Customer</th><th class="text-right">Orders</th><th class="text-right">Spend</th><th class="text-right p-3">Balance</th></tr></thead>
            @forelse($topCustomers as $c)
                <tr class="border-t"><td class="p-3"><a class="text-brand-accent" href="{{ route('manager.customers.show', $c->id) }}">{{ $c->name }}</a></td><td class="text-right">{{ $c->orders }}</td><td class="text-right">{{ $m($c->spend) }}</td><td class="text-right p-3 {{ $c->spend - $c->paid > 0 ? 'text-red-600' : '' }}">{{ $m($c->spend - $c->paid) }}</td></tr>
            @empty <tr><td colspan="4" class="p-4 text-slate-400">No data yet.</td></tr> @endforelse
        </table></div>
    </section>

    @include('manager._top-products')
</div>
@endsection
