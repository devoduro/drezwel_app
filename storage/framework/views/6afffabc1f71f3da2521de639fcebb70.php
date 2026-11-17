
<?php $__env->startSection('title', $customer->name . ' · Delivery history · Drezwel Go'); ?>
<?php
    $input = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm w-full focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]';
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $owed = max($stats['spend'] - $stats['paid'], 0);
?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => 'Customer history','title' => $customer->name,'subtitle' => $customer->phone . ($customer->location ? ' · ' . $customer->location : '')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => 'Customer history','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($customer->name),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($customer->phone . ($customer->location ? ' · ' . $customer->location : ''))]); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-box"></i> <?php echo e($stats['orders']); ?> orders</span>
        <span><i class="fas fa-calendar"></i> <?php echo e($stats['last'] ? 'Last order ' . \Carbon\Carbon::parse($stats['last'])->format('d M Y') : 'No orders yet'); ?></span>
     <?php $__env->endSlot(); ?>
    <a href="<?php echo e(route('manager.customers.index')); ?>"><i class="fas fa-arrow-left"></i> Customers</a>
    <a href="tel:<?php echo e($customer->phone); ?>"><i class="fas fa-phone"></i> Call</a>
    <a href="<?php echo e(route('manager.deliveries.create', ['name' => $customer->name, 'phone' => $customer->phone, 'location' => $customer->location])); ?>" class="primary"><i class="fas fa-plus"></i> New order</a>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'blue','icon' => 'fa-box','title' => 'Orders','value' => $stats['orders'],'sub' => $stats['delivered'] . ' delivered']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'blue','icon' => 'fa-box','title' => 'Orders','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['orders']),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['delivered'] . ' delivered')]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'orange','icon' => 'fa-hourglass-half','title' => 'In progress','value' => $stats['pending'],'sub' => 'Not delivered yet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'orange','icon' => 'fa-hourglass-half','title' => 'In progress','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['pending']),'sub' => 'Not delivered yet']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'green','icon' => 'fa-sack-dollar','title' => 'Lifetime spend','value' => $m($stats['spend']),'sub' => 'Paid ' . $m($stats['paid'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green','icon' => 'fa-sack-dollar','title' => 'Lifetime spend','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($stats['spend'])),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Paid ' . $m($stats['paid']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => $owed > 0 ? 'red' : 'green','icon' => 'fa-hand-holding-dollar','title' => 'Balance due','value' => $m($owed),'sub' => $owed > 0 ? 'Still owed' : 'All settled','href' => $owed > 0 ? route('manager.debtors') : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($owed > 0 ? 'red' : 'green'),'icon' => 'fa-hand-holding-dollar','title' => 'Balance due','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($owed)),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($owed > 0 ? 'Still owed' : 'All settled'),'href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($owed > 0 ? route('manager.debtors') : null)]); ?>
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

<?php $walkIn = $customer->phone === \App\Services\SalesService::WALK_IN_PHONE; $orderCount = $customer->deliveries()->count(); ?>
<form method="POST" action="<?php echo e(route('manager.customers.update', $customer)); ?>" class="bg-white rounded-xl p-4 grid md:grid-cols-5 gap-2 mb-3 border-l-4 border-l-[#8cc152]"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
    <?php if($errors->any()): ?><div class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 md:col-span-5"><?php echo e($errors->first()); ?></div><?php endif; ?>
    <input name="name" value="<?php echo e(old('name', $customer->name)); ?>" required placeholder="Name" class="<?php echo e($input); ?>">
    <?php if (! ($walkIn)): ?><input name="phone" value="<?php echo e(old('phone', $customer->phone)); ?>" required inputmode="tel" placeholder="Phone" class="<?php echo e($input); ?>"><?php endif; ?>
    <input name="location" value="<?php echo e($customer->location); ?>" placeholder="Default location" class="<?php echo e($input); ?>">
    <input name="notes" value="<?php echo e($customer->notes); ?>" placeholder="Notes" class="<?php echo e($input); ?>">
    <button class="rounded-lg bg-[#8cc152] hover:bg-[#74a83b] text-white px-4 py-2 text-sm font-semibold transition">Save details</button>
</form>
<?php if (! ($walkIn)): ?>
    <form method="POST" action="<?php echo e(route('manager.customers.destroy', $customer)); ?>" class="mb-5 flex flex-wrap items-center gap-3 rounded-xl bg-white p-3 text-sm"
          onsubmit="return confirm('Permanently delete <?php echo e(addslashes($customer->name)); ?><?php echo e($orderCount ? ' and their ' . $orderCount . ' order(s)' : ''); ?>? This cannot be undone.')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
        <?php if($orderCount): ?><label class="flex items-center gap-2 text-slate-600"><input type="checkbox" name="with_orders" value="1" required class="accent-red-600"> Also delete their <?php echo e($orderCount); ?> <?php echo e($orderCount === 1 ? 'order' : 'orders'); ?> (items go back to stock)</label><?php endif; ?>
        <button class="ml-auto rounded-lg bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-700"><i class="fas fa-trash"></i> Delete customer</button>
    </form>
<?php endif; ?>

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
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Reference</th><th>Date</th><th>Package</th><th>Location</th><th>Rider</th><th class="text-right">Total</th><th class="text-right">Paid</th><th class="text-right">Balance</th><th>Delivered at</th><th class="p-3">Status</th></tr></thead>
        <?php $__empty_1 = true; $__currentLoopData = $deliveries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-t border-slate-100 hover:bg-sky-50/50">
                <td class="p-3"><a class="text-[#1c3041] font-semibold hover:underline" href="<?php echo e(route('manager.deliveries.show', $d)); ?>"><?php echo e($d->reference); ?></a></td>
                <td class="whitespace-nowrap"><?php echo e($d->delivery_date->format('d M Y')); ?></td>
                <td><?php echo e($d->package_description); ?><div class="text-xs text-slate-400"><?php echo e(\App\Models\Delivery::TYPES[$d->order_type] ?? ''); ?></div></td>
                <td class="text-slate-600"><?php echo e($d->location); ?></td>
                <td><?php echo e($d->rider?->name ?? '—'); ?></td>
                <td class="text-right"><?php echo e(number_format($d->amount_due, 2)); ?></td>
                <td class="text-right text-slate-500"><?php echo e(number_format($d->amount_paid, 2)); ?></td>
                <td class="text-right <?php echo e($d->balance > 0 ? 'text-red-600 font-medium' : 'text-green-600'); ?>"><?php echo e(number_format($d->balance, 2)); ?></td>
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
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="10" class="p-10 text-center text-slate-400"><i class="fas fa-clock-rotate-left text-3xl mb-2 block"></i>No deliveries match.</td></tr> <?php endif; ?>
    </table></div>
</div>
<div class="mt-4"><?php echo e($deliveries->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/customers/show.blade.php ENDPATH**/ ?>