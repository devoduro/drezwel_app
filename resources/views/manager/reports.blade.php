@extends('layouts.app')
@section('title', 'Reports · Drezwel Go')
@php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $label = $periods[$period] ?? 'All time';
    $range = ($from || $to) ? ($from?->format('d M Y') ?? '…') . ' – ' . ($to?->format('d M Y') ?? '…') : 'All records';
    $qs = request()->only(['period', 'from', 'to']);
    $th = 'text-left text-slate-500 text-xs';
    $head = 'px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm text-slate-800';
@endphp
@section('content')
<x-dashboard-hero class="mb-6" :eyebrow="$range" title="Business report" subtitle="Finances, orders and stock in one place. Print it or download the data as CSV.">
    <x-slot:chips>
        <span><i class="fas fa-calendar-days"></i> {{ $label }}</span>
        <span><i class="fas fa-box"></i> {{ $orders }} orders</span>
        <span><i class="fas fa-coins"></i> {{ $m($finance['revenue']) }}</span>
    </x-slot:chips>
    <form method="GET" class="no-print flex flex-wrap gap-2 text-sm items-center">
        <select name="period" onchange="this.form.submit()" class="rounded-xl border-0 bg-white/10 text-white ring-1 ring-white/20 px-3 py-2.5 focus:outline-none">
            @foreach($periods as $k => $l)<option class="text-slate-800" value="{{ $k }}" @selected($period === $k)>{{ $l }}</option>@endforeach
        </select>
        @if($period === 'custom')
            <input type="date" name="from" value="{{ request('from') }}" class="rounded-xl bg-white text-slate-800 px-2 py-2">
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-xl bg-white text-slate-800 px-2 py-2">
            <button class="rounded-xl bg-white text-slate-800 px-3 py-2 font-semibold">Apply</button>
        @endif
        <button type="button" onclick="window.print()" class="rounded-xl bg-white text-slate-800 px-4 py-2.5 font-semibold hover:bg-gray-100 transition"><i class="fas fa-print"></i> Print / PDF</button>
    </form>
</x-dashboard-hero>

<div class="no-print mb-6 flex flex-wrap gap-2 text-sm">
    <span class="text-slate-500 self-center">Download CSV:</span>
    <a class="rounded-lg bg-white border border-slate-200 px-3 py-1.5 hover:border-[#1c3041]" href="{{ route('manager.reports.export', ['type' => 'orders'] + $qs) }}"><i class="fas fa-file-csv text-[#74a83b]"></i> Orders</a>
    <a class="rounded-lg bg-white border border-slate-200 px-3 py-1.5 hover:border-[#1c3041]" href="{{ route('manager.reports.export', ['type' => 'sales'] + $qs) }}"><i class="fas fa-file-csv text-[#74a83b]"></i> Sales by product</a>
    <a class="rounded-lg bg-white border border-slate-200 px-3 py-1.5 hover:border-[#1c3041]" href="{{ route('manager.reports.export', ['type' => 'stock']) }}"><i class="fas fa-file-csv text-[#74a83b]"></i> Current stock</a>
</div>

{{-- ======================= FINANCES ======================= --}}
<h2 class="text-lg font-bold mb-3"><i class="fas fa-sack-dollar text-[#1c3041]"></i> Finances</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
    <x-stat-tile color="blue" icon="fa-receipt" title="Billed to customers" :value="$m($finance['revenue'])" :sub="'Goods ' . $m($finance['goods']) . ' + delivery ' . $m($finance['fees_charged'])" />
    <x-stat-tile color="green" icon="fa-money-bill-wave" title="Collected" :value="$m($finance['collected'])" :sub="$finance['collection_rate'] . '% of revenue'" />
    <x-stat-tile color="red" icon="fa-hand-holding-dollar" title="Outstanding" :value="$m($finance['outstanding'])" sub="Owed by customers" />
    <x-stat-tile :color="$finance['net'] >= 0 ? 'purple' : 'red'" icon="fa-chart-line" title="Net profit" :value="$m($finance['net'])" sub="Goods profit - delivery fees go to riders" />
</div>

