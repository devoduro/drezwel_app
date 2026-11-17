
<?php $deal = $deal ?? false; ?>
<section class="overflow-hidden rounded-xl <?php echo e($deal ? 'bg-jm-red' : 'bg-white'); ?>">
    <div class="flex items-center justify-between gap-3 px-4 py-3 <?php echo e($deal ? 'text-white' : 'border-b border-slate-200'); ?>">
        <div class="flex items-center gap-3">
            <h2 class="text-[17px] sm:text-xl font-bold leading-tight <?php echo e($deal ? '' : 'text-jm-dark'); ?>"><?php if($deal): ?><i class="fas fa-bolt mr-1"></i><?php endif; ?><?php echo e($title); ?></h2>
            <?php if($deal): ?><span class="hidden sm:inline-flex items-center gap-1.5 rounded-lg bg-white px-2.5 py-1 text-[13px] font-bold text-jm-red"><i class="far fa-clock"></i> Limited time offers</span><?php endif; ?>
        </div>
        <?php if(isset($url)): ?><a href="<?php echo e($url); ?>" class="shrink-0 text-sm font-bold uppercase <?php echo e($deal ? 'text-white hover:underline' : 'text-jm-primary hover:underline'); ?>">See all <i class="fas fa-chevron-right text-[10px]"></i></a><?php endif; ?>
    </div>
    <div class="relative" data-row>
        <div data-scroller class="no-scrollbar flex snap-x gap-2.5 overflow-x-auto scroll-smooth p-3 <?php echo e($deal ? '' : 'bg-white'); ?>">
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo $__env->make('shop._card', ['p' => $p, 'row' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <button type="button" data-prev aria-label="Scroll left" class="absolute left-1 top-1/2 z-10 hidden h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white text-jm-dark shadow-lg ring-1 ring-black/5 hover:bg-slate-50 sm:grid"><i class="fas fa-chevron-left"></i></button>
        <button type="button" data-next aria-label="Scroll right" class="absolute right-1 top-1/2 z-10 hidden h-10 w-10 -translate-y-1/2 place-items-center rounded-full bg-white text-jm-dark shadow-lg ring-1 ring-black/5 hover:bg-slate-50 sm:grid"><i class="fas fa-chevron-right"></i></button>
    </div>
</section>
<?php if (! $__env->hasRenderedOnce('101a318a-5495-4962-99a8-ba2edad90a17')): $__env->markAsRenderedOnce('101a318a-5495-4962-99a8-ba2edad90a17'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.querySelectorAll('[data-row]').forEach(function (box) {
        const sc = box.querySelector('[data-scroller]'), prev = box.querySelector('[data-prev]'), next = box.querySelector('[data-next]');
        const step = () => Math.max(sc.clientWidth * 0.8, 200);
        const overflowing = () => sc.scrollWidth > sc.clientWidth + 4;
        const atEnd = () => sc.scrollLeft + sc.clientWidth >= sc.scrollWidth - 4;
        const arrows = () => { const on = overflowing(); [prev, next].forEach(b => b.style.visibility = on ? 'visible' : 'hidden'); prev.style.opacity = sc.scrollLeft <= 4 ? '.35' : '1'; next.style.opacity = atEnd() ? '.35' : '1'; };
        let paused = false, resumeAt = 0;
        const hold = () => { resumeAt = Date.now() + 8000; };

        prev.addEventListener('click', () => { sc.scrollBy({left: -step(), behavior: 'smooth'}); hold(); });
        next.addEventListener('click', () => { sc.scrollBy({left: step(), behavior: 'smooth'}); hold(); });
        sc.addEventListener('scroll', arrows, {passive: true}); window.addEventListener('resize', arrows); arrows();

        // click-and-drag with a mouse (touch already swipes natively)
        let down = false, moved = false, x0 = 0, left0 = 0;
        sc.addEventListener('pointerdown', e => { if (e.pointerType !== 'mouse') return; down = true; moved = false; x0 = e.clientX; left0 = sc.scrollLeft; });
        window.addEventListener('pointermove', e => {
            if (!down) return;
            const dx = e.clientX - x0;
            if (Math.abs(dx) > 5 && !moved) { moved = true; sc.style.scrollBehavior = 'auto'; sc.style.cursor = 'grabbing'; sc.classList.remove('snap-x'); }
            if (moved) sc.scrollLeft = left0 - dx;
        });
        window.addEventListener('pointerup', () => { if (!down) return; down = false; sc.style.scrollBehavior = ''; sc.style.cursor = ''; sc.classList.add('snap-x'); });
        sc.addEventListener('click', e => { if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; } }, true);

        // gentle auto-scroll: a step every few seconds, back to the start at the end; paused while hovering/touching or off screen
        ['mouseenter', 'focusin', 'touchstart'].forEach(ev => box.addEventListener(ev, () => { paused = true; }, {passive: true}));
        ['mouseleave', 'focusout', 'touchend'].forEach(ev => box.addEventListener(ev, () => { paused = false; hold(); }, {passive: true}));
        if (!reduce) {
            setInterval(function () {
                if (paused || document.hidden || !overflowing() || Date.now() < resumeAt) return;
                const r = box.getBoundingClientRect();
                if (r.bottom < 0 || r.top > innerHeight) return;
                if (atEnd()) sc.scrollTo({left: 0, behavior: 'smooth'}); else sc.scrollBy({left: step() * 0.75, behavior: 'smooth'});
            }, 3500);
        }
    });
});
</script>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/shop/_row.blade.php ENDPATH**/ ?>