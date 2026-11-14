
<?php $__env->startSection('title', 'POS · Drezwel Go'); ?>
<?php $field = 'w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]'; ?>
<?php $__env->startSection('content'); ?>
<div class="mb-4 flex flex-wrap items-center justify-between gap-2">
    <div><h1 class="text-xl font-bold">Point of sale</h1><p class="text-sm text-slate-500">Sell to a customer in the shop, or take an order over the phone.</p></div>
    <a href="<?php echo e(route('manager.products.index')); ?>" class="text-sm text-[#1c3041] font-semibold"><i class="fas fa-boxes-stacked"></i> Products</a>
</div>

<div class="grid gap-4 xl:grid-cols-3" id="pos">
    
    <section class="xl:col-span-2">
        <div class="mb-3 flex flex-wrap gap-2">
            <div class="relative flex-1 min-w-48"><i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input id="search" placeholder="Search product or SKU…" class="<?php echo e($field); ?> pl-9" autofocus></div>
            <select id="cat" class="<?php echo e($field); ?> !w-auto"><option value="">All categories</option><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option><?php echo e($c); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
        </div>
        <div id="grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3"></div>
    </section>

    
    <form method="POST" action="<?php echo e(route('manager.pos.store')); ?>" id="saleForm" class="h-fit rounded-xl bg-white p-4 xl:sticky xl:top-4 space-y-3"><?php echo csrf_field(); ?>
        <div class="grid grid-cols-2 gap-1 rounded-lg bg-slate-100 p-1 text-sm font-semibold">
            <label class="cursor-pointer"><input type="radio" name="type" value="counter" class="peer sr-only" checked><span class="block rounded-md py-2 text-center peer-checked:bg-white peer-checked:shadow"><i class="fas fa-store"></i> Counter sale</span></label>
            <label class="cursor-pointer"><input type="radio" name="type" value="delivery" class="peer sr-only"><span class="block rounded-md py-2 text-center peer-checked:bg-white peer-checked:shadow"><i class="fas fa-phone"></i> Phone order</span></label>
        </div>

        <div id="lines" class="space-y-2 max-h-80 overflow-y-auto"></div>
        <div id="empty" class="rounded-lg border-2 border-dashed p-6 text-center text-sm text-slate-400">Tap a product to add it</div>

        <div class="border-t pt-3 space-y-1 text-sm">
            <div class="flex justify-between"><span>Items</span><span id="subtotal">GH₵ 0.00</span></div>
            <div id="feeRow" class="hidden items-center justify-between gap-2"><span>Delivery fee</span><input type="number" step="0.01" min="0" name="delivery_fee" id="fee" value="<?php echo e($defaultFee); ?>" class="<?php echo e($field); ?> !w-28 text-right"></div>
            <div class="flex justify-between text-lg font-bold"><span>Total</span><span id="total">GH₵ 0.00</span></div>
        </div>

        <div class="grid grid-cols-2 gap-2">
            <input name="name" placeholder="Customer name" class="<?php echo e($field); ?>" id="cname">
            <input name="phone" placeholder="Phone" inputmode="tel" class="<?php echo e($field); ?>" id="cphone">
        </div>
        <input name="location" id="loc" placeholder="Delivery location" class="<?php echo e($field); ?> hidden">

        <div id="payBox" class="space-y-2">
            <div class="grid grid-cols-2 gap-2">
                <select name="method" class="<?php echo e($field); ?>"><?php $__currentLoopData = \App\Models\Payment::METHODS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>"><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                <input type="number" step="0.01" min="0" name="received" id="received" placeholder="Amount received" class="<?php echo e($field); ?>">
            </div>
            <div id="change" class="text-sm text-slate-500"></div>
        </div>
        <input name="notes" placeholder="Note (optional)" class="<?php echo e($field); ?>">

        <?php if($errors->any()): ?><div class="rounded-lg bg-red-50 border border-red-200 p-2 text-sm text-red-700"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($e); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php endif; ?>
        <button id="submitBtn" class="w-full rounded-xl bg-[#8cc152] hover:bg-[#74a83b] text-white py-3 font-semibold transition disabled:opacity-40" disabled>Complete sale &amp; print</button>
    </form>
