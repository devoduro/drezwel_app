
<?php
    $intro = \App\Support\Pages::text('shop_intro_text');
    $faqs = \App\Support\Markup::faqs(\App\Support\Pages::text('faq_text'));
?>
<?php if($intro !== '' || $faqs): ?>
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 pb-6 space-y-3">
    <?php if($intro !== ''): ?>
        <section class="rounded-xl bg-white p-5 sm:p-6"><div class="md-text"><?php echo \App\Support\Markup::render($intro); ?></div></section>
    <?php endif; ?>
    <?php if($faqs): ?>
        <section class="rounded-xl bg-white p-5 sm:p-6">
            <h2 class="mb-3 text-xl font-bold text-jm-dark">Frequently asked questions</h2>
            <div class="divide-y divide-slate-200 rounded-lg border border-slate-200">
                <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <details class="group p-4">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 font-semibold text-jm-dark"><?php echo e($f['q']); ?><i class="fas fa-chevron-down text-xs text-jm-grey transition group-open:rotate-180"></i></summary>
                        <div class="md-text mt-2 text-[#535357]"><?php echo \App\Support\Markup::render($f['a']); ?></div>
                    </details>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <a href="<?php echo e(route('site.page', 'faqs')); ?>" class="mt-3 inline-block text-sm font-bold uppercase text-jm-primary hover:underline">All FAQs <i class="fas fa-chevron-right text-[10px]"></i></a>
        </section>
    <?php endif; ?>
</div>
<?php echo $__env->make('site._md-style', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/shop/_seo.blade.php ENDPATH**/ ?>