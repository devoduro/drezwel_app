<?php $__env->startSection('title', 'My Deliveries · Drezwel Go'); ?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-4 !p-5','eyebrow' => ''.e(now()->format('l, d M')).'','title' => 'My Deliveries','subtitle' => 'Start a delivery when you set off and ask the customer for their code.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4 !p-5','eyebrow' => ''.e(now()->format('l, d M')).'','title' => 'My Deliveries','subtitle' => 'Start a delivery when you set off and ask the customer for their code.']); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-route"></i> <?php echo e($deliveries->count()); ?> to do</span>
        <span><i class="fas fa-motorcycle"></i> <?php echo e($deliveries->where('status', 'out_for_delivery')->count()); ?> on the road</span>
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
<?php if($custody['cash_held'] > 0 || $custody['items_pending'] > 0): ?>
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['class' => 'mb-4','color' => 'orange','icon' => 'fa-wallet','title' => 'Hand in to manager','value' => 'GH₵ ' . number_format($custody['cash_held'], 2),'sub' => $custody['items_pending'] . ' item(s) to return']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4','color' => 'orange','icon' => 'fa-wallet','title' => 'Hand in to manager','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('GH₵ ' . number_format($custody['cash_held'], 2)),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($custody['items_pending'] . ' item(s) to return')]); ?>
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
<?php endif; ?>
<div class="space-y-3">
    <?php $__empty_1 = true; $__currentLoopData = $deliveries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?> <?php echo $__env->make('rider._card', ['d' => $d], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="bg-white rounded-xl p-8 text-center text-slate-500"><i class="fas fa-mug-hot text-3xl mb-2 block text-slate-300"></i>Nothing assigned yet.<br><a class="text-[#2c8fc6] font-semibold" href="<?php echo e(route('rider.available')); ?>">Pick an order →</a></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/rider/mine.blade.php ENDPATH**/ ?>