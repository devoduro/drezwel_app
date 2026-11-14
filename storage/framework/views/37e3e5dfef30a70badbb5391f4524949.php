
<?php $__env->startSection('title', ($delivery->exists ? 'Edit' : 'New') . ' delivery · Drezwel Go'); ?>
<?php
    $v = fn ($k, $fallback = null) => old($k, $fallback);
    $input = 'mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#42a5dc]';
?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6 !p-5','eyebrow' => 'Deliveries','title' => $delivery->exists ? 'Edit ' . $delivery->reference : 'New delivery','subtitle' => 'Customers are matched by phone number, so repeat buyers keep one record.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6 !p-5','eyebrow' => 'Deliveries','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($delivery->exists ? 'Edit ' . $delivery->reference : 'New delivery'),'subtitle' => 'Customers are matched by phone number, so repeat buyers keep one record.']); ?>
    <a href="<?php echo e(route('manager.deliveries.index')); ?>"><i class="fas fa-arrow-left"></i> Back</a>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb08eb6e359b0cda244088e0d0db6136c)): ?>
<?php $attributes = $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c; ?>
<?php unset($__attributesOriginalb08eb6e359b0cda244088e0d0db6136c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb08eb6e359b0cda244088e0d0db6136c)): ?>
<?php $component = $__componentOriginalb08eb6e359b0cda244088e0d0db6136c; ?>
<?php unset($__componentOriginalb08eb6e359b0cda244088e0d0db6136c); ?>
<?php endif; ?>
<form method="POST" action="<?php echo e($delivery->exists ? route('manager.deliveries.update', $delivery) : route('manager.deliveries.store')); ?>" class="bg-white rounded-xl shadow-sm p-5 grid md:grid-cols-2 gap-4 max-w-4xl">
    <?php echo csrf_field(); ?> <?php if($delivery->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
    <label class="text-sm font-medium">Customer name<input name="customer_name" required value="<?php echo e($v('customer_name', $delivery->customer?->name ?? request('name'))); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">Customer phone<input name="customer_phone" required inputmode="tel" value="<?php echo e($v('customer_phone', $delivery->customer?->phone ?? request('phone'))); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">Location<input name="location" required value="<?php echo e($v('location', $delivery->location ?? request('location'))); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">Package description (auto-filled from products)<input name="package_description" value="<?php echo e($v('package_description', $delivery->package_description)); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">Delivery mode<select name="delivery_mode" class="<?php echo e($input); ?> bg-white"><?php $__currentLoopData = \App\Models\Delivery::MODES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if($v('delivery_mode', $delivery->delivery_mode) === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
    <label class="text-sm font-medium">Order type<select name="order_type" class="<?php echo e($input); ?> bg-white"><?php $__currentLoopData = \App\Models\Delivery::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if($v('order_type', $delivery->order_type) === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
    <label class="text-sm font-medium">Delivery date<input type="date" name="delivery_date" required value="<?php echo e($v('delivery_date', optional($delivery->delivery_date)->format('Y-m-d'))); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">Preferred time window<select name="time_window" class="<?php echo e($input); ?> bg-white"><option value="">Any time</option><?php $__currentLoopData = \App\Models\Delivery::WINDOWS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php if($v('time_window', $delivery->time_window) === $w): echo 'selected'; endif; ?>><?php echo e($w); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
    <label class="text-sm font-medium">Goods total (GH₵) - price of the items<input type="number" step="0.01" min="0" name="total_amount" required value="<?php echo e($v('total_amount', $delivery->total_amount)); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">Delivery fee (GH₵) - customer pays this on top; rider gets it back<input type="number" step="0.01" min="0" name="delivery_fee" value="<?php echo e($v('delivery_fee', $delivery->delivery_fee)); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">Items sent<input type="number" min="1" name="items_sent" value="<?php echo e($v('items_sent', $delivery->items_sent)); ?>" class="<?php echo e($input); ?>"></label>
    <?php if (! ($delivery->exists)): ?>
        <label class="text-sm font-medium">Assign a rider now (optional)<select name="rider_id" class="<?php echo e($input); ?> bg-white"><option value="">Leave open for riders to pick</option><?php $__currentLoopData = $riders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($r->id); ?>" <?php if($v('rider_id') == $r->id): echo 'selected'; endif; ?>><?php echo e($r->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
    <?php endif; ?>
    <?php
        $lineRows = old('items', isset($delivery) && $delivery->exists ? $delivery->items->map(fn ($i) => ['product_id' => $i->product_id, 'qty' => $i->qty, 'price' => (float) $i->unit_price])->all() : []);
        $catalog = ($products ?? collect())->map(fn ($p) => ['id' => $p->id, 'name' => $p->name . ($p->sku ? " ({$p->sku})" : ''), 'price' => (float) $p->selling_price, 'stock' => $p->stock_qty])->values();
    ?>
    <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50/60 p-4" id="lines" data-catalog='<?php echo json_encode($catalog, 15, 512) ?>' data-rows='<?php echo json_encode($lineRows, 15, 512) ?>'>
        <div class="flex items-center justify-between mb-2">
            <div class="text-sm font-semibold"><i class="fas fa-shirt text-[#42a5dc]"></i> Products on this order <span class="font-normal text-slate-500">(optional - takes them off stock)</span></div>
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
            d.innerHTML = `<select name="items[${i}][product_id]" class="${cls} flex-1 min-w-40 sel" required><option value="">Choose product…</option>` +
                catalog.map(c => `<option value="${c.id}" data-price="${c.price}" ${r && r.product_id == c.id ? 'selected' : ''}>${c.name} - ${c.stock} in stock</option>`).join('') + `</select>` +
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
    <label class="text-sm font-medium md:col-span-2">Notes / exchange reason<textarea name="notes" rows="2" class="<?php echo e($input); ?>"><?php echo e($v('notes', $delivery->notes)); ?></textarea></label>
    <div class="md:col-span-2 flex gap-3">
        <button class="rounded-lg bg-[#42a5dc] hover:bg-[#2c8fc6] text-white px-5 py-2 font-semibold transition"><?php echo e($delivery->exists ? 'Save changes' : 'Create delivery'); ?></button>
        <a href="<?php echo e($delivery->exists ? route('manager.deliveries.show', $delivery) : route('manager.deliveries.index')); ?>" class="px-4 py-2 text-slate-500">Cancel</a>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/deliveries/form.blade.php ENDPATH**/ ?>