@extends('layouts.app')
@section('title', ($product->exists ? 'Edit' : 'New') . ' product · Drezwel Go')
@php
    $v = fn ($k, $fallback = null) => old($k, $fallback);
    $input = 'mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#42a5dc]';
@endphp
@section('content')
<x-dashboard-hero class="mb-6 !p-5" eyebrow="Inventory" :title="$product->exists ? 'Edit ' . $product->name : 'Add product'" subtitle="Set the cost and selling price so reports can show your profit.">
    <a href="{{ $product->exists ? route('manager.products.show', $product) : route('manager.products.index') }}"><i class="fas fa-arrow-left"></i> Back</a>
</x-dashboard-hero>

<form method="POST" action="{{ $product->exists ? route('manager.products.update', $product) : route('manager.products.store') }}" class="bg-white rounded-xl p-5 grid md:grid-cols-2 gap-4 max-w-3xl">
    @csrf @if($product->exists) @method('PUT') @endif
    <label class="text-sm font-medium md:col-span-2">Product name<input name="name" required value="{{ $v('name', $product->name) }}" class="{{ $input }}" placeholder="e.g. Baggy jeans - blue, size 32"></label>
    <label class="text-sm font-medium">SKU / code (optional)<input name="sku" value="{{ $v('sku', $product->sku) }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Category<input name="category" list="cats" value="{{ $v('category', $product->category) }}" class="{{ $input }}" placeholder="Jeans, Sneakers, …"><datalist id="cats">@foreach($categories as $c)<option value="{{ $c }}">@endforeach</datalist></label>
    <label class="text-sm font-medium">Cost price (GH₵) - what you pay<input type="number" step="0.01" min="0" name="cost_price" required value="{{ $v('cost_price', $product->cost_price ?? 0) }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Selling price (GH₵)<input type="number" step="0.01" min="0" name="selling_price" required value="{{ $v('selling_price', $product->selling_price ?? 0) }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Alert me when stock falls to<input type="number" min="0" name="reorder_level" required value="{{ $v('reorder_level', $product->reorder_level) }}" class="{{ $input }}"></label>
    @unless($product->exists)
        <label class="text-sm font-medium">Opening stock (units on hand now)<input type="number" min="0" name="opening_stock" value="{{ $v('opening_stock', 0) }}" class="{{ $input }}"></label>
    @endunless
    <label class="text-sm font-medium md:col-span-2">Notes<textarea name="notes" rows="2" class="{{ $input }}">{{ $v('notes', $product->notes) }}</textarea></label>
    <div class="md:col-span-2 flex gap-3">
        <button class="rounded-lg bg-[#42a5dc] hover:bg-[#2c8fc6] text-white px-5 py-2 font-semibold transition">{{ $product->exists ? 'Save changes' : 'Add product' }}</button>
        <a href="{{ route('manager.products.index') }}" class="px-4 py-2 text-slate-500">Cancel</a>
    </div>
    @if($product->exists)<p class="md:col-span-2 text-xs text-slate-500">To change how many are in stock, use "Update stock" on the product page so the change is recorded.</p>@endif
</form>
@endsection
