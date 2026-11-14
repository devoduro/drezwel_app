
<?php $__env->startSection('title', 'Messages · Drezwel Go'); ?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => 'Website','title' => 'Messages','subtitle' => 'Questions and wholesale enquiries sent from your website. Call the customer back, then mark it handled.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => 'Website','title' => 'Messages','subtitle' => 'Questions and wholesale enquiries sent from your website. Call the customer back, then mark it handled.']); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-envelope"></i> <?php echo e($counts['new']); ?> new</span>
        <span><i class="fas fa-inbox"></i> <?php echo e($counts['all']); ?> total</span>
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

<form method="GET" class="bg-white rounded-xl p-3 flex flex-wrap gap-2 mb-4 text-sm">
    <select name="status" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
        <?php $__currentLoopData = ['new' => 'New', 'handled' => 'Handled', 'all' => 'All']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(request('status', 'new') === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <select name="type" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
        <option value="">All types</option><option value="contact" <?php if(request('type') === 'contact'): echo 'selected'; endif; ?>>Contact</option><option value="wholesale" <?php if(request('type') === 'wholesale'): echo 'selected'; endif; ?>>Wholesale</option>
    </select>
    <button class="rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-4 py-2 font-semibold transition"><i class="fas fa-filter"></i> Filter</button>
</form>

<div class="space-y-3">
    <?php $__empty_1 = true; $__currentLoopData = $messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <section class="bg-white rounded-xl p-4 border-l-4 <?php echo e($m->status === 'new' ? 'border-l-[#1c3041]' : 'border-l-[#8cc152] opacity-80'); ?>">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="font-semibold"><?php echo e($m->name); ?>

                        <span class="ml-1 rounded-full px-2 py-0.5 text-xs <?php echo e($m->type === 'wholesale' ? 'bg-purple-100 text-purple-700' : 'bg-sky-100 text-sky-700'); ?>"><?php echo e($m->type === 'wholesale' ? 'Wholesale' : 'Contact'); ?></span></div>
                    <div class="text-sm text-slate-500">
                        <a class="text-[#1c3041] hover:underline" href="tel:<?php echo e($m->phone); ?>"><i class="fas fa-phone"></i> <?php echo e($m->phone); ?></a>
                        <?php if($m->email): ?> · <a class="hover:underline" href="mailto:<?php echo e($m->email); ?>"><?php echo e($m->email); ?></a><?php endif; ?>
                        · <?php echo e($m->created_at->format('d M Y, g:ia')); ?>

                    </div>
                </div>
                <form method="POST" action="<?php echo e(route('manager.messages.toggle', $m)); ?>"><?php echo csrf_field(); ?>
                    <button class="rounded-lg border px-3 py-1.5 text-sm font-semibold hover:border-[#1c3041]"><?php echo e($m->status === 'new' ? 'Mark handled' : 'Reopen'); ?></button>
                </form>
            </div>
            <?php if($m->subject): ?><div class="mt-2 font-medium"><?php echo e($m->subject); ?></div><?php endif; ?>
            <p class="mt-1 whitespace-pre-line text-sm text-slate-700"><?php echo e($m->message); ?></p>
        </section>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="bg-white rounded-xl p-10 text-center text-slate-400"><i class="fas fa-inbox text-3xl mb-2 block"></i>No messages here.</div>
    <?php endif; ?>
</div>
<div class="mt-4"><?php echo e($messages->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/messages.blade.php ENDPATH**/ ?>