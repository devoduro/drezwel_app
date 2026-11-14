
<?php $__env->startSection('title', $title . ' | ' . \App\Models\Setting::get('business_name')); ?>
<?php $__env->startSection('description', \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($body))), 155)); ?>
<?php $__env->startSection('content'); ?>
<div class="bg-jm-dark text-white">
    <div class="max-w-[1180px] mx-auto px-4 py-10"><h1 class="text-3xl font-bold"><?php echo e($title); ?></h1></div>
</div>
<div class="max-w-3xl mx-auto px-4 py-10">
    <article class="rounded-xl bg-white p-6 sm:p-8 text-slate-700 leading-relaxed space-y-4">
        <?php $paras = preg_split("/\n\s*\n/", trim($body)); ?>
        <?php $__currentLoopData = $paras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $para): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($i === 0 && ! str_contains($para, "\n") && mb_strlen($para) < 60): ?>
                
            <?php else: ?>
                <p class="whitespace-pre-line"><?php echo e($para); ?></p>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </article>
    <div class="mt-6 flex flex-wrap gap-3 text-sm font-semibold">
        <a href="<?php echo e(route('site.contact')); ?>" class="rounded-xl bg-white px-5 py-2.5">Questions? Contact us</a>
        <a href="<?php echo e(route('shop.index')); ?>" class="rounded-xl bg-jm-orange text-white px-5 py-2.5">Back to shop</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.shop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/site/page.blade.php ENDPATH**/ ?>