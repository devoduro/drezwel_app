@extends('layouts.app')
@section('title', 'Products & stock · Drezwel Go')
@php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $input = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#42a5dc]';
@endphp
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Inventory" title="Products & stock" subtitle="Track every item, see what is running low, and get alerted before it is finished.">
    <x-slot:chips>
        <span><i class="fas fa-shirt"></i> {{ $stats['products'] }} products</span>
        <span><i class="fas fa-cubes"></i> {{ number_format($stats['units']) }} units</span>
        <span><i class="fas fa-coins"></i> {{ $m($stats['cost_value']) }} at cost</span>
    </x-slot:chips>
    <a href="{{ route('manager.reports') }}"><i class="fas fa-chart-pie"></i> Reports</a>
    <a href="{{ route('manager.products.create') }}" class="primary"><i class="fas fa-plus"></i> Add product</a>
</x-dashboard-hero>

@if($stats['out'] + $stats['low'] > 0)
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 flex flex-wrap items-center justify-between gap-2">
        <span><i class="fas fa-triangle-exclamation"></i> <strong>{{ $stats['out'] }}</strong> out of stock and <strong>{{ $stats['low'] }}</strong> running low.</span>
        <a class="font-semibold underline" href="{{ route('manager.products.index', ['level' => $stats['out'] ? 'out' : 'low']) }}">Show them</a>
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <x-stat-tile color="blue" icon="fa-cubes" title="Units in stock" :value="number_format($stats['units'])" :sub="$stats['products'] . ' active products'" />
    <x-stat-tile color="green" icon="fa-sack-dollar" title="Stock value" :value="$m($stats['retail_value'])" :sub="'Cost ' . $m($stats['cost_value'])" />
    <x-stat-tile color="orange" icon="fa-battery-quarter" title="Running low" :value="$stats['low']" sub="At or below reorder level" :href="route('manager.products.index', ['level' => 'low'])" />
    <x-stat-tile color="red" icon="fa-ban" title="Out of stock" :value="$stats['out']" sub="Cannot be ordered" :href="route('manager.products.index', ['level' => 'out'])" />
</div>

<div class="bg-white rounded-xl p-4 mb-5 border-l-4 border-l-[#967adc]">
    <div class="text-sm font-semibold mb-2"><i class="fas fa-bell text-[#967adc]"></i> SMS alerts when stock runs low</div>
    <form method="POST" action="{{ route('manager.alert-phone') }}" class="flex flex-wrap gap-2 items-center text-sm">@csrf @method('PUT')
        <input name="phone" value="{{ $alertPhone }}" inputmode="tel" placeholder="Your phone e.g. 0241234567" required class="{{ $input }} w-60">
        <button class="rounded-lg bg-[#967adc] hover:bg-[#7d62c4] text-white px-4 py-2 font-semibold transition">Save</button>
        <span class="text-slate-500">{{ $alertPhone ? 'Alerts go to ' . $alertPhone . '.' : 'No phone saved - you will only see on-screen alerts.' }}</span>
    </form>
</div>

<form method="GET" class="bg-white rounded-xl p-3 grid grid-cols-2 md:grid-cols-5 gap-2 mb-4 text-sm">
    <div class="relative col-span-2">
        <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input name="q" value="{{ request('q') }}" placeholder="Search name or SKU…" class="{{ $input }} w-full pl-9">
    </div>
    <select name="category" class="{{ $input }}"><option value="">All categories</option>@foreach($categories as $c)<option @selected(request('category') === $c)>{{ $c }}</option>@endforeach</select>
    <select name="level" class="{{ $input }}"><option value="">All stock levels</option>
        @foreach(['ok' => 'In stock', 'low' => 'Running low', 'out' => 'Out of stock'] as $k => $l)<option value="{{ $k }}" @selected(request('level') === $k)>{{ $l }}</option>@endforeach</select>
    <div class="flex items-center gap-2"><label class="flex items-center gap-1 whitespace-nowrap"><input type="checkbox" name="inactive" value="1" @checked(request()->boolean('inactive'))> Hidden</label>
        <button class="rounded-lg bg-[#42a5dc] hover:bg-[#2c8fc6] text-white px-4 py-2 font-semibold transition flex-1"><i class="fas fa-filter"></i></button></div>
</form>

<div class="bg-white rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#42a5dc] font-semibold text-sm">All products</div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Product</th><th>Category</th><th class="text-right">In stock</th><th class="text-right">Reorder at</th><th class="text-right">Cost</th><th class="text-right">Price</th><th class="text-right">Value</th><th class="p-3">Level</th></tr></thead>
        @forelse($products as $p)
            <tr class="border-t border-slate-100 hover:bg-sky-50/50 {{ $p->is_active ? '' : 'opacity-50' }}">
                <td class="p-3"><div class="flex items-center gap-3">
                    @if($p->primary_image_url)<img src="{{ $p->primary_image_url }}" alt="" class="h-11 w-11 rounded-lg object-cover border shrink-0">@else<span class="grid h-11 w-11 shrink-0 place-items-center rounded-lg bg-slate-100 text-slate-300"><i class="fas fa-shirt"></i></span>@endif
                    <div><a class="font-semibold text-[#2c8fc6] hover:underline" href="{{ route('manager.products.show', $p) }}">{{ $p->name }}</a>
                    <div class="text-xs text-slate-400">{{ $p->sku ?: 'No SKU' }}@if($p->variants->count() > 1 || $p->has_options) · {{ $p->variants->count() }} variants @endif @if($p->show_in_store)· <i class="fas fa-store" title="On the online store"></i>@endif</div></div></div></td>
                <td class="text-slate-600">{{ $p->category ?: '—' }}</td>
                <td class="text-right font-bold {{ $p->level === 'out' ? 'text-red-600' : ($p->level === 'low' ? 'text-[#de7a14]' : '') }}">{{ $p->stock_qty }}</td>
                <td class="text-right text-slate-500">{{ $p->reorder_level }}</td>
                <td class="text-right text-slate-500">{{ number_format($p->cost_price, 2) }}</td>
                <td class="text-right">{{ number_format($p->selling_price, 2) }}</td>
                <td class="text-right text-slate-500">{{ number_format($p->stock_qty * $p->selling_price, 2) }}</td>
                <td class="p-3">@include('manager.products._level', ['p' => $p])</td>
            </tr>
        @empty <tr><td colspan="8" class="p-10 text-center text-slate-400"><i class="fas fa-shirt text-3xl mb-2 block"></i>No products yet. <a class="text-[#2c8fc6] font-semibold" href="{{ route('manager.products.create') }}">Add your first product</a>.</td></tr> @endforelse
    </table></div>
</div>
<div class="mt-4">{{ $products->links() }}</div>
@endsection
