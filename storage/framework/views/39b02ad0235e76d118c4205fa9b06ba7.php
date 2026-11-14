
<?php $__env->startSection('title', 'Deliveries · Drezwel Go'); ?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => 'Manage orders','title' => 'Deliveries','subtitle' => 'Create orders, assign riders and follow every delivery from pending to delivered.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => 'Manage orders','title' => 'Deliveries','subtitle' => 'Create orders, assign riders and follow every delivery from pending to delivered.']); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-box"></i> <?php echo e($counts->sum() - ($counts['cancelled'] ?? 0)); ?> orders</span>
        <span><i class="fas fa-filter"></i> <?php echo e($deliveries->total()); ?> shown</span>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'orange','icon' => 'fa-hourglass-half','title' => 'Pending','value' => $counts['pending'] ?? 0,'sub' => 'Waiting for a rider','href' => route('manager.deliveries.index', ['status' => 'pending'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'orange','icon' => 'fa-hourglass-half','title' => 'Pending','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($counts['pending'] ?? 0),'sub' => 'Waiting for a rider','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.deliveries.index', ['status' => 'pending']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'blue','icon' => 'fa-motorcycle','title' => 'On the road','value' => ($counts['assigned'] ?? 0) + ($counts['out_for_delivery'] ?? 0),'sub' => ($counts['out_for_delivery'] ?? 0) . ' out · ' . ($counts['assigned'] ?? 0) . ' assigned','href' => route('manager.deliveries.index', ['status' => 'out_for_delivery'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'blue','icon' => 'fa-motorcycle','title' => 'On the road','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($counts['assigned'] ?? 0) + ($counts['out_for_delivery'] ?? 0)),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($counts['out_for_delivery'] ?? 0) . ' out · ' . ($counts['assigned'] ?? 0) . ' assigned'),'href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.deliveries.index', ['status' => 'out_for_delivery']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'green','icon' => 'fa-circle-check','title' => 'Delivered','value' => $counts['delivered'] ?? 0,'sub' => 'Completed drop-offs','href' => route('manager.deliveries.index', ['status' => 'delivered'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green','icon' => 'fa-circle-check','title' => 'Delivered','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($counts['delivered'] ?? 0),'sub' => 'Completed drop-offs','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.deliveries.index', ['status' => 'delivered']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'red','icon' => 'fa-triangle-exclamation','title' => 'Undelivered','value' => $counts['undelivered'] ?? 0,'sub' => 'Needs follow-up','href' => route('manager.deliveries.index', ['status' => 'undelivered'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'red','icon' => 'fa-triangle-exclamation','title' => 'Undelivered','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($counts['undelivered'] ?? 0),'sub' => 'Needs follow-up','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.deliveries.index', ['status' => 'undelivered']))]); ?>
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

<form method="GET" class="bg-white rounded-xl p-3 grid grid-cols-2 md:grid-cols-5 gap-2 mb-4 text-sm">
    <div class="relative col-span-2">
        <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input name="q" value="<?php echo e(request('q')); ?>" placeholder="Search name, phone, reference…" class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#42a5dc]">
    </div>
    <select name="status" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><option value="">All statuses</option>
        <?php $__currentLoopData = \App\Models\Delivery::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(request('status') === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
    <select name="rider" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><option value="">All riders</option>
        <?php $__currentLoopData = $riders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($r->id); ?>" <?php if(request('rider') == $r->id): echo 'selected'; endif; ?>><?php echo e($r->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
    <div class="flex gap-2">
        <input type="date" name="date" value="<?php echo e(request('date')); ?>" class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-slate-50 px-2 py-2">
        <button class="rounded-lg bg-[#42a5dc] hover:bg-[#2c8fc6] text-white px-4 font-semibold transition"><i class="fas fa-filter"></i></button>
    </div>
</form>

<div class="bg-white rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#42a5dc] font-semibold text-sm text-slate-800">All deliveries</div>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Reference</th><th>Date</th><th>Customer</th><th>Location</th><th>Rider</th><th class="text-right">Total</th><th class="text-right">Balance</th><th class="p-3">Status</th></tr></thead>
        <?php $__empty_1 = true; $__currentLoopData = $deliveries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-t border-slate-100 hover:bg-sky-50/50">
                <td class="p-3"><a class="text-[#2c8fc6] font-semibold hover:underline" href="<?php echo e(route('manager.deliveries.show', $d)); ?>"><?php echo e($d->reference); ?></a>
                    <?php if($d->source === 'online'): ?><span class="ml-1 rounded bg-purple-100 px-1.5 py-0.5 text-[10px] font-semibold text-purple-700">WEB</span><?php elseif($d->source === 'pos'): ?><span class="ml-1 rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700">POS</span><?php elseif($d->source === 'phone'): ?><span class="ml-1 rounded bg-sky-100 px-1.5 py-0.5 text-[10px] font-semibold text-sky-700">PHONE</span><?php endif; ?></td>
                <td class="whitespace-nowrap text-slate-600"><?php echo e($d->delivery_date->format('d M')); ?></td>
                <td><div class="font-medium"><?php echo e($d->customer->name); ?></div><div class="text-xs text-slate-400"><?php echo e($d->customer->phone); ?></div></td>
                <td class="text-slate-600"><?php echo e($d->location); ?></td>
                <td><?php if($d->rider): ?><span class="inline-flex items-center gap-1.5"><span class="grid h-6 w-6 place-items-center rounded-full bg-slate-200 text-[11px] font-semibold text-slate-600"><?php echo e(mb_strtoupper(mb_substr($d->rider->name, 0, 1))); ?></span><?php echo e($d->rider->name); ?></span><?php else: ?><span class="text-slate-400">—</span><?php endif; ?></td>
                <td class="text-right"><?php echo e(number_format($d->amount_due, 2)); ?></td>
                <td class="text-right <?php echo e($d->balance > 0 ? 'text-red-600 font-medium' : 'text-green-600'); ?>"><?php echo e(number_format($d->balance, 2)); ?></td>
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
<?php endif; ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="8" class="p-10 text-center text-slate-400"><i class="fas fa-box-open text-3xl mb-2 block"></i>No deliveries found.</td></tr> <?php endif; ?>
    </table>
    </div>
</div>
<div class="mt-4"><?php echo e($deliveries->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/deliveries/index.blade.php ENDPATH**/ ?>