</div>


<div id="modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-sm rounded-2xl bg-white p-5">
        <div class="flex justify-between"><h3 class="font-semibold" id="mTitle"></h3><button type="button" id="mClose" class="text-slate-400">✕</button></div>
        <div id="mList" class="mt-3 space-y-2"></div>
    </div>
</div>

<script>
(function () {
    const catalog = <?php echo json_encode($catalog, 15, 512) ?>, cart = new Map(); // variantId -> {id, name, label, price, qty, stock}
    const $ = id => document.getElementById(id), money = n => 'GH₵ ' + (Math.round(n * 100) / 100).toFixed(2);
    const form = $('saleForm'), grid = $('grid');
    let type = 'counter';

    function stock(p) { return p.variants.reduce((s, v) => s + v.stock, 0); }
    function renderGrid() {
        const q = $('search').value.trim().toLowerCase(), c = $('cat').value;
        grid.innerHTML = '';
        catalog.filter(p => (!c || p.category === c) && (!q || (p.name + ' ' + (p.sku || '') + ' ' + (p.category || '')).toLowerCase().includes(q))).forEach(p => {
            const out = stock(p) <= 0, prices = p.variants.map(v => v.price), min = Math.min(...prices);
            const b = document.createElement('button'); b.type = 'button'; b.disabled = out;
            b.className = 'overflow-hidden rounded-xl border bg-white text-left transition hover:shadow-md hover:border-[#1c3041] disabled:opacity-40 disabled:cursor-not-allowed';
            b.innerHTML = `<div class="aspect-square bg-slate-100">${p.image ? `<img src="${p.image}" class="h-full w-full object-cover" loading="lazy">` : '<div class="grid h-full place-items-center text-4xl text-slate-300"><i class="fas fa-shirt"></i></div>'}</div>
                <div class="p-2"><div class="line-clamp-2 text-sm font-medium leading-tight">${p.name}</div>
                <div class="mt-1 flex items-center justify-between text-xs"><span class="font-bold text-[#1c3041]">${prices.some(x => x !== min) ? 'From ' : ''}${money(min)}</span><span class="${out ? 'text-red-600' : 'text-slate-400'}">${out ? 'Sold out' : stock(p) + ' left'}</span></div></div>`;
            b.addEventListener('click', () => pick(p));
            grid.appendChild(b);
        });
    }

    function pick(p) {
        const avail = p.variants.filter(v => v.stock > 0);
        if (avail.length === 1 && avail[0].plain) return add(p, avail[0]);
        $('mTitle').textContent = p.name; const list = $('mList'); list.innerHTML = '';
        p.variants.forEach(v => {
            const b = document.createElement('button'); b.type = 'button'; b.disabled = v.stock <= 0;
            b.className = 'flex w-full items-center justify-between rounded-lg border px-3 py-2.5 text-sm hover:border-[#1c3041] disabled:opacity-40';
            b.innerHTML = `<span class="font-medium">${v.label}</span><span><strong>${money(v.price)}</strong> <span class="text-xs text-slate-400">· ${v.stock} left</span></span>`;
            b.addEventListener('click', () => { add(p, v); close(); });
            list.appendChild(b);
        });
        $('modal').classList.remove('hidden'); $('modal').classList.add('flex');
    }
    function close() { $('modal').classList.add('hidden'); $('modal').classList.remove('flex'); }
    $('mClose').addEventListener('click', close); $('modal').addEventListener('click', e => { if (e.target.id === 'modal') close(); });

    function add(p, v) {
        const cur = cart.get(v.id) || {id: v.id, name: p.name, label: v.plain ? '' : v.label, price: v.price, qty: 0, stock: v.stock};
        if (cur.qty >= v.stock) return;
        cur.qty++; cart.set(v.id, cur); renderCart();
    }

    function totals() { const sub = [...cart.values()].reduce((s, l) => s + l.qty * l.price, 0); return {sub, total: sub + (type === 'delivery' ? +$('fee').value || 0 : 0)}; }
    function renderCart() {
        const box = $('lines'); box.innerHTML = ''; let i = 0;
        cart.forEach(l => {
            const d = document.createElement('div'); d.className = 'rounded-lg border p-2 text-sm';
            d.innerHTML = `<input type="hidden" name="items[${i}][variant_id]" value="${l.id}">
                <div class="flex justify-between gap-2"><div class="min-w-0"><div class="font-medium leading-tight">${l.name}</div>${l.label ? `<div class="text-xs text-slate-500">${l.label}</div>` : ''}</div><button type="button" class="rm text-slate-400 hover:text-red-500"><i class="fas fa-trash"></i></button></div>
                <div class="mt-2 flex items-center gap-2">
                    <div class="flex items-center rounded-lg border"><button type="button" class="dec h-8 w-8">−</button><input name="items[${i}][qty]" value="${l.qty}" readonly class="h-8 w-10 border-0 text-center font-semibold"><button type="button" class="inc h-8 w-8">+</button></div>
                    <span class="text-slate-400">×</span><input type="number" step="0.01" min="0" name="items[${i}][price]" value="${l.price}" class="price h-8 w-24 rounded-lg border px-2 text-right" title="Price each (you can change it)">
                    <span class="ml-auto font-semibold">${money(l.qty * l.price)}</span></div>`;
            d.querySelector('.rm').addEventListener('click', () => { cart.delete(l.id); renderCart(); });
            d.querySelector('.dec').addEventListener('click', () => { l.qty > 1 ? l.qty-- : cart.delete(l.id); renderCart(); });
            d.querySelector('.inc').addEventListener('click', () => { if (l.qty < l.stock) { l.qty++; renderCart(); } });
            d.querySelector('.price').addEventListener('change', e => { l.price = Math.max(0, +e.target.value || 0); renderCart(); });
            box.appendChild(d); i++;
        });
        $('empty').style.display = cart.size ? 'none' : ''; $('submitBtn').disabled = !cart.size;
        const t = totals(); $('subtotal').textContent = money(t.sub); $('total').textContent = money(t.total);
        if (type === 'counter' && !$('received').dataset.touched) $('received').value = cart.size ? t.total.toFixed(2) : '';
        change();
    }
    function change() {
        const r = +$('received').value || 0, t = totals().total;
        $('change').textContent = type !== 'counter' || !r ? '' : r >= t ? 'Change to give: ' + money(r - t) : 'Customer still owes ' + money(t - r);
    }
    $('received').addEventListener('input', e => { e.target.dataset.touched = 1; change(); });
    $('fee').addEventListener('input', renderCart);

    document.querySelectorAll('input[name=type]').forEach(r => r.addEventListener('change', () => {
        type = r.value; const del = type === 'delivery';
        $('feeRow').classList.toggle('hidden', !del); $('feeRow').classList.toggle('flex', del);
        $('loc').classList.toggle('hidden', !del); $('payBox').classList.toggle('hidden', del);
        $('cname').required = $('cphone').required = $('loc').required = del;
        $('cname').placeholder = del ? 'Customer name *' : 'Customer name (optional)'; $('cphone').placeholder = del ? 'Phone *' : 'Phone (optional)';
        $('submitBtn').innerHTML = del ? 'Create order for delivery' : 'Complete sale &amp; print';
        renderCart();
    }));
    $('search').addEventListener('input', renderGrid); $('cat').addEventListener('change', renderGrid);
    renderGrid(); renderCart();
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/pos.blade.php ENDPATH**/ ?>