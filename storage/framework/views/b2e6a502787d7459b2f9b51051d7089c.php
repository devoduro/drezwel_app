
<?php
    $intro = \App\Support\Pages::text('shop_intro_text');
    $faqs = \App\Support\Markup::faqs(\App\Support\Pages::text('faq_text'));
?>
<?php if($intro !== '' || $faqs): ?>
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 pb-6 space-y-2">
    <?php if($intro !== ''): ?>
        <section class="rounded-xl bg-white p-4">
            <div id="seoText" class="md-text md-small relative max-h-[4.6rem] overflow-hidden transition-all">
                <?php echo \App\Support\Markup::render($intro); ?>

                <div id="seoFade" class="pointer-events-none absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-white to-transparent"></div>
            </div>
            <button type="button" id="seoMore" class="mt-2 text-sm font-bold uppercase text-jm-primary hover:underline">Read more <i class="fas fa-chevron-down text-[10px]"></i></button>
        </section>
        <script>
            document.getElementById('seoMore').addEventListener('click', function () {
                var box = document.getElementById('seoText'), open = box.classList.toggle('is-open');
                box.style.maxHeight = open ? box.scrollHeight + 'px' : '';
                document.getElementById('seoFade').style.display = open ? 'none' : '';
                this.innerHTML = open ? 'Show less <i class="fas fa-chevron-up text-[10px]"></i>' : 'Read more <i class="fas fa-chevron-down text-[10px]"></i>';
            });
        </script>
    <?php endif; ?>
    <?php if($faqs): ?>
        <details class="group rounded-xl bg-white">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-4 text-[15px] font-bold text-jm-dark">
                <span><i class="far fa-circle-question mr-1 text-jm-primary"></i> Frequently asked questions <span class="font-normal text-jm-grey">(<?php echo e(count($faqs)); ?>)</span></span>
                <i class="fas fa-chevron-down text-xs text-jm-grey transition group-open:rotate-180"></i>
            </summary>
            <div class="divide-y divide-slate-200 border-t border-slate-200">
                <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <details class="group/q px-4 py-3">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-semibold text-jm-dark"><?php echo e($f['q']); ?><i class="fas fa-plus text-[10px] text-jm-grey group-open/q:hidden"></i><i class="fas fa-minus hidden text-[10px] text-jm-grey group-open/q:inline"></i></summary>
                        <div class="md-text md-small mt-2 text-[#535357]"><?php echo \App\Support\Markup::render($f['a']); ?></div>
                    </details>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="px-4 py-3"><a href="<?php echo e(route('site.page', 'faqs')); ?>" class="text-sm font-bold uppercase text-jm-primary hover:underline">See all FAQs <i class="fas fa-chevron-right text-[10px]"></i></a></div>
            </div>
        </details>
    <?php endif; ?>
</div>
<?php echo $__env->make('site._md-style', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php if (! $__env->hasRenderedOnce('0311e826-c8b3-46c8-9f88-d806aa677f9b')): $__env->markAsRenderedOnce('0311e826-c8b3-46c8-9f88-d806aa677f9b'); ?>
<style>.md-small { font-size: 13px; line-height: 1.55; } .md-small h1, .md-small h2 { font-size: 1.05rem; margin-top: .9rem; } .md-small h2:first-child { margin-top: 0; } .md-small h3 { font-size: .95rem; }</style>
<?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/shop/_seo.blade.php ENDPATH**/ ?>