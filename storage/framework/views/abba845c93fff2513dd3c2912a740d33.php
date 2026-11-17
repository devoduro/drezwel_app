
<?php $__env->startSection('title', ($product->exists ? 'Edit' : 'New') . ' product · Drezwel Go'); ?>
<?php
    $v = fn ($k, $fallback = null) => old($k, $fallback);
    $input = 'mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]';
    $cell = 'rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm w-full';
?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6 !p-5','eyebrow' => 'Products','title' => $product->exists ? 'Edit ' . $product->name : 'Add product','subtitle' => 'Photos, sizes and colours, and prices in cedis. Products marked for the online store appear on your website.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6 !p-5','eyebrow' => 'Products','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->exists ? 'Edit ' . $product->name : 'Add product'),'subtitle' => 'Photos, sizes and colours, and prices in cedis. Products marked for the online store appear on your website.']); ?>
    <a href="<?php echo e($product->exists ? route('manager.products.show', $product) : route('manager.products.index')); ?>"><i class="fas fa-arrow-left"></i> Back</a>
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

<form method="POST" enctype="multipart/form-data" action="<?php echo e($product->exists ? route('manager.products.update', $product) : route('manager.products.store')); ?>" class="space-y-5 max-w-4xl">
    <?php echo csrf_field(); ?> <?php if($product->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

    <section class="bg-white rounded-xl p-5 grid md:grid-cols-2 gap-4">
        <label class="text-sm font-medium md:col-span-2">Product name<input name="name" required value="<?php echo e($v('name', $product->name)); ?>" class="<?php echo e($input); ?>" placeholder="e.g. Baggy jeans"></label>
        <label class="text-sm font-medium">Category<input name="category" list="cats" value="<?php echo e($v('category', $product->category)); ?>" class="<?php echo e($input); ?>" placeholder="Jeans, Sneakers, Dresses…"><datalist id="cats"><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c); ?>"><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></datalist></label>
        <label class="text-sm font-medium">SKU / code (optional)<input name="sku" value="<?php echo e($v('sku', $product->sku)); ?>" class="<?php echo e($input); ?>"></label>
        <label class="text-sm font-medium">Cost price (GH₵) - what you pay<input type="number" step="0.01" min="0" name="cost_price" required value="<?php echo e($v('cost_price', $product->cost_price ?? 0)); ?>" class="<?php echo e($input); ?>"></label>
        <label class="text-sm font-medium">Selling price (GH₵)<input type="number" step="0.01" min="0" name="selling_price" required value="<?php echo e($v('selling_price', $product->selling_price ?? 0)); ?>" class="<?php echo e($input); ?>"></label>
        <label class="text-sm font-medium">Old price (GH₵, optional)<input type="number" step="0.01" min="0" name="compare_at_price" value="<?php echo e($v('compare_at_price', $product->compare_at_price)); ?>" class="<?php echo e($input); ?>"><span class="text-xs font-normal text-slate-500">Higher than the selling price? The store shows it struck through with a -% badge. This does not add the product to Today's deals: use the tick box below for that.</span></label>
    <label class="text-sm font-medium">Alert me when total stock falls to<input type="number" min="0" name="reorder_level" required value="<?php echo e($v('reorder_level', $product->reorder_level)); ?>" class="<?php echo e($input); ?>"></label>
        <label class="flex items-center gap-2 text-sm font-medium self-end pb-2"><input type="checkbox" name="show_in_store" value="1" <?php if($v('show_in_store', $product->show_in_store ?? true)): echo 'checked'; endif; ?>> Show on the online store</label>
        <label class="flex items-center gap-2 text-sm font-medium md:col-span-2 rounded-lg bg-red-50 px-3 py-2 text-red-800"><input type="checkbox" name="is_deal" value="1" class="accent-red-600" <?php if($v('is_deal', $product->is_deal ?? false)): echo 'checked'; endif; ?>> <i class="fas fa-bolt"></i> Feature in Today's deals (hot deals row on the store). The price does not have to be reduced.</label>
        <label class="text-sm font-medium md:col-span-2">Description (shown to customers)<textarea name="description" rows="3" class="<?php echo e($input); ?>" placeholder="Fabric, fit, care…"><?php echo e($v('description', $product->description)); ?></textarea></label>
        <label class="text-sm font-medium md:col-span-2">Private notes<textarea name="notes" rows="2" class="<?php echo e($input); ?>"><?php echo e($v('notes', $product->notes)); ?></textarea></label>
    </section>

    <section class="bg-white rounded-xl p-5 border-l-4 border-l-[#967adc]">
        <h2 class="font-semibold mb-1"><i class="fas fa-images text-[#967adc]"></i> Photos</h2>
        <p class="text-xs text-slate-500 mb-3">JPG, PNG or WebP, up to 6 MB each, up to <?php echo e(\App\Services\ProductImageService::MAX_PER_PRODUCT); ?> photos. The first one is the main photo.</p>
        <?php if($product->exists && $product->images->count()): ?>
            <div class="flex flex-wrap gap-3 mb-3">
                <?php $__currentLoopData = $product->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="w-24 text-center">
                        <img src="<?php echo e($img->url); ?>" alt="" class="h-24 w-24 rounded-lg object-cover border <?php echo e($loop->first ? 'ring-2 ring-[#1c3041]' : ''); ?>">
                        <div class="mt-1 text-[11px] text-slate-500"><?php echo e($loop->first ? 'Main photo' : ''); ?></div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <p class="text-xs text-slate-500 mb-3">To remove a photo or change the main one, use the product page.</p>
        <?php endif; ?>
        <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-[#967adc] file:px-4 file:py-2 file:text-white file:font-semibold">
        <div id="preview" class="mt-3 flex flex-wrap gap-2"></div>
    </section>

    <section class="bg-white rounded-xl p-5 border-l-4 border-l-[#8cc152]" id="variants">
        <h2 class="font-semibold mb-1"><i class="fas fa-palette text-[#74a83b]"></i> Sizes &amp; colours</h2>
        <p class="text-xs text-slate-500 mb-3">Each size/colour combination has its own stock. Leave this empty for a product with no options.</p>

        <?php if($product->exists && $product->variants->count()): ?>
            <div class="overflow-x-auto mb-4"><table class="w-full text-sm">
                <thead class="text-left text-xs text-slate-500"><tr><th class="pb-1">Size</th><th>Colour</th><th>SKU</th><th>Own price (GH₵)</th><th>In stock</th><th>Active</th></tr></thead>
                <?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $var): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="align-middle">
                        <td class="pr-2 py-1"><input name="existing[<?php echo e($var->id); ?>][size]" value="<?php echo e($var->size); ?>" class="<?php echo e($cell); ?>" placeholder="—"></td>
                        <td class="pr-2"><input name="existing[<?php echo e($var->id); ?>][color]" value="<?php echo e($var->color); ?>" class="<?php echo e($cell); ?>" placeholder="—"></td>
                        <td class="pr-2"><input name="existing[<?php echo e($var->id); ?>][sku]" value="<?php echo e($var->sku); ?>" class="<?php echo e($cell); ?>"></td>
                        <td class="pr-2"><input type="number" step="0.01" min="0" name="existing[<?php echo e($var->id); ?>][price]" value="<?php echo e($var->price); ?>" class="<?php echo e($cell); ?>" placeholder="same as product"></td>
                        <td class="pr-2 font-semibold <?php echo e($var->stock_qty <= 0 ? 'text-red-600' : ''); ?>"><?php echo e($var->stock_qty); ?></td>
                        <td><input type="checkbox" name="existing[<?php echo e($var->id); ?>][is_active]" value="1" <?php if($var->is_active): echo 'checked'; endif; ?>></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </table></div>
            <p class="text-xs text-slate-500 mb-3">Change how many are in stock with "Update stock" on the product page.</p>
        <?php endif; ?>

        <div class="rounded-lg bg-slate-50 p-3 mb-3">
            <div class="text-xs font-semibold text-slate-600 mb-2">Quick add: type sizes and colours separated by commas</div>
            <div class="grid sm:grid-cols-4 gap-2">
                <input id="genSizes" placeholder="Sizes: S, M, L, XL" class="<?php echo e($cell); ?>">
                <input id="genColors" placeholder="Colours: Black, Blue" class="<?php echo e($cell); ?>">
                <input id="genStock" type="number" min="0" placeholder="Stock each" class="<?php echo e($cell); ?>">
                <button type="button" id="genBtn" class="rounded-lg bg-[#8cc152] hover:bg-[#74a83b] text-white px-3 py-1.5 text-sm font-semibold transition">Add combinations</button>
            </div>
        </div>

        <div id="newRows" class="space-y-2"></div>
        <button type="button" id="addRow" class="mt-2 text-sm text-[#1c3041] font-semibold"><i class="fas fa-plus"></i> Add one size/colour</button>

        <?php if (! ($product->exists)): ?>
            <label class="block mt-4 text-sm font-medium">If there are no sizes or colours, opening stock (units on hand now)
                <input type="number" min="0" name="opening_stock" value="<?php echo e($v('opening_stock', 0)); ?>" class="<?php echo e($input); ?> max-w-xs"></label>
        <?php endif; ?>
    </section>

    <div class="flex gap-3">
        <button class="rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-6 py-2.5 font-semibold transition"><?php echo e($product->exists ? 'Save changes' : 'Add product'); ?></button>
        <a href="<?php echo e(route('manager.products.index')); ?>" class="px-4 py-2.5 text-slate-500">Cancel</a>
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
    <?php if(old('new')): ?>
        <?php $__currentLoopData = old('new'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> add(<?php echo json_encode($r['size'] ?? '', 15, 512) ?>, <?php echo json_encode($r['color'] ?? '', 15, 512) ?>, <?php echo json_encode($r['stock'] ?? '', 15, 512) ?>); <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/products/form.blade.php ENDPATH**/ ?>