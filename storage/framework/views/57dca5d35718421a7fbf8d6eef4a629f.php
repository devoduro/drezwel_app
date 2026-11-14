
<?php $__env->startSection('title', 'About us | ' . \App\Models\Setting::get('business_name')); ?>
<?php $__env->startSection('description', \Illuminate\Support\Str::limit(strtok((string) \App\Models\Setting::get('about_text'), "\n"), 155)); ?>
<?php
    $S = fn ($k) => \App\Models\Setting::get($k);
    $phones = \App\Models\Setting::phones();
    $wa = preg_replace('/\D/', '', (string) $S('whatsapp'));
    $paras = preg_split("/\n\s*\n/", trim((string) $S('about_text')));
    $cats = \App\Models\Product::inStore()->whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category')->pluck('category');
?>
<?php $__env->startSection('content'); ?>
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 py-3 space-y-3">
    <nav class="text-[13px] text-jm-grey">
        <a class="hover:text-jm-primary" href="<?php echo e(route('home')); ?>">Home</a> <i class="fas fa-chevron-right mx-1 text-[8px]"></i>
        <span>Help center</span> <i class="fas fa-chevron-right mx-1 text-[8px]"></i> <span class="text-jm-dark">About us</span>
    </nav>

    <div class="grid items-start gap-3 lg:grid-cols-[250px_1fr]">
        <?php echo $__env->make('site._helpmenu', ['active' => 'about'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="min-w-0 space-y-3">
            
            <section class="relative isolate overflow-hidden rounded-xl bg-jm-dark p-6 text-white sm:p-8">
                <?php if($S('hero_image')): ?>
                    <img src="<?php echo e(asset('uploads/' . $S('hero_image'))); ?>" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover">
                    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950/90 via-slate-950/70 to-slate-950/30"></div>
                <?php else: ?>
                    <div class="absolute inset-0 -z-20 bg-gradient-to-br from-[#1c3041] to-[#111d28]"></div>
                    <div class="pointer-events-none absolute -right-16 -top-20 -z-10 h-64 w-64 rounded-full bg-jm-light/25 blur-3xl"></div>
                <?php endif; ?>
                <span class="inline-block rounded bg-jm-light px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-jm-dark">About us</span>
                <h1 class="mt-3 text-3xl font-bold leading-tight sm:text-4xl"><?php echo e($S('business_name')); ?>: a step above <span class="text-jm-light">class</span>.</h1>
                <p class="mt-2 max-w-xl text-slate-200"><?php echo e($S('tagline')); ?></p>
            </section>

            
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Who we are</div>
                <div class="space-y-3 p-4 leading-relaxed text-[#535357] sm:p-5">
                    <?php $__currentLoopData = $paras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $para): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p><?php echo e($para); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>

            
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Why shop with <?php echo e($S('business_name')); ?></div>
                <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-5">
                    <?php $__currentLoopData = [
                        ['fa-gem', 'Quality first', 'Clothing chosen for how it looks and how it lasts.'],
                        ['fa-truck-fast', 'Delivered to you', 'By rider to your door in Accra, and by station bus or parcel office to other regions.'],
                        ['fa-hand-holding-dollar', 'Pay on delivery', 'See your order first, then pay the rider in cash or MoMo.'],
                        ['fa-location-crosshairs', 'Track every step', 'SMS updates and a delivery code, and you can follow your order online.'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$ic, $t, $d]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex gap-3"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-[#e4eaf0] text-xl text-jm-primary"><i class="fas <?php echo e($ic); ?>"></i></span><div><div class="font-bold"><?php echo e($t); ?></div><div class="text-sm text-jm-grey"><?php echo e($d); ?></div></div></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </section>

            
            <?php if($cats->count()): ?>
                <section class="overflow-hidden rounded-xl bg-white">
                    <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">What we sell</div>
                    <div class="flex flex-wrap gap-2 p-4 sm:p-5">
                        <?php $__currentLoopData = $cats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a href="<?php echo e(route('shop.index', ['category' => $c])); ?>" class="rounded-full border border-slate-300 px-4 py-1.5 text-sm transition hover:border-jm-primary hover:text-jm-primary"><?php echo e($c); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('site.wholesale')); ?>" class="rounded-full border border-slate-300 px-4 py-1.5 text-sm transition hover:border-jm-primary hover:text-jm-primary">Wholesale</a>
                    </div>
                </section>
            <?php endif; ?>

            
            <div class="grid gap-3 md:grid-cols-2">
                <section class="overflow-hidden rounded-xl bg-white">
                    <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Find us</div>
                    <div class="space-y-2 p-4 text-sm">
                        <?php if($S('address')): ?><div class="flex gap-3"><i class="fas fa-location-dot mt-0.5 w-5 text-jm-primary"></i><span><?php echo e($S('address')); ?></span></div><?php endif; ?>
                        <?php if($S('hours')): ?><div class="flex gap-3"><i class="fas fa-clock mt-0.5 w-5 text-jm-primary"></i><span><?php echo e($S('hours')); ?></span></div><?php endif; ?>
                        <?php $__currentLoopData = $phones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$disp, $digits]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a class="flex gap-3 text-jm-primary hover:underline" href="tel:<?php echo e($digits); ?>"><i class="fas fa-phone mt-0.5 w-5"></i><?php echo e($disp); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($S('email')): ?><a class="flex gap-3 text-jm-primary hover:underline" href="mailto:<?php echo e($S('email')); ?>"><i class="fas fa-envelope mt-0.5 w-5"></i><?php echo e($S('email')); ?></a><?php endif; ?>
                        <?php if($S('address')): ?><a target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?php echo e(urlencode($S('address'))); ?>" class="mt-2 flex items-center justify-center gap-2 rounded-lg border border-jm-primary px-4 py-2.5 font-bold uppercase text-jm-primary transition hover:bg-[#e4eaf0]/60"><i class="fas fa-map-location-dot"></i> Open in Google Maps</a><?php endif; ?>
                    </div>
                </section>
                <section class="flex flex-col justify-center rounded-xl bg-jm-primary p-6 text-white">
                    <h2 class="text-xl font-bold">Ready to look a step above?</h2>
                    <p class="mt-1 text-white/90">Browse our latest styles, or call us and we will help you choose.</p>
                    <div class="mt-4 flex flex-wrap gap-3 text-sm font-bold uppercase tracking-wide">
                        <a href="<?php echo e(route('shop.index')); ?>" class="rounded-lg bg-white px-6 py-2.5 text-jm-primary shadow transition hover:bg-slate-100">Shop now</a>
                        <a href="<?php echo e(route('site.contact')); ?>" class="rounded-lg px-5 py-2.5 ring-2 ring-white/70 transition hover:bg-white/15">Contact us</a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.shop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/site/about.blade.php ENDPATH**/ ?>