@extends('layouts.app')
@section('title', $product->name . ' · Drezwel Go')
@php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $input = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#42a5dc]';
@endphp
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="{{ $product->category ?: 'Product' }}{{ $product->sku ? ' · ' . $product->sku : '' }}" :title="$product->name" :subtitle="'Selling ' . $m($product->selling_price) . ' · cost ' . $m($product->cost_price) . ' · margin ' . $m($product->margin)">
    <x-slot:chips>
        <span><i class="fas fa-cubes"></i> {{ $product->stock_qty }} in stock</span>
        <span><i class="fas fa-bell"></i> alert at {{ $product->reorder_level }}</span>
        @unless($product->is_active)<span><i class="fas fa-eye-slash"></i> Hidden</span>@endunless
    </x-slot:chips>
    <a href="{{ route('manager.products.index') }}"><i class="fas fa-arrow-left"></i> Products</a>
    <a href="{{ route('manager.products.edit', $product) }}" class="primary"><i class="fas fa-pen"></i> Edit</a>
</x-dashboard-hero>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <x-stat-tile :color="$product->level === 'out' ? 'red' : ($product->level === 'low' ? 'orange' : 'green')" icon="fa-cubes" title="In stock" :value="$product->stock_qty" :sub="$product->level === 'out' ? 'Out of stock' : ($product->level === 'low' ? 'Running low' : 'Healthy')" />
    <x-stat-tile color="blue" icon="fa-bag-shopping" title="Units sold" :value="number_format($sold['units'])" sub="Excluding cancelled" />
    <x-stat-tile color="purple" icon="fa-sack-dollar" title="Sales revenue" :value="$m($sold['revenue'])" sub="From orders" />
    <x-stat-tile color="green" icon="fa-chart-line" title="Profit" :value="$m($sold['profit'])" sub="Revenue minus cost" />
</div>

<div class="grid lg:grid-cols-3 gap-5">
    <section class="bg-white rounded-xl p-5 border-l-4 border-l-[#8cc152] lg:col-span-1 h-fit">
        <h2 class="font-semibold mb-3"><i class="fas fa-boxes-stacked text-[#74a83b]"></i> Update stock</h2>
        <form method="POST" action="{{ route('manager.products.adjust', $product) }}" class="space-y-2" x-data>@csrf
            <select name="action" id="act" class="{{ $input }} w-full" onchange="document.getElementById('costRow').style.display = this.value==='purchase' ? '' : 'none'">
                @foreach($actions as $k => [$label])<option value="{{ $k }}">{{ $label }}</option>@endforeach
            </select>
            <input type="number" name="qty" min="1" required placeholder="Quantity" class="{{ $input }} w-full">
            <div id="costRow"><input type="number" step="0.01" min="0" name="unit_cost" placeholder="Cost per unit (optional, updates cost price)" class="{{ $input }} w-full"></div>
            <input name="note" placeholder="Note (supplier, reason…)" class="{{ $input }} w-full">
            <button class="w-full rounded-lg bg-[#8cc152] hover:bg-[#74a83b] text-white py-2 font-semibold transition">Save</button>
        </form>
        <form method="POST" action="{{ route('manager.products.toggle', $product) }}" class="mt-4">@csrf
            <button class="text-sm text-slate-500 hover:text-slate-800"><i class="fas {{ $product->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i> {{ $product->is_active ? 'Hide from new orders' : 'Show in new orders' }}</button>
        </form>
    </section>

    <section class="bg-white rounded-xl overflow-hidden lg:col-span-2">
        <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#42a5dc] font-semibold text-sm">Stock history</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">When</th><th>Type</th><th class="text-right">Change</th><th class="text-right">Balance</th><th>Order</th><th>By</th><th class="p-3">Note</th></tr></thead>
            @forelse($movements as $mv)
                <tr class="border-t border-slate-100 hover:bg-sky-50/50">
                    <td class="p-3 whitespace-nowrap">{{ $mv->created_at->format('d M, g:ia') }}</td>
                    <td>{{ \App\Models\StockMovement::TYPES[$mv->type] ?? $mv->type }}</td>
                    <td class="text-right font-semibold {{ $mv->qty > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $mv->qty > 0 ? '+' : '' }}{{ $mv->qty }}</td>
                    <td class="text-right">{{ $mv->balance_after }}</td>
                    <td>@if($mv->delivery)<a class="text-[#2c8fc6] hover:underline" href="{{ route('manager.deliveries.show', $mv->delivery) }}">{{ $mv->delivery->reference }}</a>@else — @endif</td>
                    <td>{{ $mv->user?->name }}</td><td class="p-3 text-slate-500">{{ $mv->note }}</td>
                </tr>
            @empty <tr><td colspan="7" class="p-8 text-center text-slate-400">No stock movements yet.</td></tr> @endforelse
        </table></div>
        <div class="p-3">{{ $movements->links() }}</div>
    </section>
</div>
@endsection