<div class="grid xl:grid-cols-2 gap-5 mb-8">
    <section class="bg-white rounded-xl overflow-hidden">
        <div class="{{ $head }}">Profit &amp; loss (orders with products)</div>
        <table class="w-full text-sm">
            @foreach([
                ['Sales of products', $finance['line_revenue'], ''],
                ['Cost of goods sold', -$finance['cogs'], 'text-slate-500'],
                ['Gross profit', $finance['gross'], 'font-semibold border-t'],
                ['Net profit', $finance['net'], 'font-bold border-t text-base ' . ($finance['net'] >= 0 ? 'text-green-700' : 'text-red-600')],
            ] as [$l, $v, $c])
                <tr class="{{ $c }}"><td class="px-4 py-2">{{ $l }}</td><td class="px-4 py-2 text-right">{{ $v < 0 ? '- ' . $m(abs($v)) : $m($v) }}</td></tr>
            @endforeach
            <tr class="border-t bg-slate-50 text-xs text-slate-500"><td class="px-4 py-2">Gross margin</td><td class="px-4 py-2 text-right">{{ $finance['margin'] }}%</td></tr>
            @if($finance['untracked'] > 0)
                <tr class="bg-amber-50 text-xs text-amber-800"><td class="px-4 py-2" colspan="2"><i class="fas fa-circle-info"></i> {{ $m($finance['untracked']) }} of revenue comes from orders with no products attached, so its cost is not counted.</td></tr>
            @endif
        </table>
    </section>

    <section class="bg-white rounded-xl overflow-hidden">
        <div class="{{ $head }}">Delivery fees (collected for riders)</div>
        <table class="w-full text-sm">
            @foreach([
                ['Charged to customers', $finance['fees_charged']],
                ['Earned by riders (delivered orders)', $finance['fees_earned']],
                ['Paid back to riders', $finance['fees_paid']],
            ] as [$l, $v])
                <tr class="border-t border-slate-100"><td class="px-4 py-2">{{ $l }}</td><td class="px-4 py-2 text-right">{{ $m($v) }}</td></tr>
            @endforeach
            <tr class="border-t font-semibold bg-purple-50"><td class="px-4 py-2">Still owed to riders</td><td class="px-4 py-2 text-right">{{ $m($finance['fees_owed']) }}</td></tr>
            <tr class="bg-slate-50 text-xs text-slate-500"><td class="px-4 py-2" colspan="2">Customers pay goods + delivery fee to the rider, the rider hands it all in, and the fee is paid back to them. It is not business income or cost.</td></tr>
        </table>
    </section>

    <section class="bg-white rounded-xl overflow-hidden">
        <div class="{{ $head }}">Money received by method</div>
        <table class="w-full text-sm">
            <thead class="{{ $th }}"><tr><th class="px-4 py-2">Method</th><th class="text-right">Payments</th><th class="text-right px-4">Amount</th></tr></thead>
            @foreach(\App\Models\Payment::METHODS as $k => $l)
                <tr class="border-t border-slate-100"><td class="px-4 py-2">{{ $l }}</td><td class="text-right">{{ $payments[$k]->n ?? 0 }}</td><td class="text-right px-4">{{ $m($payments[$k]->total ?? 0) }}</td></tr>
            @endforeach
            <tr class="border-t font-semibold bg-slate-50"><td class="px-4 py-2">Total</td><td class="text-right">{{ $payments->sum('n') }}</td><td class="text-right px-4">{{ $m($payments->sum('total')) }}</td></tr>
        </table>
    </section>

    <section class="bg-white rounded-xl overflow-hidden xl:col-span-2">
        <div class="{{ $head }}">Daily orders &amp; revenue</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="{{ $th }}"><tr><th class="px-4 py-2">Date</th><th class="text-right">Orders</th><th class="text-right">Delivered</th><th class="text-right">Revenue</th><th class="text-right px-4">Collected</th></tr></thead>
            @forelse($daily as $r)
                <tr class="border-t border-slate-100"><td class="px-4 py-2">{{ \Carbon\Carbon::parse($r->day)->format('D d M Y') }}</td><td class="text-right">{{ $r->orders }}</td><td class="text-right">{{ $r->delivered }}</td><td class="text-right">{{ $m($r->revenue) }}</td><td class="text-right px-4">{{ $m($r->paid) }}</td></tr>
            @empty <tr><td colspan="5" class="p-6 text-center text-slate-400">No orders in this period.</td></tr> @endforelse
        </table></div>
    </section>
</div>

{{-- ======================= ORDERS ======================= --}}
<h2 class="text-lg font-bold mb-3 print-break"><i class="fas fa-truck-fast text-[#1c3041]"></i> Orders</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
    <x-stat-tile color="blue" icon="fa-box" title="Orders" :value="$orders" sub="Excluding cancelled" />
    <x-stat-tile color="green" icon="fa-circle-check" title="Delivered" :value="$delivered" :sub="($orders ? round($delivered / $orders * 100, 1) : 0) . '% fulfilment'" />
    <x-stat-tile color="orange" icon="fa-hourglass-half" title="Pending" :value="($byStatus['pending'] ?? 0) + ($byStatus['assigned'] ?? 0) + ($byStatus['out_for_delivery'] ?? 0)" sub="Not delivered yet" />
    <x-stat-tile color="slate" icon="fa-tag" title="Average order" :value="$m($orders ? $finance['revenue'] / $orders : 0)" sub="Revenue per order" />
