@extends('layouts.app')
@section('title', ($delivery->exists ? 'Edit' : 'New') . ' delivery · Drezwel Go')
@php
    $v = fn ($k, $fallback = null) => old($k, $fallback);
    $input = 'mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]';
    $original = $original ?? null;
    $isNew = ! $delivery->exists;
@endphp
@section('content')
<x-dashboard-hero class="mb-6 !p-5" eyebrow="{{ $original ? 'Return / exchange' : 'Deliveries' }}" :title="$delivery->exists ? 'Edit ' . $delivery->reference : ($original ? 'Exchange for ' . $original->reference : 'New delivery')" subtitle="{{ $original ? 'Pick the items the customer is bringing back, then the new items they are taking. The balance is worked out for you.' : 'Customers are matched by phone number, so repeat buyers keep one record.' }}">
    <a href="{{ $original ? route('manager.deliveries.show', $original) : route('manager.deliveries.index') }}"><i class="fas fa-arrow-left"></i> Back</a>
</x-dashboard-hero>
<form method="POST" action="{{ $delivery->exists ? route('manager.deliveries.update', $delivery) : route('manager.deliveries.store') }}" class="bg-white rounded-xl shadow-sm p-5 grid md:grid-cols-2 gap-4 max-w-4xl">
    @csrf @if($delivery->exists) @method('PUT') @endif
    @if($errors->any())<div class="md:col-span-2 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    @if($v('exchange_of_id', $delivery->exchange_of_id))<input type="hidden" name="exchange_of_id" value="{{ $v('exchange_of_id', $delivery->exchange_of_id) }}">@endif
    <label class="text-sm font-medium">Customer name<input name="customer_name" required value="{{ $v('customer_name', $delivery->customer?->name ?? request('name')) }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Customer phone<input name="customer_phone" required inputmode="tel" value="{{ $v('customer_phone', $delivery->customer?->phone ?? request('phone')) }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Location<input name="location" required value="{{ $v('location', $delivery->location ?? request('location')) }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Package description (auto-filled from products)<input name="package_description" value="{{ $v('package_description', $delivery->package_description) }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Delivery mode<select name="delivery_mode" class="{{ $input }} bg-white">@foreach(\App\Models\Delivery::MODES as $k => $l)<option value="{{ $k }}" @selected($v('delivery_mode', $delivery->delivery_mode) === $k)>{{ $l }}</option>@endforeach</select></label>
    <label class="text-sm font-medium">Order type<select name="order_type" id="orderType" class="{{ $input }} bg-white">@foreach(\App\Models\Delivery::TYPES as $k => $l)<option value="{{ $k }}" @selected($v('order_type', $delivery->order_type) === $k)>{{ $l }}</option>@endforeach</select></label>
    <label class="text-sm font-medium">Delivery date<input type="date" name="delivery_date" required value="{{ $v('delivery_date', optional($delivery->delivery_date)->format('Y-m-d')) }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Preferred time window<select name="time_window" class="{{ $input }} bg-white"><option value="">Any time</option>@foreach(\App\Models\Delivery::WINDOWS as $w)<option @selected($v('time_window', $delivery->time_window) === $w)>{{ $w }}</option>@endforeach</select></label>
    <label class="text-sm font-medium"><span id="totalLabel">Goods total (GH₵) - price of the items</span><input type="number" step="0.01" min="0" name="total_amount" required value="{{ $v('total_amount', $delivery->total_amount) }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Delivery fee (GH₵) - customer pays this on top; rider gets it back<input type="number" step="0.01" min="0" name="delivery_fee" value="{{ $v('delivery_fee', $delivery->delivery_fee) }}" class="{{ $input }}"></label>
    <div class="md:col-span-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-3 text-sm">
        <div class="mb-2 font-medium"><i class="fas fa-map-location-dot text-[#1c3041]"></i> Fill the delivery fee from the price list <span class="font-normal text-slate-500">(optional)</span></div>
        <div class="grid gap-2 sm:grid-cols-2">
            <select id="feeRegion" class="{{ $input }} mt-0 bg-white"><option value="">Region…</option>@foreach(($feeCatalogue ?? []) as $r)<option value="{{ $r['id'] }}">{{ $r['name'] }}</option>@endforeach</select>
            <select id="feeZone" class="{{ $input }} mt-0 bg-white" disabled><option value="">Town…</option></select>
        </div>
    </div>
    <script type="application/json" id="feeData">@json($feeCatalogue ?? [])</script>
    <script>
    (function () {
        var data = JSON.parse(document.getElementById('feeData').textContent), rs = document.getElementById('feeRegion'), zs = document.getElementById('feeZone');
        var fee = document.querySelector('[name=delivery_fee]'), loc = document.querySelector('[name=location]');
        function put(amount, place) {
            fee.value = Number(amount).toFixed(2);
            if (place && loc.value.toLowerCase().indexOf(place.toLowerCase()) === -1) loc.value = (loc.value ? loc.value + ', ' : '') + place;
        }
        rs.addEventListener('change', function () {
            var r = data.find(function (x) { return String(x.id) === rs.value; });
            zs.innerHTML = '<option value="">Town…</option>'; zs.disabled = !r;
            if (!r) return;
            zs.innerHTML += r.zones.map(function (z) { return '<option value="' + z.id + '">' + z.name + ' - GH₵ ' + Number(z.fee).toFixed(2) + '</option>'; }).join('') + '<option value="other">Another town in ' + r.name + ' - GH₵ ' + Number(r.fee).toFixed(2) + '</option>';
        });
        zs.addEventListener('change', function () {
            var r = data.find(function (x) { return String(x.id) === rs.value; });
            if (!r || !zs.value) return;
            var z = r.zones.find(function (x) { return String(x.id) === zs.value; });
            if (z) put(z.fee, z.name + ', ' + r.name); else put(r.fee, r.name);
        });
    })();
    </script>
    <label class="text-sm font-medium">Items sent<input type="number" min="0" name="items_sent" value="{{ $v('items_sent', $delivery->items_sent) }}" class="{{ $input }}"></label>
    @unless($delivery->exists)
        <label class="text-sm font-medium">Assign a rider now (optional)<select name="rider_id" class="{{ $input }} bg-white"><option value="">Leave open for riders to pick</option>@foreach($riders as $r)<option value="{{ $r->id }}" @selected($v('rider_id') == $r->id)>{{ $r->name }}</option>@endforeach</select></label>
    @endunless
    @php
        $lineRows = old('items', $delivery->exists ? $delivery->sent_items->map(fn ($i) => ['variant_id' => $i->variant_id, 'qty' => $i->qty, 'price' => (float) $i->unit_price])->all() : []);
        $retRows = old('returns', $returnRows ?? []);
        $catalog = ($products ?? collect())->map(fn ($v) => ['id' => $v->id, 'name' => $v->product->name . ($v->is_plain ? '' : ' - ' . $v->label), 'price' => $v->selling_price, 'stock' => $v->stock_qty])->values();
    @endphp
    <script type="application/json" id="catalogData">@json($catalog)</script>

    {{-- Items the customer is bringing back (exchange / sale return only) --}}
    <div class="md:col-span-2 rounded-xl border border-amber-200 bg-amber-50/60 p-4" id="returnsBox" data-rows='@json($retRows)'>
        <div class="flex items-center justify-between mb-2">
            <div class="text-sm font-semibold"><i class="fas fa-rotate-left text-amber-700"></i> Items the customer is returning <span class="font-normal text-slate-500">(credited at the price below and put back in stock)</span></div>
            <button type="button" class="addLine rounded-lg bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 text-sm font-semibold transition"><i class="fas fa-plus"></i> Add returned item</button>
        </div>
        <div class="rows space-y-2"></div>
        <div class="empty text-sm text-slate-400">Nothing returned yet.</div>
        <div class="mt-3 text-right text-sm">Returned credit: <strong class="sum">GH₵ 0.00</strong></div>
    </div>

    <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50/60 p-4" id="linesBox" data-rows='@json($lineRows)'>
        <div class="flex items-center justify-between mb-2">
            <div class="text-sm font-semibold"><i class="fas fa-shirt text-[#1c3041]"></i> <span id="newTitle">Products on this order (pick size / colour)</span> <span class="font-normal text-slate-500">(optional - takes them off stock)</span></div>
            <button type="button" class="addLine rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-3 py-1.5 text-sm font-semibold transition"><i class="fas fa-plus"></i> Add product</button>
        </div>
        <div class="rows space-y-2"></div>
        <div class="empty text-sm text-slate-400">No products added. You can still type a package description and item count by hand.</div>
        <div class="mt-3 text-right text-sm">Products total: <strong class="sum">GH₵ 0.00</strong></div>
    </div>

    {{-- Exchange balance: new items minus returned items --}}
    <div class="md:col-span-2 rounded-xl border-2 border-[#1c3041] bg-[#f3f6f9] p-4 text-sm" id="balanceBox">
        <div class="flex justify-between"><span>New items</span><strong id="bNew">GH₵ 0.00</strong></div>
        <div class="flex justify-between"><span>Returned items</span><strong id="bRet">- GH₵ 0.00</strong></div>
        <div class="mt-2 flex justify-between border-t border-slate-300 pt-2 text-base"><span id="bLabel" class="font-bold">Customer pays</span><strong id="bNet" class="text-lg">GH₵ 0.00</strong></div>
        @if($isNew)
            <label class="mt-3 flex flex-wrap items-center gap-3 border-t border-slate-300 pt-3">
                <span class="flex items-center gap-2 font-medium"><input type="checkbox" name="settle_now" value="1" class="accent-[#1c3041]" @checked(old('settle_now', (bool) $original))> Customer is at the counter: complete it now and record the money</span>
                <select name="settle_method" class="rounded-lg border border-slate-200 bg-white px-2 py-1.5">@foreach(\App\Models\Payment::METHODS as $k => $l)<option value="{{ $k }}" @selected(old('settle_method') === $k)>{{ $l }}</option>@endforeach</select>
            </label>
        @endif
    </div>

    <script>
    (function () {
        const catalog = JSON.parse(document.getElementById('catalogData').textContent);
        const type = document.getElementById('orderType'), totalInput = document.querySelector('[name=total_amount]'), sentInput = document.querySelector('[name=items_sent]');
        const money = n => 'GH₵ ' + Math.abs(n).toFixed(2), cls = 'rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm';
        let totalTouched = totalInput.value !== '' && Number(totalInput.value) !== 0;
        totalInput.addEventListener('input', () => totalTouched = true);

        function makeList(boxId, field, opts) {
            const box = document.getElementById(boxId), wrap = box.querySelector('.rows'), empty = box.querySelector('.empty'), sumEl = box.querySelector('.sum');
            let n = 0;
            const api = {
                box, total() { let s = 0, q = 0; wrap.querySelectorAll('[data-line]').forEach(r => { const a = +r.querySelector('.q').value || 0, p = +r.querySelector('.p').value || 0; s += a * p; q += a; }); return {s, q}; },
                count() { return wrap.children.length; },
            };
            function recalc() { const t = api.total(); sumEl.textContent = money(t.s); empty.style.display = wrap.children.length ? 'none' : ''; refresh(); }
            function addRow(r) {
                const i = n++, d = document.createElement('div'); d.dataset.line = 1; d.className = 'flex flex-wrap gap-2 items-center';
                d.innerHTML = `<select name="${field}[${i}][variant_id]" class="${cls} flex-1 min-w-40 sel" required><option value="">Choose product…</option>` +
                    catalog.map(c => `<option value="${c.id}" data-price="${c.price}" ${r && r.variant_id == c.id ? 'selected' : ''}>${c.name}${opts.showStock ? ' - ' + c.stock + ' in stock' : ''}</option>`).join('') + `</select>` +
                    `<input type="number" min="1" name="${field}[${i}][qty]" value="${r ? r.qty : 1}" class="${cls} w-20 q" title="Quantity" required>` +
                    `<input type="number" step="0.01" min="0" name="${field}[${i}][price]" value="${r ? r.price : ''}" class="${cls} w-28 p" title="${opts.priceTitle}" required>` +
                    `<button type="button" class="text-red-500 px-2" title="Remove"><i class="fas fa-trash"></i></button>`;
                d.querySelector('.sel').addEventListener('change', e => { const o = e.target.selectedOptions[0]; if (o && o.dataset.price) d.querySelector('.p').value = o.dataset.price; recalc(); });
                d.querySelectorAll('.q,.p').forEach(x => x.addEventListener('input', recalc));
                d.querySelector('button').addEventListener('click', () => { d.remove(); recalc(); });
                wrap.appendChild(d); recalc();
            }
            box.querySelector('.addLine').addEventListener('click', () => addRow());
            JSON.parse(box.dataset.rows || '[]').forEach(addRow);
            empty.style.display = wrap.children.length ? 'none' : '';
            return api;
        }

        let sent, back;
        function refresh() {
            if (!sent || !back) return;
            const isEx = type.value === 'exchange', a = sent.total(), b = isEx ? back.total() : {s: 0, q: 0}, net = a.s - b.s;
            document.getElementById('returnsBox').style.display = isEx ? '' : 'none';
            document.getElementById('balanceBox').style.display = isEx ? '' : 'none';
            document.getElementById('newTitle').textContent = isEx ? 'New items the customer is taking (pick size / colour)' : 'Products on this order (pick size / colour)';
            document.getElementById('totalLabel').textContent = isEx ? 'Exchange balance (GH₵): new items minus returned items' : 'Goods total (GH₵) - price of the items';
            totalInput.min = isEx ? '' : '0';
            document.getElementById('bNew').textContent = money(a.s); document.getElementById('bRet').textContent = '- ' + money(b.s);
            document.getElementById('bLabel').textContent = net < 0 ? 'Refund to customer' : net === 0 ? 'Even swap' : 'Customer pays';
            document.getElementById('bNet').textContent = money(net);
            if (sent.count() || (isEx && back.count())) { sentInput.value = a.q; if (!totalTouched) totalInput.value = net.toFixed(2); }
        }
        sent = makeList('linesBox', 'items', {showStock: true, priceTitle: 'Price each'});
        back = makeList('returnsBox', 'returns', {showStock: false, priceTitle: 'Credit each'});
        type.addEventListener('change', refresh);
        refresh();
    })();
    </script>
    <label class="text-sm font-medium md:col-span-2">Notes / exchange reason<textarea name="notes" rows="2" class="{{ $input }}">{{ $v('notes', $delivery->notes) }}</textarea></label>
    <div class="md:col-span-2 flex gap-3">
        <button class="rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-5 py-2 font-semibold transition">{{ $delivery->exists ? 'Save changes' : 'Create delivery' }}</button>
        <a href="{{ $delivery->exists ? route('manager.deliveries.show', $delivery) : route('manager.deliveries.index') }}" class="px-4 py-2 text-slate-500">Cancel</a>
    </div>
</form>
@endsection
