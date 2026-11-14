
<?php $deal = $deal ?? false; ?>
<section class="overflow-hidden rounded-xl <?php echo e($deal ? 'bg-jm-red' : 'bg-white'); ?>">
    <div class="flex items-center justify-between gap-3 px-4 py-3 <?php echo e($deal ? 'text-white' : 'border-b border-slate-200'); ?>">
        <div class="flex items-center gap-3">
            <h2 class="text-[17px] sm:text-xl font-bold leading-tight <?php echo e($deal ? '' : 'text-jm-dark'); ?>"><?php if($deal): ?><i class="fas fa-bolt mr-1"></i><?php endif; ?><?php echo e($title); ?></h2>
            <?php if($deal): ?><span class="hidden sm:inline-flex items-center gap-1.5 rounded-lg bg-white px-2.5 py-1 text-[13px] font-bold text-jm-red"><i class="far fa-clock"></i> Limited time offers</span><?php endif; ?>
        </div>
        <?php if(isset($url)): ?><a href="<?php echo e($url); ?>" class="shrink-0 text-sm font-bold uppercase <?php echo e($deal ? 'text-white hover:underline' : 'text-jm-orange hover:underline'); ?>">See all <i class="fas fa-chevron-right text-[10px]"></i></a><?php endif; ?>
    </div>
    <div class="no-scrollbar flex snap-x gap-2.5 overflow-x-auto p-3 <?php echo e($deal ? '' : 'bg-white'); ?>">
        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo $__env->make('shop._card', ['p' => $p, 'row' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</section>
<?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/shop/_row.blade.php ENDPATH**/ ?>