</div>

<div class="grid xl:grid-cols-2 gap-5 mb-5">
    <section class="bg-white rounded-xl overflow-hidden">
        <div class="{{ $head }}">By status, mode &amp; type</div>
        <table class="w-full text-sm">
            <thead class="{{ $th }}"><tr><th class="px-4 py-2">Status</th><th class="text-right px-4">Orders</th></tr></thead>
            @foreach(\App\Models\Delivery::STATUSES as $k => $l) @continue($k === 'cancelled')
                <tr class="border-t border-slate-100"><td class="px-4 py-2"><x-badge :status="$k"/></td><td class="text-right px-4">{{ $byStatus[$k] ?? 0 }}</td></tr>
            @endforeach
            <thead class="{{ $th }} border-t"><tr><th class="px-4 py-2">Delivery mode</th><th class="text-right px-4">Orders · Revenue</th></tr></thead>
            @foreach(\App\Models\Delivery::MODES as $k => $l)
                <tr class="border-t border-slate-100"><td class="px-4 py-2">{{ $l }}</td><td class="text-right px-4">{{ $modes[$k]->n ?? 0 }} · {{ $m($modes[$k]->revenue ?? 0) }}</td></tr>
            @endforeach
            <thead class="{{ $th }} border-t"><tr><th class="px-4 py-2">Order type</th><th class="text-right px-4">Orders · Revenue</th></tr></thead>
            @foreach(\App\Models\Delivery::TYPES as $k => $l)
                <tr class="border-t border-slate-100"><td class="px-4 py-2">{{ $l }}</td><td class="text-right px-4">{{ $types[$k]->n ?? 0 }} · {{ $m($types[$k]->revenue ?? 0) }}</td></tr>
            @endforeach
        </table>
    </section>

    <section class="bg-white rounded-xl overflow-hidden">
        <div class="{{ $head }}">Top customers</div>
        <table class="w-full text-sm">
            <thead class="{{ $th }}"><tr><th class="px-4 py-2">Customer</th><th class="text-right">Orders</th><th class="text-right">Spend</th><th class="text-right px-4">Owes</th></tr></thead>
            @forelse($topCustomers as $c)
                <tr class="border-t border-slate-100"><td class="px-4 py-2">{{ $c->name }}</td><td class="text-right">{{ $c->orders }}</td><td class="text-right">{{ $m($c->spend) }}</td><td class="text-right px-4 {{ $c->spend - $c->paid > 0 ? 'text-red-600' : '' }}">{{ $m($c->spend - $c->paid) }}</td></tr>
            @empty <tr><td colspan="4" class="p-6 text-center text-slate-400">No data.</td></tr> @endforelse
        </table>
    </section>

    <section class="bg-white rounded-xl overflow-hidden xl:col-span-2">
        <div class="{{ $head }}">Rider performance &amp; custody</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="{{ $th }}"><tr><th class="px-4 py-2">Rider</th><th class="text-right">Assigned</th><th class="text-right">Delivered</th><th class="text-right">Undelivered</th><th class="text-right">Rate</th><th class="text-right">Value</th><th class="text-right">Fees</th><th class="text-right">Cash held</th><th class="text-right px-4">Items to return</th></tr></thead>
            @forelse($riders as $r)
                <tr class="border-t border-slate-100"><td class="px-4 py-2 font-medium">{{ $r->rider->name }}</td><td class="text-right">{{ $r->assigned }}</td><td class="text-right">{{ $r->delivered }}</td><td class="text-right">{{ $r->undelivered }}</td><td class="text-right">{{ $r->rate }}%</td>
                    <td class="text-right">{{ $m($r->value) }}</td><td class="text-right">{{ $m($r->fees) }}</td><td class="text-right {{ $r->custody['cash_held'] > 0 ? 'text-red-600 font-medium' : '' }}">{{ $m($r->custody['cash_held']) }}</td><td class="text-right px-4">{{ $r->custody['items_pending'] }}</td></tr>
            @empty <tr><td colspan="9" class="p-6 text-center text-slate-400">No rider activity.</td></tr> @endforelse
        </table></div>
    </section>
