
<?php $__env->startSection('title', $rider->name . ' · Delivery history · Drezwel Go'); ?>
<?php $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2); ?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => 'Rider history','title' => $rider->name,'subtitle' => ($rider->phone ?: 'No phone') . ($rider->email ? ' · ' . $rider->email : '')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => 'Rider history','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($rider->name),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($rider->phone ?: 'No phone') . ($rider->email ? ' · ' . $rider->email : ''))]); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-box"></i> <?php echo e($stats['total']); ?> deliveries</span>
        <span><i class="fas fa-bullseye"></i> <?php echo e($stats['rate']); ?>% delivered</span>
        <span class="<?php echo e($custody['cleared'] ? '' : '!bg-amber-400/30'); ?>"><i class="fas fa-wallet"></i> <?php echo e($custody['cleared'] ? 'Cleared' : 'Holding ' . $m($custody['cash_held'])); ?></span>
     <?php $__env->endSlot(); ?>
    <a href="<?php echo e(route('manager.riders.index')); ?>"><i class="fas fa-arrow-left"></i> Riders</a>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'green','icon' => 'fa-circle-check','title' => 'Delivered','value' => $stats['delivered'],'sub' => $m($stats['value']) . ' value']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green','icon' => 'fa-circle-check','title' => 'Delivered','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['delivered']),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($stats['value']) . ' value')]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'blue','icon' => 'fa-route','title' => 'Open runs','value' => $stats['open'],'sub' => 'Assigned or on the road']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'blue','icon' => 'fa-route','title' => 'Open runs','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['open']),'sub' => 'Assigned or on the road']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'red','icon' => 'fa-triangle-exclamation','title' => 'Undelivered','value' => $stats['undelivered'],'sub' => 'Failed attempts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'red','icon' => 'fa-triangle-exclamation','title' => 'Undelivered','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['undelivered']),'sub' => 'Failed attempts']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'orange','icon' => 'fa-motorcycle','title' => 'Delivery fees','value' => $m($stats['fees']),'sub' => 'On delivered orders']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'orange','icon' => 'fa-motorcycle','title' => 'Delivery fees','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($stats['fees'])),'sub' => 'On delivered orders']); ?>
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

<form method="GET" class="bg-white rounded-xl p-3 grid grid-cols-2 md:grid-cols-4 gap-2 mb-4 text-sm">
    <select name="status" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><option value="">All statuses</option>
        <?php $__currentLoopData = \App\Models\Delivery::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(request('status') === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
    <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-2" title="From">
    <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-2" title="To">
    <button class="rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-4 py-2 font-semibold transition"><i class="fas fa-filter"></i> Filter</button>
</form>

<div class="bg-white rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm">Delivery history</div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Reference</th><th>Date</th><th>Customer</th><th>Location</th><th class="text-right">Customer pays</th><th class="text-right">Of which fee</th><th>Delivered at</th><th class="p-3">Status</th></tr></thead>
        <?php $__empty_1 = true; $__currentLoopData = $deliveries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-t border-slate-100 hover:bg-sky-50/50">
                <td class="p-3"><a class="text-[#1c3041] font-semibold hover:underline" href="<?php echo e(route('manager.deliveries.show', $d)); ?>"><?php echo e($d->reference); ?></a></td>
                <td class="whitespace-nowrap"><?php echo e($d->delivery_date->format('d M Y')); ?></td>
                <td><div class="font-medium"><?php echo e($d->customer->name); ?></div><div class="text-xs text-slate-400"><?php echo e($d->customer->phone); ?></div></td>
                <td class="text-slate-600"><?php echo e($d->location); ?></td>
                <td class="text-right"><?php echo e(number_format($d->amount_due, 2)); ?></td>
                <td class="text-right text-slate-500"><?php echo e(number_format($d->delivery_fee, 2)); ?></td>
                <td class="whitespace-nowrap text-slate-500"><?php echo e($d->delivered_at?->format('d M, g:ia') ?? '—'); ?></td>
                <td class="p-3"><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $d->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($d->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?><?php if($d->undelivered_reason): ?><div class="text-xs text-red-600 mt-1"><?php echo e($d->undelivered_reason); ?></div><?php endif; ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="8" class="p-10 text-center text-slate-400"><i class="fas fa-clock-rotate-left text-3xl mb-2 block"></i>No deliveries for this rider yet.</td></tr> <?php endif; ?>
    </table></div>
</div>
<div class="mt-4"><?php echo e($deliveries->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/riders/show.blade.php ENDPATH**/ ?>