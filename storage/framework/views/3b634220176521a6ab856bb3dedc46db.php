<?php $__env->startSection('title', $product->name . ' · ' . \App\Models\Setting::get('business_name')); ?>
<?php $__env->startSection('description', \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 150)); ?>
<?php if($product->primary_image_url): ?><?php $__env->startSection('og_image', $product->primary_image_url); ?><?php endif; ?>
<?php
    $tel = \App\Models\Setting::phoneLink();
    $sizes = $product->variants->pluck('size')->filter()->unique()->values();
    $colors = $product->variants->pluck('color')->filter()->unique()->values();
    [$min, $max] = $product->priceRange();
    $open = \App\Models\Setting::get('store_open') === '1';
    $off = $product->discount_percent;
?>
<?php $__env->startSection('content'); ?>
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 py-3 pb-28 lg:pb-6 space-y-3">
    <nav class="text-[13px] text-jm-grey">
        <a class="hover:text-jm-orange" href="<?php echo e(route('home')); ?>">Home</a> <i class="fas fa-chevron-right mx-1 text-[8px]"></i>
        <a class="hover:text-jm-orange" href="<?php echo e(route('shop.index')); ?>">Shop</a>
        <?php if($product->category): ?> <i class="fas fa-chevron-right mx-1 text-[8px]"></i> <a class="hover:text-jm-orange" href="<?php echo e(route('shop.index', ['category' => $product->category])); ?>"><?php echo e($product->category); ?></a><?php endif; ?>
        <i class="fas fa-chevron-right mx-1 text-[8px]"></i> <span class="text-jm-dark"><?php echo e($product->name); ?></span>
    </nav>

    <div class="grid gap-3 lg:grid-cols-[1fr_270px]">
        
        <section class="grid gap-6 rounded-xl bg-white p-4 sm:p-5 md:grid-cols-[minmax(0,420px)_1fr]">
            <div>
                <div class="relative aspect-square overflow-hidden rounded-lg bg-slate-100">
                    <?php if($product->images->count()): ?>
                        <img id="mainPhoto" src="<?php echo e($product->images->first()->url); ?>" alt="<?php echo e($product->name); ?>" class="h-full w-full object-cover">
                    <?php else: ?>
                        <div class="grid h-full place-items-center text-7xl text-slate-300"><i class="fas fa-shirt"></i></div>
                    <?php endif; ?>
                    <?php if($off): ?><span class="absolute right-0 top-3 rounded-l bg-jm-orange px-2 py-1 text-sm font-bold text-white shadow">-<?php echo e($off); ?>%</span><?php endif; ?>
                </div>
                <?php if($product->images->count() > 1): ?>
                    <div class="no-scrollbar mt-2 flex gap-2 overflow-x-auto">
                        <?php $__currentLoopData = $product->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button type="button" class="thumb h-[68px] w-[68px] shrink-0 overflow-hidden rounded-lg border-2 <?php echo e($loop->first ? 'border-jm-orange' : 'border-slate-200'); ?>" data-src="<?php echo e($img->url); ?>"><img src="<?php echo e($img->url); ?>" alt="" class="h-full w-full object-cover"></button>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="min-w-0">
                <h1 class="text-[20px] sm:text-[22px] font-medium leading-snug text-jm-dark"><?php echo e($product->name); ?></h1>
                <?php if($product->category): ?><div class="mt-1 text-sm"><a class="text-jm-orange hover:underline" href="<?php echo e(route('shop.index', ['category' => $product->category])); ?>">Similar products from <?php echo e($product->category); ?></a></div><?php endif; ?>
                <hr class="my-3 border-slate-200">

                <div class="text-[26px] sm:text-[28px] font-medium leading-tight text-jm-dark" id="price"><?php echo e($min == $max ? 'GH₵ ' . number_format($min, 2) : 'GH₵ ' . number_format($min, 2) . ' – ' . number_format($max, 2)); ?></div>
                <?php if($off): ?>
                    <div class="mt-1 flex items-center gap-2"><span class="text-base text-jm-grey line-through">GH₵ <?php echo e(number_format($product->compare_at_price, 2)); ?></span><span class="rounded bg-[#fdecd5] px-2 py-0.5 text-sm font-bold text-jm-orange">-<?php echo e($off); ?>%</span></div>
                <?php endif; ?>
                <div id="stockNote" class="mt-2 text-sm font-medium"></div>
                <div class="mt-1 text-xs text-jm-grey">Delivery fee, if any, is confirmed when we call you.</div>

                <form method="POST" action="<?php echo e(route('shop.cart.add')); ?>" id="buyForm"><?php echo csrf_field(); ?>
                    <input type="hidden" name="variant_id" id="variantId" value="<?php echo e($product->has_options ? '' : ($product->variants->first()->id ?? '')); ?>">

                    <?php if($sizes->count() || $colors->count()): ?>
                        <hr class="my-4 border-slate-200">
                        <div class="mb-3 text-[12px] font-bold uppercase tracking-wide text-jm-grey">Variation available</div>
                    <?php endif; ?>
                    <?php if($sizes->count()): ?>
                        <div class="mb-4">
                            <div class="mb-1.5 text-sm">Size: <span id="sizeName" class="font-bold"></span></div>
                            <div class="flex flex-wrap gap-2" id="sizeBtns">
                                <?php $__currentLoopData = $sizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><button type="button" data-size="<?php echo e($s); ?>" class="opt-size min-w-12 rounded border-2 border-slate-200 bg-white px-3 py-2 text-sm font-medium transition"><?php echo e($s); ?></button><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if($colors->count()): ?>
                        <div class="mb-4">
                            <div class="mb-1.5 text-sm">Colour: <span id="colorName" class="font-bold"></span></div>
                            <div class="flex flex-wrap gap-2" id="colorBtns">
                                <?php $__currentLoopData = $colors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><button type="button" data-color="<?php echo e($c); ?>" class="opt-color rounded border-2 border-slate-200 bg-white px-3 py-2 text-sm font-medium transition"><?php echo e($c); ?></button><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php $__errorArgs = ['variant_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="mb-2 text-sm text-red-600"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                    
                    <div class="mt-5 flex items-center gap-3 max-lg:fixed max-lg:inset-x-0 max-lg:bottom-0 max-lg:z-30 max-lg:mt-0 max-lg:bg-white max-lg:p-3 max-lg:shadow-[0_-4px_14px_rgba(0,0,0,.15)]">
                        <div class="flex shrink-0 items-center rounded-lg border border-slate-300 bg-white">
                            <button type="button" id="minus" class="h-11 w-10 text-xl text-jm-orange" aria-label="Less">−</button>
                            <input type="number" name="qty" id="qty" value="1" min="1" max="20" class="h-11 w-11 border-0 text-center font-bold focus:outline-none" aria-label="Quantity">
                            <button type="button" id="plus" class="h-11 w-10 text-xl text-jm-orange" aria-label="More">+</button>
                        </div>
                        <button id="addBtn" class="flex h-11 flex-1 items-center justify-center gap-2 rounded-lg bg-jm-orange text-[15px] font-bold uppercase tracking-wide text-white shadow transition hover:bg-[#e07b10] disabled:cursor-not-allowed disabled:opacity-40" disabled><i class="fas fa-cart-shopping"></i> <span id="addLabel">Add to cart</span></button>
                    </div>
                </form>
                <?php if (! ($open)): ?><div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">Online ordering is paused. Please call us to order.</div><?php endif; ?>
            </div>
        </section>

        
        <aside class="space-y-3 text-sm">
            <div class="overflow-hidden rounded-xl bg-white">
                <div class="border-b border-slate-200 px-4 py-3 text-[13px] font-bold uppercase tracking-wide text-jm-dark">Delivery &amp; returns</div>
                <ul class="space-y-4 p-4">
                    <li class="flex gap-3"><i class="fas fa-truck-fast mt-0.5 w-5 text-jm-orange"></i><div><div class="font-medium">Door delivery</div><div class="text-jm-grey">Accra by rider. Other regions by station bus or parcel office.</div></div></li>
                    <li class="flex gap-3"><i class="fas fa-store mt-0.5 w-5 text-jm-orange"></i><div><div class="font-medium">Pick-up</div><div class="text-jm-grey">Collect from us<?php echo e(\App\Models\Setting::get('address') ? ': ' . \App\Models\Setting::get('address') : ''); ?>.</div></div></li>
                    <li class="flex gap-3"><i class="fas fa-hand-holding-dollar mt-0.5 w-5 text-jm-orange"></i><div><div class="font-medium">Pay on delivery</div><div class="text-jm-grey">Cash or MoMo when it arrives.</div></div></li>
                    <li class="flex gap-3"><i class="fas fa-rotate-left mt-0.5 w-5 text-jm-orange"></i><div><div class="font-medium">Returns</div><a class="text-jm-orange hover:underline" href="<?php echo e(route('site.page', 'return-policy')); ?>">Read our return policy</a></div></li>
                </ul>
            </div>
            <?php if($tel): ?>
                <div class="rounded-xl bg-jm-orange p-4 text-white"><i class="fas fa-phone-volume text-xl"></i>
                    <div class="mt-1 font-bold">Prefer to order by phone?</div>
                    <a class="text-lg font-bold hover:underline" href="tel:<?php echo e($tel); ?>"><?php echo e(\App\Models\Setting::get('phone')); ?></a>
                    <div class="text-xs text-white/90">Quote: <?php echo e($product->name); ?></div></div>
            <?php endif; ?>
        </aside>
    </div>

    
    <section class="overflow-hidden rounded-xl bg-white">
        <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Product details</div>
        <div class="grid gap-6 p-4 sm:p-5 md:grid-cols-2">
            <div>
                <?php if($product->description): ?><p class="whitespace-pre-line leading-relaxed text-[#535357]"><?php echo e($product->description); ?></p>
                <?php else: ?><p class="text-jm-grey">Call us for more details about this item.</p><?php endif; ?>
            </div>
            <div>
                <div class="mb-2 text-[13px] font-bold uppercase tracking-wide text-jm-grey">Specifications</div>
                <dl class="divide-y divide-slate-100 rounded-lg bg-slate-50 text-sm">
                    <?php $__currentLoopData = array_filter(['Category' => $product->category, 'SKU' => $product->sku, 'Sizes' => $sizes->implode(', '), 'Colours' => $colors->implode(', ')]); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex gap-4 px-3 py-2"><dt class="w-24 shrink-0 text-jm-grey"><?php echo e($k); ?></dt><dd class="font-medium"><?php echo e($v); ?></dd></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </dl>
            </div>
        </div>
    </section>

    <?php if($related->count()): ?>
        <?php echo $__env->make('shop._row', ['title' => 'You may also like', 'products' => $related, 'url' => $product->category ? route('shop.index', ['category' => $product->category]) : null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>
</div>

<script>
(function () {
    const variants = <?php echo json_encode($variants, 15, 512) ?>, hasOptions = <?php echo json_encode($product->has_options, 15, 512) ?>, open = <?php echo json_encode($open, 15, 512) ?>;
    let size = null, color = null;
    const el = id => document.getElementById(id), qty = el('qty');
    const fmt = n => 'GH₵ ' + Number(n).toFixed(2);

    // photo gallery
    document.querySelectorAll('.thumb').forEach(b => b.addEventListener('click', () => {
        el('mainPhoto').src = b.dataset.src;
        document.querySelectorAll('.thumb').forEach(x => { x.classList.remove('border-jm-orange'); x.classList.add('border-slate-200'); });
        b.classList.remove('border-slate-200'); b.classList.add('border-jm-orange');
    }));

    const find = () => variants.find(v => (v.size || null) === size && (v.color || null) === color);
    const hasSizes = variants.some(v => v.size), hasColors = variants.some(v => v.color);
    // Is any variant with these options in stock?
    const available = (s, c) => variants.some(v => (s === undefined || (v.size || null) === s) && (c === undefined || (v.color || null) === c) && v.stock > 0);
    const chip = 'rounded border-2 px-3 py-2 text-sm font-medium transition ';

    function paint() {
        document.querySelectorAll('.opt-size').forEach(b => {
            const on = b.dataset.size === size, ok = available(b.dataset.size, hasColors ? color ?? undefined : undefined);
            b.className = 'opt-size min-w-12 ' + chip + (on ? 'border-jm-orange bg-[#fdecd5] text-jm-orange' : ok ? 'border-slate-200 bg-white hover:border-jm-orange' : 'border-slate-100 bg-slate-50 text-slate-300 line-through');
        });
        document.querySelectorAll('.opt-color').forEach(b => {
            const on = b.dataset.color === color, ok = available(hasSizes ? size ?? undefined : undefined, b.dataset.color);
            b.className = 'opt-color ' + chip + (on ? 'border-jm-orange bg-[#fdecd5] text-jm-orange' : ok ? 'border-slate-200 bg-white hover:border-jm-orange' : 'border-slate-100 bg-slate-50 text-slate-300 line-through');
        });
        if (el('sizeName')) el('sizeName').textContent = size || '';
        if (el('colorName')) el('colorName').textContent = color || '';

        const v = hasOptions ? ((hasSizes ? size : true) !== null && (hasColors ? color : true) !== null ? find() : null) : variants[0];
        const note = el('stockNote'), btn = el('addBtn');
        if (hasOptions && !v) { el('variantId').value = ''; note.textContent = (hasSizes && !size) ? 'Choose a size' + (hasColors ? ' and colour' : '') : 'Choose a colour'; note.className = 'mt-2 text-sm text-jm-grey'; btn.disabled = true; return; }
        if (!v) { btn.disabled = true; return; }
        el('variantId').value = v.id; el('price').textContent = fmt(v.price); qty.max = Math.min(20, v.stock);
        if (+qty.value > v.stock) qty.value = Math.max(1, v.stock);
        if (v.stock <= 0) { note.textContent = 'Sold out in this option'; note.className = 'mt-2 text-sm font-medium text-jm-red'; btn.disabled = true; }
        else { note.textContent = v.stock <= 5 ? 'Few units left: only ' + v.stock : 'In stock'; note.className = 'mt-2 text-sm font-medium ' + (v.stock <= 5 ? 'text-jm-red' : 'text-green-700'); btn.disabled = !open; }
        el('addLabel').textContent = open ? 'Add to cart' : 'Ordering paused';
    }
    document.querySelectorAll('.opt-size').forEach(b => b.addEventListener('click', () => { size = size === b.dataset.size ? null : b.dataset.size; paint(); }));
    document.querySelectorAll('.opt-color').forEach(b => b.addEventListener('click', () => { color = color === b.dataset.color ? null : b.dataset.color; paint(); }));
    el('minus').addEventListener('click', () => { qty.value = Math.max(1, +qty.value - 1); });
    el('plus').addEventListener('click', () => { qty.value = Math.min(+qty.max || 20, +qty.value + 1); });
    paint();
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.shop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/shop/product.blade.php ENDPATH**/ ?>