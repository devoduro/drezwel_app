
<?php $__env->startSection('title', $title . ' | ' . \App\Models\Setting::get('business_name')); ?>
<?php $__env->startSection('description', \Illuminate\Support\Str::limit(\App\Support\Markup::plain($body), 155)); ?>
<?php $__env->startSection('content'); ?>
<?php echo $__env->make('site._md-style', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="bg-jm-dark text-white">
    <div class="max-w-[1180px] mx-auto px-4 py-10"><h1 class="text-3xl font-bold"><?php echo e($title); ?></h1></div>
</div>
<div class="max-w-3xl mx-auto px-4 py-10">
    <?php if($slug === 'faqs'): ?>
        <div class="divide-y divide-slate-200 rounded-xl bg-white">
            <?php $__currentLoopData = \App\Support\Markup::faqs($body); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <details class="group p-5" <?php if($loop->first): ?> open <?php endif; ?>>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-lg font-semibold text-jm-dark"><?php echo e($f['q']); ?><i class="fas fa-chevron-down text-xs text-jm-grey transition group-open:rotate-180"></i></summary>
                    <div class="md-text mt-2"><?php echo \App\Support\Markup::render($f['a']); ?></div>
                </details>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php else: ?>
        <article class="rounded-xl bg-white p-6 sm:p-8"><div class="md-text"><?php echo \App\Support\Markup::render($body); ?></div></article>
    <?php endif; ?>
    <div class="mt-6 flex flex-wrap gap-3 text-sm font-semibold">
        <a href="<?php echo e(route('site.contact')); ?>" class="rounded-xl bg-white px-5 py-2.5">Questions? Contact us</a>
        <a href="<?php echo e(route('shop.index')); ?>" class="rounded-xl bg-jm-primary text-white px-5 py-2.5">Back to shop</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.shop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/site/page.blade.php ENDPATH**/ ?>