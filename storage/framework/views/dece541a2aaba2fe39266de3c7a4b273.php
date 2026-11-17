
<?php $__env->startSection('title', 'Orders · Drezwel Go'); ?>
<?php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $field = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]';
    $keep = request()->except('status', 'page');
    $tabs = ['all' => 'All'] + \App\Models\Delivery::STATUSES;
    $allCount = $counts->sum();
    $badge = ['online' => 'bg-purple-100 text-purple-700', 'phone' => 'bg-sky-100 text-sky-700', 'pos' => 'bg-emerald-100 text-emerald-700', 'manager' => 'bg-slate-100 text-slate-600'];
?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => 'Customer orders','title' => 'Orders','subtitle' => 'Everything customers have ordered: from the website, by phone, at the shop counter or entered by your team.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => 'Customer orders','title' => 'Orders','subtitle' => 'Everything customers have ordered: from the website, by phone, at the shop counter or entered by your team.']); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-box"></i> <?php echo e($today['orders']); ?> today</span>
        <span><i class="fas fa-globe"></i> <?php echo e($today['web']); ?> from the website today</span>
     <?php $__env->endSlot(); ?>
    <a href="<?php echo e(route('manager.pos')); ?>"><i class="fas fa-cash-register"></i> Point of sale</a>
    <a href="<?php echo e(route('manager.deliveries.create')); ?>" class="primary"><i class="fas fa-plus"></i> New order</a>
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

<?php if($waiting > 0): ?>
    <a href="<?php echo e(route('manager.orders', ['status' => 'pending'])); ?>" class="mb-5 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-purple-200 bg-purple-50 px-4 py-3 text-sm text-purple-900 hover:bg-purple-100">
        <span><i class="fas fa-bell"></i> <strong><?php echo e($waiting); ?></strong> new website / phone <?php echo e($waiting === 1 ? 'order is' : 'orders are'); ?> waiting for a rider.</span>
        <span class="font-semibold">Review them <i class="fas fa-arrow-right"></i></span>
    </a>
<?php endif; ?>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'blue','icon' => 'fa-box','title' => 'Orders today','value' => $today['orders'],'sub' => $m($today['value']) . ' billed']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'blue','icon' => 'fa-box','title' => 'Orders today','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($today['orders']),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($today['value']) . ' billed')]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'purple','icon' => 'fa-globe','title' => 'Website orders today','value' => $today['web'],'sub' => 'Placed online']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'purple','icon' => 'fa-globe','title' => 'Website orders today','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($today['web']),'sub' => 'Placed online']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'orange','icon' => 'fa-hourglass-half','title' => 'In progress','value' => $today['in_progress'],'sub' => 'Not delivered yet','href' => route('manager.orders', ['status' => 'pending'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'orange','icon' => 'fa-hourglass-half','title' => 'In progress','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($today['in_progress']),'sub' => 'Not delivered yet','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.orders', ['status' => 'pending']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => $waiting ? 'red' : 'green','icon' => 'fa-bell','title' => 'Waiting for a rider','value' => $waiting,'sub' => 'New website & phone orders']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($waiting ? 'red' : 'green'),'icon' => 'fa-bell','title' => 'Waiting for a rider','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($waiting),'sub' => 'New website & phone orders']); ?>
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


<div class="no-print mb-3 flex flex-wrap gap-2 text-sm">
    <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $n = $k === 'all' ? $allCount : ($counts[$k] ?? 0); ?>
        <a href="<?php echo e(route('manager.orders', $keep + ['status' => $k])); ?>" class="rounded-full border px-3.5 py-1.5 font-medium <?php echo e($status === $k ? 'border-[#1c3041] bg-[#1c3041] text-white' : 'bg-white text-slate-600 hover:border-[#1c3041]'); ?>"><?php echo e($label); ?> <span class="<?php echo e($status === $k ? 'text-white/70' : 'text-slate-400'); ?>"><?php echo e($n); ?></span></a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<form method="GET" class="mb-4 grid grid-cols-2 gap-2 rounded-xl bg-white p-3 text-sm md:grid-cols-6">
    <input type="hidden" name="status" value="<?php echo e($status); ?>">
    <div class="relative col-span-2"><i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input name="q" value="<?php echo e(request('q')); ?>" placeholder="Search name, phone, reference…" class="<?php echo e($field); ?> w-full pl-9"></div>
    <select name="source" class="<?php echo e($field); ?>"><option value="">All channels</option><?php $__currentLoopData = $sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(request('source') === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
    <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="<?php echo e($field); ?>" title="From">
    <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="<?php echo e($field); ?>" title="To">
    <button class="rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-4 py-2 font-semibold transition"><i class="fas fa-filter"></i> Filter</button>
</form>

<div class="bg-white rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm"><?php echo e($tabs[$status] ?? 'Orders'); ?> · <?php echo e($orders->total()); ?></div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Order</th><th>Placed</th><th>Customer</th><th>Items</th><th>Channel</th><th>Rider</th><th class="text-right">Total</th><th class="text-right">Balance</th><th class="p-3">Status</th><th></th></tr></thead>
        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-t border-slate-100 hover:bg-sky-50/50 align-top">
                <td class="p-3"><a class="font-semibold text-[#1c3041] hover:underline" href="<?php echo e(route('manager.deliveries.show', $o)); ?>"><?php echo e($o->reference); ?></a></td>
                <td class="whitespace-nowrap text-slate-600"><?php echo e($o->created_at->format('d M, g:ia')); ?></td>
                <td><div class="font-medium"><?php echo e($o->customer->phone === \App\Services\SalesService::WALK_IN_PHONE ? 'Walk-in customer' : $o->customer->name); ?></div><div class="text-xs text-slate-400"><?php echo e($o->location); ?></div></td>
                <td class="max-w-[220px] text-slate-600"><div class="truncate" title="<?php echo e($o->package_description); ?>"><?php echo e($o->package_description); ?></div></td>
                <td><span class="rounded-full px-2 py-0.5 text-xs font-medium <?php echo e($badge[$o->source] ?? $badge['manager']); ?>"><?php echo e($sources[$o->source] ?? $o->source); ?></span></td>
                <td><?php echo e($o->rider?->name ?? '—'); ?></td>
                <td class="text-right"><?php echo e(number_format($o->amount_due, 2)); ?></td>
                <td class="text-right <?php echo e($o->balance > 0 && $o->status !== 'cancelled' ? 'text-red-600 font-medium' : 'text-green-600'); ?>"><?php echo e(number_format($o->status === 'cancelled' ? 0 : $o->balance, 2)); ?></td>
                <td class="p-3"><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $o->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($o->status)]); ?>
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
                <td class="p-3 whitespace-nowrap text-right text-xs">
                    <a class="text-[#1c3041] hover:underline" href="<?php echo e(route('manager.deliveries.show', $o)); ?>">Open</a> ·
                    <a class="text-[#1c3041] hover:underline" target="_blank" href="<?php echo e(route('manager.deliveries.invoice', $o)); ?>">Invoice</a> ·
                    <?php if($o->status !== 'cancelled'): ?><a class="text-[#1c3041] hover:underline" href="<?php echo e(route('manager.deliveries.edit', $o)); ?>">Edit</a> ·<?php endif; ?>
                    <?php echo $__env->make('manager._delete-delivery', ['d' => $o], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="10" class="p-10 text-center text-slate-400"><i class="fas fa-box-open text-3xl mb-2 block"></i>No orders match.</td></tr> <?php endif; ?>
    </table></div>
</div>
<div class="mt-4"><?php echo e($orders->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/orders.blade.php ENDPATH**/ ?>