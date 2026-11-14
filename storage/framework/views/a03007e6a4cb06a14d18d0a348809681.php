
<?php $__env->startSection('title', 'Debtors · Drezwel Go'); ?>
<?php $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2); ?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => 'Collections','title' => 'Debtors','subtitle' => 'Customers with unpaid balances, oldest debt first. Record a payment right here.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => 'Collections','title' => 'Debtors','subtitle' => 'Customers with unpaid balances, oldest debt first. Record a payment right here.']); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-user-clock"></i> <?php echo e($debtors->count()); ?> owing</span>
        <span><i class="fas fa-coins"></i> <?php echo e($m($total)); ?></span>
     <?php $__env->endSlot(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'red','icon' => 'fa-hand-holding-dollar','title' => 'Total outstanding','value' => $m($total),'sub' => 'Arrears balance']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'red','icon' => 'fa-hand-holding-dollar','title' => 'Total outstanding','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($total)),'sub' => 'Arrears balance']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'blue','icon' => 'fa-users','title' => 'Customers owing','value' => $debtors->count(),'sub' => 'Active debtor accounts']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'blue','icon' => 'fa-users','title' => 'Customers owing','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($debtors->count()),'sub' => 'Active debtor accounts']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'green','icon' => 'fa-clock','title' => '1–7 days','value' => $m($aging['recent']),'sub' => 'Recent']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green','icon' => 'fa-clock','title' => '1–7 days','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($aging['recent'])),'sub' => 'Recent']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'orange','icon' => 'fa-clock-rotate-left','title' => '8+ days','value' => $m($aging['old']),'sub' => 'Urgent collection']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'orange','icon' => 'fa-clock-rotate-left','title' => '8+ days','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($aging['old'])),'sub' => 'Urgent collection']); ?>
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

<div class="space-y-4">
    <?php $__empty_1 = true; $__currentLoopData = $debtors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <section class="bg-white rounded-xl overflow-hidden border-l-4 <?php echo e($d->age > 7 ? 'border-l-red-500' : 'border-l-[#1c3041]'); ?>">
            <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 bg-slate-50 border-b border-slate-100">
                <a class="inline-flex items-center gap-2 font-semibold text-[#1c3041]" href="<?php echo e(route('manager.customers.show', $d->customer)); ?>">
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-slate-200 text-xs text-slate-600"><?php echo e(mb_strtoupper(mb_substr($d->customer->name, 0, 1))); ?></span>
                    <?php echo e($d->customer->name); ?> <span class="text-slate-400 text-sm font-normal"><?php echo e($d->customer->phone); ?></span></a>
                <div class="text-right"><strong class="text-red-600"><?php echo e($m($d->balance)); ?></strong> <span class="text-xs rounded-full px-2 py-0.5 <?php echo e($d->age > 7 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800'); ?>"><?php echo e($d->age); ?> day(s)</span></div>
            </div>
            <div class="px-4 text-sm divide-y divide-slate-100">
                <?php $__currentLoopData = $d->deliveries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $x): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="py-2.5 flex flex-wrap items-center justify-between gap-2">
                        <a class="text-slate-600 hover:text-slate-900" href="<?php echo e(route('manager.deliveries.show', $x)); ?>"><?php echo e($x->reference); ?> · <?php echo e($x->package_description); ?> <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $x->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($x->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?></a>
                        <form method="POST" action="<?php echo e(route('manager.deliveries.payment', $x)); ?>" class="flex gap-1"><?php echo csrf_field(); ?>
                            <input type="number" step="0.01" name="amount" value="<?php echo e($x->balance); ?>" max="<?php echo e($x->balance); ?>" class="w-24 rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                            <select name="method" class="rounded-lg border border-slate-200 bg-slate-50 px-1 py-1.5"><?php $__currentLoopData = \App\Models\Payment::METHODS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>"><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                            <button class="rounded-lg bg-[#8cc152] hover:bg-[#74a83b] text-white px-3 py-1.5 font-semibold transition">Pay</button>
                        </form>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="bg-white rounded-xl p-10 text-center text-slate-400"><i class="fas fa-champagne-glasses text-3xl mb-2 block text-[#8cc152]"></i>Nobody owes anything.</div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/debtors.blade.php ENDPATH**/ ?>