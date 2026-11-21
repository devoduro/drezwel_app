@extends('layouts.app')
@section('title', ($product->exists ? 'Edit' : 'New') . ' product · Drezwel Go')
@php
    $v = fn ($k, $fallback = null) => old($k, $fallback);
    $input = 'mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]';
    $cell = 'rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm w-full';
@endphp
@section('content')
<x-dashboard-hero class="mb-6 !p-5" eyebrow="Products" :title="$product->exists ? 'Edit ' . $product->name : 'Add product'" subtitle="Photos, sizes and colours, and prices in cedis. Products marked for the online store appear on your website.">
    <a href="{{ $product->exists ? route('manager.products.show', $product) : route('manager.products.index') }}"><i class="fas fa-arrow-left"></i> Back</a>
</x-dashboard-hero>

<form method="POST" enctype="multipart/form-data" action="{{ $product->exists ? route('manager.products.update', $product) : route('manager.products.store') }}" class="space-y-5 max-w-4xl">
    @csrf @if($product->exists) @method('PUT') @endif

    <section class="bg-white rounded-xl p-5 grid md:grid-cols-2 gap-4">
        <label class="text-sm font-medium md:col-span-2">Product name<input name="name" required value="{{ $v('name', $product->name) }}" class="{{ $input }}" placeholder="e.g. Baggy jeans"></label>
        <label class="text-sm font-medium">Category<input name="category" list="cats" value="{{ $v('category', $product->category) }}" class="{{ $input }}" placeholder="Jeans, Sneakers, Dresses…"><datalist id="cats">@foreach($categories as $c)<option value="{{ $c }}">@endforeach</datalist></label>
        <label class="text-sm font-medium">SKU / code (optional)<input name="sku" value="{{ $v('sku', $product->sku) }}" class="{{ $input }}"></label>
        <label class="text-sm font-medium">Cost price (GH₵) - what you pay<input type="number" step="0.01" min="0" name="cost_price" required value="{{ $v('cost_price', $product->cost_price ?? 0) }}" class="{{ $input }}"></label>
        <label class="text-sm font-medium">Selling price (GH₵)<input type="number" step="0.01" min="0" name="selling_price" required value="{{ $v('selling_price', $product->selling_price ?? 0) }}" class="{{ $input }}"></label>
        <label class="text-sm font-medium">Old price (GH₵, optional)<input type="number" step="0.01" min="0" name="compare_at_price" value="{{ $v('compare_at_price', $product->compare_at_price) }}" class="{{ $input }}"><span class="text-xs font-normal text-slate-500">Higher than the selling price? The store shows it struck through with a -% badge. This does not add the product to Today's deals: use the tick box below for that.</span></label>
    <label class="text-sm font-medium">Alert me when total stock falls to<input type="number" min="0" name="reorder_level" required value="{{ $v('reorder_level', $product->reorder_level) }}" class="{{ $input }}"></label>
        <label class="flex items-center gap-2 text-sm font-medium self-end pb-2"><input type="checkbox" name="show_in_store" value="1" @checked($v('show_in_store', $product->show_in_store ?? true))> Show on the online store</label>
        <label class="flex items-center gap-2 text-sm font-medium md:col-span-2 rounded-lg bg-red-50 px-3 py-2 text-red-800"><input type="checkbox" name="is_deal" value="1" class="accent-red-600" @checked($v('is_deal', $product->is_deal ?? false))> <i class="fas fa-bolt"></i> Feature in Today's deals (hot deals row on the store). The price does not have to be reduced.</label>
        <label class="text-sm font-medium md:col-span-2">Description (shown to customers)<textarea name="description" rows="3" class="{{ $input }}" placeholder="Fabric, fit, care…">{{ $v('description', $product->description) }}</textarea></label>
        <label class="text-sm font-medium md:col-span-2">Private notes<textarea name="notes" rows="2" class="{{ $input }}">{{ $v('notes', $product->notes) }}</textarea></label>
    </section>

    <section class="bg-white rounded-xl p-5 border-l-4 border-l-[#967adc]">
        <h2 class="font-semibold mb-1"><i class="fas fa-images text-[#967adc]"></i> Photos</h2>
        <p class="text-xs text-slate-500 mb-3">JPG, PNG or WebP, up to 6 MB each, up to {{ \App\Services\ProductImageService::MAX_PER_PRODUCT }} photos. The first one is the main photo.</p>
        @if($product->exists && $product->images->count())
            <div class="flex flex-wrap gap-3 mb-3">
                @foreach($product->images as $img)
                    <div class="w-24 text-center">
                        <img src="{{ $img->url }}" alt="" class="h-24 w-24 rounded-lg object-cover border {{ $loop->first ? 'ring-2 ring-[#1c3041]' : '' }}">
                        <div class="mt-1 text-[11px] text-slate-500">{{ $loop->first ? 'Main photo' : '' }}</div>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-slate-500 mb-3">To remove a photo or change the main one, use the product page.</p>
        @endif
        <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-[#967adc] file:px-4 file:py-2 file:text-white file:font-semibold">
        <div id="preview" class="mt-3 flex flex-wrap gap-2"></div>
    </section>

    <section class="bg-white rounded-xl p-5 border-l-4 border-l-[#8cc152]" id="variants">
        <h2 class="font-semibold mb-1"><i class="fas fa-palette text-[#74a83b]"></i> Sizes &amp; colours</h2>
        <p class="text-xs text-slate-500 mb-3">Each size/colour combination has its own stock. Leave this empty for a product with no options.</p>

        @if($product->exists && $product->variants->count())
            <div class="overflow-x-auto mb-4"><table class="w-full text-sm">
                <thead class="text-left text-xs text-slate-500"><tr><th class="pb-1">Size</th><th>Colour</th><th>SKU</th><th>Own price (GH₵)</th><th>In stock</th><th title="Units to ADD to the stock you already have">Add stock (+)</th><th>Active</th></tr></thead>
                @foreach($product->variants as $var)
                    <tr class="align-middle">
                        <td class="pr-2 py-1"><input name="existing[{{ $var->id }}][size]" value="{{ $var->size }}" class="{{ $cell }}" placeholder="—"></td>
                        <td class="pr-2"><input name="existing[{{ $var->id }}][color]" value="{{ $var->color }}" class="{{ $cell }}" placeholder="—"></td>
                        <td class="pr-2"><input name="existing[{{ $var->id }}][sku]" value="{{ $var->sku }}" class="{{ $cell }}"></td>
                        <td class="pr-2"><input type="number" step="0.01" min="0" name="existing[{{ $var->id }}][price]" value="{{ $var->price }}" class="{{ $cell }}" placeholder="same as product"></td>
                        <td class="pr-2 font-semibold {{ $var->stock_qty <= 0 ? 'text-red-600' : '' }}">{{ $var->stock_qty }}</td>
                        <td class="pr-2"><input type="number" min="0" max="100000" name="existing[{{ $var->id }}][add]" value="{{ old('existing.' . $var->id . '.add') }}" placeholder="+ 0" class="{{ $cell }} !w-24 border-[#8cc152] bg-green-50/50" title="Type how many new units arrived. They are added to the {{ $var->stock_qty }} already in stock."></td>
                        <td><input type="checkbox" name="existing[{{ $var->id }}][is_active]" value="1" @checked($var->is_active)></td>
                    </tr>
                @endforeach
            </table></div>
            <div class="mb-3 grid gap-2 rounded-lg bg-green-50 p-3 text-sm sm:grid-cols-2">
                <div class="sm:col-span-2 text-xs text-slate-600"><i class="fas fa-circle-plus text-[#74a83b]"></i> Type a number under <strong>Add stock (+)</strong> for each size/colour that has arrived. It is <strong>added</strong> to what you already have and recorded in the stock history. To take stock out or correct a count, use "Update stock" on the product page.</div>
                <label class="text-xs font-medium">Cost per unit of this new stock (GH₵, optional)<input type="number" step="0.01" min="0" name="restock_cost" value="{{ old('restock_cost') }}" class="{{ $cell }} mt-1" placeholder="updates the cost price"></label>
                <label class="text-xs font-medium">Note (supplier, invoice…)<input name="restock_note" value="{{ old('restock_note') }}" maxlength="200" class="{{ $cell }} mt-1" placeholder="e.g. Kantamanto order"></label>
            </div>
        @endif

        <div class="rounded-lg bg-slate-50 p-3 mb-3">
            <div class="text-xs font-semibold text-slate-600 mb-2">Quick add: type sizes and colours separated by commas</div>
            <div class="grid sm:grid-cols-4 gap-2">
                <input id="genSizes" placeholder="Sizes: S, M, L, XL" class="{{ $cell }}">
                <input id="genColors" placeholder="Colours: Black, Blue" class="{{ $cell }}">
                <input id="genStock" type="number" min="0" placeholder="Stock each" class="{{ $cell }}">
                <button type="button" id="genBtn" class="rounded-lg bg-[#8cc152] hover:bg-[#74a83b] text-white px-3 py-1.5 text-sm font-semibold transition">Add combinations</button>
            </div>
        </div>

        <div id="newRows" class="space-y-2"></div>
        <button type="button" id="addRow" class="mt-2 text-sm text-[#1c3041] font-semibold"><i class="fas fa-plus"></i> Add one size/colour</button>

        @unless($product->exists)
            <label class="block mt-4 text-sm font-medium">If there are no sizes or colours, opening stock (units on hand now)
                <input type="number" min="0" name="opening_stock" value="{{ $v('opening_stock', 0) }}" class="{{ $input }} max-w-xs"></label>
        @endunless
    </section>

    <div class="flex gap-3">
        <button class="rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-6 py-2.5 font-semibold transition">{{ $product->exists ? 'Save changes' : 'Add product' }}</button>
        <a href="{{ route('manager.products.index') }}" class="px-4 py-2.5 text-slate-500">Cancel</a>
    </div>
</form>

<script>
(function () {
    const rows = document.getElementById('newRows'); let n = 0;
    const cell = 'rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm w-full';
    function add(size = '', color = '', stock = '') {
        const i = n++, d = document.createElement('div'); d.className = 'grid grid-cols-2 sm:grid-cols-6 gap-2 items-center';
        d.innerHTML = `<input name="new[${i}][size]" value="${size}" placeholder="Size" class="${cell}">
            <input name="new[${i}][color]" value="${color}" placeholder="Colour" class="${cell}">
            <input name="new[${i}][sku]" placeholder="SKU (optional)" class="${cell}">
            <input type="number" step="0.01" min="0" name="new[${i}][price]" placeholder="Own price" class="${cell}">
            <input type="number" min="0" name="new[${i}][stock]" value="${stock}" placeholder="Stock" class="${cell}">
            <button type="button" class="text-red-500 text-left px-2"><i class="fas fa-trash"></i></button>`;
        d.querySelector('button').addEventListener('click', () => d.remove());
        rows.appendChild(d);
    }
    const split = s => s.split(',').map(x => x.trim()).filter(Boolean);
    document.getElementById('addRow').addEventListener('click', () => add());
    document.getElementById('genBtn').addEventListener('click', () => {
        const sizes = split(document.getElementById('genSizes').value), colors = split(document.getElementById('genColors').value), stock = document.getElementById('genStock').value;
        const S = sizes.length ? sizes : [''], C = colors.length ? colors : [''];
        if (!sizes.length && !colors.length) return;
        S.forEach(s => C.forEach(c => add(s, c, stock)));
    });
    // photo previews
    document.querySelector('input[type=file]').addEventListener('change', e => {
        const box = document.getElementById('preview'); box.innerHTML = '';
        [...e.target.files].forEach(f => { const im = document.createElement('img'); im.src = URL.createObjectURL(f); im.className = 'h-20 w-20 rounded-lg object-cover border'; box.appendChild(im); });
    });
    @if(old('new'))
        @foreach(old('new') as $r) add(@json($r['size'] ?? ''), @json($r['color'] ?? ''), @json($r['stock'] ?? '')); @endforeach
    @endif
})();
</script>
@endsection
