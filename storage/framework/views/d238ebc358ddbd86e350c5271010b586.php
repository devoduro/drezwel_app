<?php $__env->startSection('title', 'Products & stock · Drezwel Go'); ?>
<?php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $input = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#42a5dc]';
?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => 'Inventory','title' => 'Products & stock','subtitle' => 'Track every item, see what is running low, and get alerted before it is finished.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => 'Inventory','title' => 'Products & stock','subtitle' => 'Track every item, see what is running low, and get alerted before it is finished.']); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-shirt"></i> <?php echo e($stats['products']); ?> products</span>
        <span><i class="fas fa-cubes"></i> <?php echo e(number_format($stats['units'])); ?> units</span>
        <span><i class="fas fa-coins"></i> <?php echo e($m($stats['cost_value'])); ?> at cost</span>
     <?php $__env->endSlot(); ?>
    <a href="<?php echo e(route('manager.reports')); ?>"><i class="fas fa-chart-pie"></i> Reports</a>
    <a href="<?php echo e(route('manager.products.create')); ?>" class="primary"><i class="fas fa-plus"></i> Add product</a>
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

<?php if($stats['out'] + $stats['low'] > 0): ?>
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 flex flex-wrap items-center justify-between gap-2">
        <span><i class="fas fa-triangle-exclamation"></i> <strong><?php echo e($stats['out']); ?></strong> out of stock and <strong><?php echo e($stats['low']); ?></strong> running low.</span>
        <a class="font-semibold underline" href="<?php echo e(route('manager.products.index', ['level' => $stats['out'] ? 'out' : 'low'])); ?>">Show them</a>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'blue','icon' => 'fa-cubes','title' => 'Units in stock','value' => number_format($stats['units']),'sub' => $stats['products'] . ' active products']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'blue','icon' => 'fa-cubes','title' => 'Units in stock','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($stats['units'])),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['products'] . ' active products')]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'green','icon' => 'fa-sack-dollar','title' => 'Stock value','value' => $m($stats['retail_value']),'sub' => 'Cost ' . $m($stats['cost_value'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green','icon' => 'fa-sack-dollar','title' => 'Stock value','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($stats['retail_value'])),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Cost ' . $m($stats['cost_value']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'orange','icon' => 'fa-battery-quarter','title' => 'Running low','value' => $stats['low'],'sub' => 'At or below reorder level','href' => route('manager.products.index', ['level' => 'low'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'orange','icon' => 'fa-battery-quarter','title' => 'Running low','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['low']),'sub' => 'At or below reorder level','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.products.index', ['level' => 'low']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'red','icon' => 'fa-ban','title' => 'Out of stock','value' => $stats['out'],'sub' => 'Cannot be ordered','href' => route('manager.products.index', ['level' => 'out'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'red','icon' => 'fa-ban','title' => 'Out of stock','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['out']),'sub' => 'Cannot be ordered','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.products.index', ['level' => 'out']))]); ?>
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

<div class="bg-white rounded-xl p-4 mb-5 border-l-4 border-l-[#967adc]">
    <div class="text-sm font-semibold mb-2"><i class="fas fa-bell text-[#967adc]"></i> SMS alerts when stock runs low</div>
    <form method="POST" action="<?php echo e(route('manager.alert-phone')); ?>" class="flex flex-wrap gap-2 items-center text-sm"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <input name="phone" value="<?php echo e($alertPhone); ?>" inputmode="tel" placeholder="Your phone e.g. 0241234567" required class="<?php echo e($input); ?> w-60">
        <button class="rounded-lg bg-[#967adc] hover:bg-[#7d62c4] text-white px-4 py-2 font-semibold transition">Save</button>
        <span class="text-slate-500"><?php echo e($alertPhone ? 'Alerts go to ' . $alertPhone . '.' : 'No phone saved - you will only see on-screen alerts.'); ?></span>
    </form>
</div>

<form method="GET" class="bg-white rounded-xl p-3 grid grid-cols-2 md:grid-cols-5 gap-2 mb-4 text-sm">
    <div class="relative col-span-2">
        <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input name="q" value="<?php echo e(request('q')); ?>" placeholder="Search name or SKU…" class="<?php echo e($input); ?> w-full pl-9">
    </div>
    <select name="category" class="<?php echo e($input); ?>"><option value="">All categories</option><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php if(request('category') === $c): echo 'selected'; endif; ?>><?php echo e($c); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
    <select name="level" class="<?php echo e($input); ?>"><option value="">All stock levels</option>
        <?php $__currentLoopData = ['ok' => 'In stock', 'low' => 'Running low', 'out' => 'Out of stock']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(request('level') === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
    <div class="flex items-center gap-2"><label class="flex items-center gap-1 whitespace-nowrap"><input type="checkbox" name="inactive" value="1" <?php if(request()->boolean('inactive')): echo 'checked'; endif; ?>> Hidden</label>
        <button class="rounded-lg bg-[#42a5dc] hover:bg-[#2c8fc6] text-white px-4 py-2 font-semibold transition flex-1"><i class="fas fa-filter"></i></button></div>
</form>

<div class="bg-white rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#42a5dc] font-semibold text-sm">All products</div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Product</th><th>Category</th><th class="text-right">In stock</th><th class="text-right">Reorder at</th><th class="text-right">Cost</th><th class="text-right">Price</th><th class="text-right">Value</th><th class="p-3">Level</th></tr></thead>
        <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-t border-slate-100 hover:bg-sky-50/50 <?php echo e($p->is_active ? '' : 'opacity-50'); ?>">
                <td class="p-3"><a class="font-semibold text-[#2c8fc6] hover:underline" href="<?php echo e(route('manager.products.show', $p)); ?>"><?php echo e($p->name); ?></a><div class="text-xs text-slate-400"><?php echo e($p->sku ?: 'No SKU'); ?></div></td>
                <td class="text-slate-600"><?php echo e($p->category ?: '—'); ?></td>
                <td class="text-right font-bold <?php echo e($p->level === 'out' ? 'text-red-600' : ($p->level === 'low' ? 'text-[#de7a14]' : '')); ?>"><?php echo e($p->stock_qty); ?></td>
                <td class="text-right text-slate-500"><?php echo e($p->reorder_level); ?></td>
                <td class="text-right text-slate-500"><?php echo e(number_format($p->cost_price, 2)); ?></td>
                <td class="text-right"><?php echo e(number_format($p->selling_price, 2)); ?></td>
                <td class="text-right text-slate-500"><?php echo e(number_format($p->stock_qty * $p->selling_price, 2)); ?></td>
                <td class="p-3"><?php echo $__env->make('manager.products._level', ['p' => $p], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="8" class="p-10 text-center text-slate-400"><i class="fas fa-shirt text-3xl mb-2 block"></i>No products yet. <a class="text-[#2c8fc6] font-semibold" href="<?php echo e(route('manager.products.create')); ?>">Add your first product</a>.</td></tr> <?php endif; ?>
    </table></div>
</div>
<div class="mt-4"><?php echo e($products->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/products/index.blade.php ENDPATH**/ ?>