</div>

{{-- ======================= STOCK ======================= --}}
<h2 class="text-lg font-bold mb-3 mt-8 print-break"><i class="fas fa-boxes-stacked text-[#1c3041]"></i> Stock</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
    <x-stat-tile color="blue" icon="fa-cubes" title="Units in stock" :value="number_format($stock['units'])" :sub="$stock['products'] . ' products'" />
    <x-stat-tile color="orange" icon="fa-coins" title="Stock at cost" :value="$m($stock['cost_value'])" sub="Money tied up in stock" />
    <x-stat-tile color="green" icon="fa-sack-dollar" title="Stock at selling price" :value="$m($stock['retail_value'])" :sub="'Potential profit ' . $m($stock['potential_profit'])" />
    <x-stat-tile :color="$needing->count() ? 'red' : 'green'" icon="fa-triangle-exclamation" title="Need restocking" :value="$needing->count()" sub="Low or out of stock" />
</div>

<div class="grid xl:grid-cols-2 gap-5">
    <section class="bg-white rounded-xl overflow-hidden">
        <div class="{{ $head }}">Reorder list</div>
        <table class="w-full text-sm">
            <thead class="{{ $th }}"><tr><th class="px-4 py-2">Product</th><th class="text-right">In stock</th><th class="text-right">Reorder at</th><th class="px-4">Level</th></tr></thead>
            @forelse($needing as $p)
                <tr class="border-t border-slate-100"><td class="px-4 py-2"><a class="text-[#1c3041] hover:underline" href="{{ route('manager.products.show', $p) }}">{{ $p->name }}</a></td><td class="text-right font-bold {{ $p->level === 'out' ? 'text-red-600' : 'text-amber-600' }}">{{ $p->stock_qty }}</td><td class="text-right">{{ $p->reorder_level }}</td><td class="px-4">@include('manager.products._level', ['p' => $p])</td></tr>
            @empty <tr><td colspan="4" class="p-6 text-center text-slate-400"><i class="fas fa-circle-check text-green-500"></i> Everything is above its reorder level.</td></tr> @endforelse
        </table>
    </section>

    <section class="bg-white rounded-xl overflow-hidden">
        <div class="{{ $head }}">Stock movement in period</div>
        <table class="w-full text-sm">
            <thead class="{{ $th }}"><tr><th class="px-4 py-2">Type</th><th class="text-right">Entries</th><th class="text-right px-4">Units</th></tr></thead>
            @foreach(\App\Models\StockMovement::TYPES as $k => $l)
                <tr class="border-t border-slate-100"><td class="px-4 py-2">{{ $l }}</td><td class="text-right">{{ $movements[$k]->n ?? 0 }}</td><td class="text-right px-4 {{ ($movements[$k]->units ?? 0) < 0 ? 'text-red-600' : 'text-green-600' }}">{{ ($movements[$k]->units ?? 0) > 0 ? '+' : '' }}{{ $movements[$k]->units ?? 0 }}</td></tr>
            @endforeach
        </table>
    </section>

    <section class="bg-white rounded-xl overflow-hidden xl:col-span-2">
        <div class="{{ $head }}">Best sellers &amp; profit by product</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="{{ $th }}"><tr><th class="px-4 py-2">Product</th><th class="text-right">Units sold</th><th class="text-right">Revenue</th><th class="text-right">Cost</th><th class="text-right">Profit</th><th class="text-right px-4">Margin</th></tr></thead>
            @forelse($sales as $s)
                @php $profit = $s->revenue - $s->cost; @endphp
                <tr class="border-t border-slate-100"><td class="px-4 py-2">{{ $s->name }}<div class="text-xs text-slate-400">{{ $s->sku }}</div></td><td class="text-right font-semibold">{{ $s->units }}</td><td class="text-right">{{ $m($s->revenue) }}</td><td class="text-right text-slate-500">{{ $m($s->cost) }}</td>
                    <td class="text-right {{ $profit >= 0 ? 'text-green-700' : 'text-red-600' }}">{{ $m($profit) }}</td><td class="text-right px-4">{{ $s->revenue > 0 ? round($profit / $s->revenue * 100, 1) : 0 }}%</td></tr>
            @empty <tr><td colspan="6" class="p-6 text-center text-slate-400">No product sales in this period. Add products to orders to see them here.</td></tr> @endforelse
        </table></div>
    </section>
</div>

<p class="mt-6 text-xs text-slate-400">Generated {{ now()->format('d M Y, g:ia') }} · Revenue and orders are by delivery date; money received is by payment date.</p>
@endsection
