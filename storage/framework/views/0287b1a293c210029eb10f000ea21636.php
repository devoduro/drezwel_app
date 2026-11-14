<?php $__env->startSection('title', $product->name . ' · Drezwel Go'); ?>
<?php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $input = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#42a5dc]';
?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => ''.e($product->category ?: 'Product').''.e($product->sku ? ' · ' . $product->sku : '').'','title' => $product->name,'subtitle' => 'Selling ' . $m($product->selling_price) . ' · cost ' . $m($product->cost_price) . ' · margin ' . $m($product->margin)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => ''.e($product->category ?: 'Product').''.e($product->sku ? ' · ' . $product->sku : '').'','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->name),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Selling ' . $m($product->selling_price) . ' · cost ' . $m($product->cost_price) . ' · margin ' . $m($product->margin))]); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-cubes"></i> <?php echo e($product->stock_qty); ?> in stock</span>
        <span><i class="fas fa-bell"></i> alert at <?php echo e($product->reorder_level); ?></span>
        <?php if (! ($product->is_active)): ?><span><i class="fas fa-eye-slash"></i> Hidden</span><?php endif; ?>
     <?php $__env->endSlot(); ?>
    <a href="<?php echo e(route('manager.products.index')); ?>"><i class="fas fa-arrow-left"></i> Products</a>
    <a href="<?php echo e(route('manager.products.edit', $product)); ?>" class="primary"><i class="fas fa-pen"></i> Edit</a>
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

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => $product->level === 'out' ? 'red' : ($product->level === 'low' ? 'orange' : 'green'),'icon' => 'fa-cubes','title' => 'In stock','value' => $product->stock_qty,'sub' => $product->level === 'out' ? 'Out of stock' : ($product->level === 'low' ? 'Running low' : 'Healthy')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->level === 'out' ? 'red' : ($product->level === 'low' ? 'orange' : 'green')),'icon' => 'fa-cubes','title' => 'In stock','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->stock_qty),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->level === 'out' ? 'Out of stock' : ($product->level === 'low' ? 'Running low' : 'Healthy'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b)): ?>
<?php $attributes = $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b; ?>
<?php unset($__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b)): ?>
<?php $component = $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b; ?>
<?php unset($__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'blue','icon' => 'fa-bag-shopping','title' => 'Units sold','value' => number_format($sold['units']),'sub' => 'Excluding cancelled']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'blue','icon' => 'fa-bag-shopping','title' => 'Units sold','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($sold['units'])),'sub' => 'Excluding cancelled']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b)): ?>
<?php $attributes = $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b; ?>
<?php unset($__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b)): ?>
<?php $component = $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b; ?>
<?php unset($__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'purple','icon' => 'fa-sack-dollar','title' => 'Sales revenue','value' => $m($sold['revenue']),'sub' => 'From orders']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'purple','icon' => 'fa-sack-dollar','title' => 'Sales revenue','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($sold['revenue'])),'sub' => 'From orders']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b)): ?>
<?php $attributes = $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b; ?>
<?php unset($__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b)): ?>
<?php $component = $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b; ?>
<?php unset($__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'green','icon' => 'fa-chart-line','title' => 'Profit','value' => $m($sold['profit']),'sub' => 'Revenue minus cost']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green','icon' => 'fa-chart-line','title' => 'Profit','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($sold['profit'])),'sub' => 'Revenue minus cost']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b)): ?>
<?php $attributes = $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b; ?>
<?php unset($__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b)): ?>
<?php $component = $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b; ?>
<?php unset($__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b); ?>
<?php endif; ?>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    <section class="bg-white rounded-xl p-5 border-l-4 border-l-[#8cc152] lg:col-span-1 h-fit">
        <h2 class="font-semibold mb-3"><i class="fas fa-boxes-stacked text-[#74a83b]"></i> Update stock</h2>
        <form method="POST" action="<?php echo e(route('manager.products.adjust', $product)); ?>" class="space-y-2" x-data><?php echo csrf_field(); ?>
            <select name="action" id="act" class="<?php echo e($input); ?> w-full" onchange="document.getElementById('costRow').style.display = this.value==='purchase' ? '' : 'none'">
                <?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => [$label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>"><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input type="number" name="qty" min="1" required placeholder="Quantity" class="<?php echo e($input); ?> w-full">
            <div id="costRow"><input type="number" step="0.01" min="0" name="unit_cost" placeholder="Cost per unit (optional, updates cost price)" class="<?php echo e($input); ?> w-full"></div>
            <input name="note" placeholder="Note (supplier, reason…)" class="<?php echo e($input); ?> w-full">
            <button class="w-full rounded-lg bg-[#8cc152] hover:bg-[#74a83b] text-white py-2 font-semibold transition">Save</button>
        </form>
        <form method="POST" action="<?php echo e(route('manager.products.toggle', $product)); ?>" class="mt-4"><?php echo csrf_field(); ?>
            <button class="text-sm text-slate-500 hover:text-slate-800"><i class="fas <?php echo e($product->is_active ? 'fa-eye-slash' : 'fa-eye'); ?>"></i> <?php echo e($product->is_active ? 'Hide from new orders' : 'Show in new orders'); ?></button>
        </form>
    </section>

    <section class="bg-white rounded-xl overflow-hidden lg:col-span-2">
        <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#42a5dc] font-semibold text-sm">Stock history</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">When</th><th>Type</th><th class="text-right">Change</th><th class="text-right">Balance</th><th>Order</th><th>By</th><th class="p-3">Note</th></tr></thead>
            <?php $__empty_1 = true; $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t border-slate-100 hover:bg-sky-50/50">
                    <td class="p-3 whitespace-nowrap"><?php echo e($mv->created_at->format('d M, g:ia')); ?></td>
                    <td><?php echo e(\App\Models\StockMovement::TYPES[$mv->type] ?? $mv->type); ?></td>
                    <td class="text-right font-semibold <?php echo e($mv->qty > 0 ? 'text-green-600' : 'text-red-600'); ?>"><?php echo e($mv->qty > 0 ? '+' : ''); ?><?php echo e($mv->qty); ?></td>
                    <td class="text-right"><?php echo e($mv->balance_after); ?></td>
                    <td><?php if($mv->delivery): ?><a class="text-[#2c8fc6] hover:underline" href="<?php echo e(route('manager.deliveries.show', $mv->delivery)); ?>"><?php echo e($mv->delivery->reference); ?></a><?php else: ?> — <?php endif; ?></td>
                    <td><?php echo e($mv->user?->name); ?></td><td class="p-3 text-slate-500"><?php echo e($mv->note); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="7" class="p-8 text-center text-slate-400">No stock movements yet.</td></tr> <?php endif; ?>
        </table></div>
        <div class="p-3"><?php echo e($movements->links()); ?></div>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/products/show.blade.php ENDPATH**/ ?>