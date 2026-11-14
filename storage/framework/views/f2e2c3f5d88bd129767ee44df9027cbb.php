
<?php
    $items = [
        ['contact', route('site.contact'), 'Contact us', 'fa-headset'],
        ['wholesale', route('site.wholesale'), 'Wholesale orders', 'fa-boxes-stacked'],
        ['delivery-info', route('site.page', 'delivery-info'), 'Delivery & payment', 'fa-truck-fast'],
        ['return-policy', route('site.page', 'return-policy'), 'Return policy', 'fa-rotate-left'],
        ['track', route('track'), 'Track your order', 'fa-location-crosshairs'],
        ['about', route('site.about'), 'About us', 'fa-circle-info'],
        ['terms', route('site.page', 'terms'), 'Terms & conditions', 'fa-file-contract'],
        ['privacy-policy', route('site.page', 'privacy-policy'), 'Privacy policy', 'fa-shield-halved'],
    ];
?>
<aside class="hidden overflow-hidden rounded-xl bg-white lg:block">
    <div class="border-b border-slate-200 px-4 py-3 text-[13px] font-bold uppercase tracking-wide text-jm-grey">Help center</div>
    <nav class="py-1 text-[14px]">
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$key, $url, $label, $icon]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e($url); ?>" class="flex items-center gap-3 px-4 py-2.5 <?php echo e($active === $key ? 'bg-[#fdecd5]/60 font-bold text-jm-orange' : 'hover:bg-jm-bg'); ?>"><i class="fas <?php echo e($icon); ?> w-4 text-center <?php echo e($active === $key ? '' : 'text-jm-grey'); ?>"></i><?php echo e($label); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </nav>
</aside>
<?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/site/_helpmenu.blade.php ENDPATH**/ ?>