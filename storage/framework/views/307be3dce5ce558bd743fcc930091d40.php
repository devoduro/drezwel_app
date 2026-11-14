
<?php $__env->startSection('title', 'Customers · Drezwel Go'); ?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => 'Your buyers','title' => 'Customers','subtitle' => 'Customers are created automatically when you add a delivery for a new phone number.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => 'Your buyers','title' => 'Customers','subtitle' => 'Customers are created automatically when you add a delivery for a new phone number.']); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-users"></i> <?php echo e($totals['customers']); ?> customers</span>
        <span><i class="fas fa-triangle-exclamation"></i> <?php echo e($totals['debtors']); ?> owing</span>
     <?php $__env->endSlot(); ?>
    <a href="<?php echo e(route('manager.deliveries.create')); ?>" class="primary"><i class="fas fa-plus"></i> New delivery</a>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'blue','icon' => 'fa-users','title' => 'Customers','value' => $totals['customers'],'sub' => 'Registered accounts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'blue','icon' => 'fa-users','title' => 'Customers','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totals['customers']),'sub' => 'Registered accounts']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'green','icon' => 'fa-sack-dollar','title' => 'Lifetime spend','value' => 'GH₵ ' . number_format($totals['spend'], 2),'sub' => 'All orders']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green','icon' => 'fa-sack-dollar','title' => 'Lifetime spend','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('GH₵ ' . number_format($totals['spend'], 2)),'sub' => 'All orders']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'red','icon' => 'fa-hand-holding-dollar','title' => 'Owed','value' => 'GH₵ ' . number_format($totals['owed'], 2),'sub' => 'Uncollected balances','href' => route('manager.debtors')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'red','icon' => 'fa-hand-holding-dollar','title' => 'Owed','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('GH₵ ' . number_format($totals['owed'], 2)),'sub' => 'Uncollected balances','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.debtors'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'orange','icon' => 'fa-user-clock','title' => 'Active debtors','value' => $totals['debtors'],'sub' => 'With unpaid arrears','href' => route('manager.debtors')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'orange','icon' => 'fa-user-clock','title' => 'Active debtors','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($totals['debtors']),'sub' => 'With unpaid arrears','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.debtors'))]); ?>
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

<form method="GET" class="bg-white rounded-xl p-3 mb-4 text-sm">
    <div class="relative">
        <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input name="q" value="<?php echo e(request('q')); ?>" placeholder="Search name or phone…" class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]">
    </div>
</form>

<div class="bg-white rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm text-slate-800">All customers</div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Name</th><th>Phone</th><th>Default location</th><th class="text-right">Orders</th><th class="text-right">Lifetime spend</th><th class="text-right p-3">Balance due</th></tr></thead>
        <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $bal = max(($c->spend ?? 0) - ($c->paid ?? 0), 0); ?>
            <tr class="border-t border-slate-100 hover:bg-sky-50/50">
                <td class="p-3"><a class="inline-flex items-center gap-2 font-semibold text-[#1c3041] hover:underline" href="<?php echo e(route('manager.customers.show', $c)); ?>">
                    <span class="grid h-7 w-7 place-items-center rounded-full bg-slate-200 text-xs text-slate-600"><?php echo e(mb_strtoupper(mb_substr($c->name, 0, 1))); ?></span><?php echo e($c->name); ?></a></td>
                <td class="text-slate-600"><?php echo e($c->phone); ?></td><td class="text-slate-600"><?php echo e($c->location); ?></td>
                <td class="text-right"><?php echo e($c->orders); ?></td><td class="text-right"><?php echo e(number_format($c->spend ?? 0, 2)); ?></td>
                <td class="text-right p-3 <?php echo e($bal > 0 ? 'text-red-600 font-medium' : 'text-green-600'); ?>"><?php echo e(number_format($bal, 2)); ?></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="6" class="p-10 text-center text-slate-400"><i class="fas fa-users text-3xl mb-2 block"></i>No customers yet.</td></tr> <?php endif; ?>
    </table></div>
</div>
<div class="mt-4"><?php echo e($customers->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/customers/index.blade.php ENDPATH**/ ?>