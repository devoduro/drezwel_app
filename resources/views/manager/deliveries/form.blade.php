@extends('layouts.app')
@section('title', ($delivery->exists ? 'Edit' : 'New') . ' delivery · Drezwel Go')
@php
    $v = fn ($k, $fallback = null) => old($k, $fallback);
    $input = 'mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#42a5dc]';
@endphp
@section('content')
<x-dashboard-hero class="mb-6 !p-5" eyebrow="Deliveries" :title="$delivery->exists ? 'Edit ' . $delivery->reference : 'New delivery'" subtitle="Customers are matched by phone number, so repeat buyers keep one record.">
    <a href="{{ route('manager.deliveries.index') }}"><i class="fas fa-arrow-left"></i> Back</a>
</x-dashboard-hero>
<form method="POST" action="{{ $delivery->exists ? route('manager.deliveries.update', $delivery) : route('manager.deliveries.store') }}" class="bg-white rounded-xl shadow-sm p-5 grid md:grid-cols-2 gap-4 max-w-4xl">
    @csrf @if($delivery->exists) @method('PUT') @endif
    <label class="text-sm font-medium">Customer name<input name="customer_name" required value="{{ $v('customer_name', $delivery->customer?->name ?? request('name')) }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Customer phone<input name="customer_phone" required inputmode="tel" value="{{ $v('customer_phone', $delivery->customer?->phone ?? request('phone')) }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Location<input name="location" required value="{{ $v('location', $delivery->location ?? request('location')) }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Package description (auto-filled from products)<input name="package_description" value="{{ $v('package_description', $delivery->package_description) }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Delivery mode<select name="delivery_mode" class="{{ $input }} bg-white">@foreach(\App\Models\Delivery::MODES as $k => $l)<option value="{{ $k }}" @selected($v('delivery_mode', $delivery->delivery_mode) === $k)>{{ $l }}</option>@endforeach</select></label>
    <label class="text-sm font-medium">Order type<select name="order_type" class="{{ $input }} bg-white">@foreach(\App\Models\Delivery::TYPES as $k => $l)<option value="{{ $k }}" @selected($v('order_type', $delivery->order_type) === $k)>{{ $l }}</option>@endforeach</select></label>
    <label class="text-sm font-medium">Delivery date<input type="date" name="delivery_date" required value="{{ $v('delivery_date', optional($delivery->delivery_date)->format('Y-m-d')) }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Preferred time window<select name="time_window" class="{{ $input }} bg-white"><option value="">Any time</option>@foreach(\App\Models\Delivery::WINDOWS as $w)<option @selected($v('time_window', $delivery->time_window) === $w)>{{ $w }}</option>@endforeach</select></label>
    <label class="text-sm font-medium">Goods total (GH₵) - price of the items<input type="number" step="0.01" min="0" name="total_amount" required value="{{ $v('total_amount', $delivery->total_amount) }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Delivery fee (GH₵) - customer pays this on top; rider gets it back<input type="number" step="0.01" min="0" name="delivery_fee" value="{{ $v('delivery_fee', $delivery->delivery_fee) }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Items sent<input type="number" min="1" name="items_sent" value="{{ $v('items_sent', $delivery->items_sent) }}" class="{{ $input }}"></label>
    @unless($delivery->exists)
        <label class="text-sm font-medium">Assign a rider now (optional)<select name="rider_id" class="{{ $input }} bg-white"><option value="">Leave open for riders to pick</option>@foreach($riders as $r)<option value="{{ $r->id }}" @selected($v('rider_id') == $r->id)>{{ $r->name }}</option>@endforeach</select></label>
    @endunless
    @php
        $lineRows = old('items', isset($delivery) && $delivery->exists ? $delivery->items->map(fn ($i) => ['variant_id' => $i->variant_id, 'qty' => $i->qty, 'price' => (float) $i->unit_price])->all() : []);
        $catalog = ($products ?? collect())->map(fn ($v) => ['id' => $v->id, 'name' => $v->product->name . ($v->is_plain ? '' : ' - ' . $v->label), 'price' => $v->selling_price, 'stock' => $v->stock_qty])->values();
    @endphp
    <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50/60 p-4" id="lines" data-catalog='@json($catalog)' data-rows='@json($lineRows)'>
        <div class="flex items-center justify-between mb-2">
            <div class="text-sm font-semibold"><i class="fas fa-shirt text-[#42a5dc]"></i> Products on this order (pick size / colour) <span class="font-normal text-slate-500">(optional - takes them off stock)</span></div>
            <button type="button" id="addLine" class="rounded-lg bg-[#42a5dc] hover:bg-[#2c8fc6] text-white px-3 py-1.5 text-sm font-semibold transition"><i class="fas fa-plus"></i> Add product</button>
        </div>
        <div id="lineRows" class="space-y-2"></div>
        <div id="lineEmpty" class="text-sm text-slate-400">No products added. You can still type a package description and item count by hand.</div>
        <div class="mt-3 text-right text-sm">Products total: <strong id="lineTotal">GH₵ 0.00</strong></div>
    </div>
    <script>
    (function () {
        const box = document.getElementById('lines');
        const catalog = JSON.parse(box.dataset.catalog), rows = JSON.parse(box.dataset.rows || '[]');
        const wrap = document.getElementById('lineRows'), empty = document.getElementById('lineEmpty'), totalEl = document.getElementById('lineTotal');
        const totalInput = document.querySelector('[name=total_amount]'); let totalTouched = totalInput.value && Number(totalInput.value) > 0;
        totalInput.addEventListener('input', () => totalTouched = true);
        const money = n => 'GH₵ ' + n.toFixed(2), cls = 'rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm';
        let n = 0;
        function recalc() {
            let sum = 0, qty = 0;
            wrap.querySelectorAll('[data-line]').forEach(r => { const q = +r.querySelector('.q').value || 0, p = +r.querySelector('.p').value || 0; sum += q * p; qty += q; });
            totalEl.textContent = money(sum); empty.style.display = wrap.children.length ? 'none' : '';
            if (wrap.children.length) { document.querySelector('[name=items_sent]').value = qty; if (!totalTouched) totalInput.value = sum.toFixed(2); }
        }
        function addRow(r) {
            const i = n++, d = document.createElement('div'); d.dataset.line = 1; d.className = 'flex flex-wrap gap-2 items-center';
            d.innerHTML = `<select name="items[${i}][variant_id]" class="${cls} flex-1 min-w-40 sel" required><option value="">Choose product…</option>` +
                catalog.map(c => `<option value="${c.id}" data-price="${c.price}" ${r && r.variant_id == c.id ? 'selected' : ''}>${c.name} - ${c.stock} in stock</option>`).join('') + `</select>` +
                `<input type="number" min="1" name="items[${i}][qty]" value="${r ? r.qty : 1}" class="${cls} w-20 q" title="Quantity" required>` +
                `<input type="number" step="0.01" min="0" name="items[${i}][price]" value="${r ? r.price : ''}" class="${cls} w-28 p" title="Price each" required>` +
                `<button type="button" class="text-red-500 px-2" title="Remove"><i class="fas fa-trash"></i></button>`;
            d.querySelector('.sel').addEventListener('change', e => { const o = e.target.selectedOptions[0]; if (o && o.dataset.price) d.querySelector('.p').value = o.dataset.price; recalc(); });
            d.querySelectorAll('.q,.p').forEach(x => x.addEventListener('input', recalc));
            d.querySelector('button').addEventListener('click', () => { d.remove(); recalc(); });
            wrap.appendChild(d); recalc();
        }
        document.getElementById('addLine').addEventListener('click', () => addRow());
        rows.forEach(addRow);
    })();
    </script>
    <label class="text-sm font-medium md:col-span-2">Notes / exchange reason<textarea name="notes" rows="2" class="{{ $input }}">{{ $v('notes', $delivery->notes) }}</textarea></label>
    <div class="md:col-span-2 flex gap-3">
        <button class="rounded-lg bg-[#42a5dc] hover:bg-[#2c8fc6] text-white px-5 py-2 font-semibold transition">{{ $delivery->exists ? 'Save changes' : 'Create delivery' }}</button>
        <a href="{{ $delivery->exists ? route('manager.deliveries.show', $delivery) : route('manager.deliveries.index') }}" class="px-4 py-2 text-slate-500">Cancel</a>
    </div>
</form>
@endsection
