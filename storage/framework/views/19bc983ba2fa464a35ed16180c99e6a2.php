<a href="<?php echo e(route('rider.show', $d)); ?>" class="block bg-white rounded-xl p-4 border-l-4 transition hover:shadow-md <?php echo e($d->status === 'out_for_delivery' ? 'border-l-[#967adc]' : ($d->status === 'delivered' ? 'border-l-[#8cc152]' : ($d->status === 'undelivered' ? 'border-l-[#da4453]' : 'border-l-[#1c3041]'))); ?>">
    <div class="flex justify-between gap-2">
        <div class="font-semibold"><?php echo e($d->customer->name); ?></div>
        <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
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
<?php endif; ?>
    </div>
    <div class="text-xs text-slate-500 mt-0.5"><?php echo e($d->reference); ?> · <?php echo e($d->delivery_date->format('d M')); ?> <?php echo e($d->time_window); ?></div>
    <div class="text-sm mt-2"><i class="fas fa-location-dot text-slate-400"></i> <?php echo e($d->location); ?></div>
    <div class="text-sm text-slate-500"><i class="fas fa-box text-slate-400"></i> <?php echo e($d->package_description); ?></div>
    <div class="text-sm mt-2 font-semibold <?php echo e($d->balance > 0 ? 'text-[#c12f3e]' : 'text-green-600'); ?>"><?php echo e($d->balance > 0 ? 'Collect GH₵ ' . number_format($d->balance, 2) : 'Fully paid'); ?></div>
</a>
<?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/rider/_card.blade.php ENDPATH**/ ?>