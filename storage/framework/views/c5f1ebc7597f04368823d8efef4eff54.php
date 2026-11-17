<?php $__env->startSection('title', 'Website pages · Drezwel Go'); ?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => 'Website','title' => 'Website pages','subtitle' => 'Edit the policies, FAQs and other text customers read on your website.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => 'Website','title' => 'Website pages','subtitle' => 'Edit the policies, FAQs and other text customers read on your website.']); ?>
     <?php $__env->slot('chips', null, []); ?> <span><i class="fas fa-file-lines"></i> <?php echo e($pages->count()); ?> pages</span> <?php $__env->endSlot(); ?>
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

<div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
    <i class="fas fa-circle-info"></i> Pages marked <strong>Suggested draft</strong> use wording we prepared for Drezwel. Please read each one and adjust it to match how you really work, then save it. Saving makes it your own.
</div>

<div class="grid gap-3 md:grid-cols-2">
    <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="rounded-xl bg-white p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-slate-800"><?php echo e($p['title']); ?></h2>
                    <p class="mt-1 text-sm text-slate-500"><?php echo e($p['help']); ?></p>
                </div>
                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium <?php echo e($p['draft'] ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-700'); ?>"><?php echo e($p['draft'] ? 'Suggested draft' : 'Edited by you'); ?></span>
            </div>
            <div class="mt-3 flex gap-2 text-sm font-semibold">
                <a href="<?php echo e(route('manager.pages.edit', $p['slug'])); ?>" class="rounded-lg bg-[#1c3041] px-4 py-2 text-white hover:bg-[#14212d]"><i class="fas fa-pen"></i> Edit</a>
                <a href="<?php echo e($p['url']); ?>" target="_blank" class="rounded-lg bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200"><i class="fas fa-arrow-up-right-from-square"></i> View page</a>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/pages/index.blade.php ENDPATH**/ ?>