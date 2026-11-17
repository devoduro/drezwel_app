
<?php $__env->startSection('title', \App\Models\Setting::get('business_name') . ' | A step above class'); ?>
<?php $__env->startSection('description', \App\Models\Setting::get('tagline') . '. Shop jeans, shirts, streetwear, jackets, hoodies and accessories in Accra, Ghana. Delivered to your door, pay on delivery.'); ?>
<?php
    $S = fn ($k) => \App\Models\Setting::get($k);
    $tel = \App\Models\Setting::phoneLink();
    $wa = preg_replace('/\D/', '', (string) $S('whatsapp'));
    $heroImg = $S('hero_image');
    $icons = ['jean' => 'fa-shirt', 'sneaker' => 'fa-shoe-prints', 'shoe' => 'fa-shoe-prints', 'jacket' => 'fa-vest', 'coat' => 'fa-vest', 'hood' => 'fa-vest-patches', 'shirt' => 'fa-shirt',
        'vest' => 'fa-shirt', 'accessor' => 'fa-glasses', 'belt' => 'fa-glasses', 'active' => 'fa-person-running', 'street' => 'fa-hat-cowboy', 'dress' => 'fa-person-dress', 'cap' => 'fa-hat-cowboy'];
    $iconFor = function ($name) use ($icons) { foreach ($icons as $k => $i) { if (stripos($name, $k) !== false) return $i; } return 'fa-tag'; };
?>
<?php $__env->startSection('content'); ?>
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 py-3 space-y-3">

    
    <div class="grid gap-3 lg:grid-cols-[216px_1fr_216px]">
        <aside class="hidden lg:block rounded-xl bg-white py-1.5 text-[14px]">
            <?php if($deals->count()): ?><a href="<?php echo e(route('shop.index', ['deals' => 1])); ?>" class="flex items-center gap-3 px-4 py-2 font-bold text-jm-red hover:bg-jm-bg"><i class="fas fa-bolt w-4 text-center"></i>Today's deals</a><?php endif; ?>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('shop.index', ['category' => $c->category])); ?>" class="flex items-center gap-3 px-4 py-2 hover:bg-jm-bg hover:text-jm-primary"><i class="fas <?php echo e($iconFor($c->category)); ?> w-4 text-center text-jm-grey"></i><span class="flex-1 truncate"><?php echo e($c->category); ?></span><i class="fas fa-chevron-right text-[9px] text-slate-300"></i></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('site.wholesale')); ?>" class="flex items-center gap-3 px-4 py-2 hover:bg-jm-bg hover:text-jm-primary"><i class="fas fa-boxes-stacked w-4 text-center text-jm-grey"></i><span class="flex-1">Wholesale</span><i class="fas fa-chevron-right text-[9px] text-slate-300"></i></a>
            <a href="<?php echo e(route('shop.index')); ?>" class="flex items-center gap-3 px-4 py-2 font-bold text-jm-primary hover:bg-jm-bg"><i class="fas fa-border-all w-4 text-center"></i>All products</a>
        </aside>

        
        <section class="relative isolate flex min-h-[260px] overflow-hidden rounded-xl bg-jm-dark text-white sm:min-h-[330px]">
            <?php if($heroImg): ?>
                <img src="<?php echo e(asset('uploads/' . $heroImg)); ?>" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover">
                <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950/90 via-slate-950/65 to-slate-950/20"></div>
            <?php else: ?>
                <div class="absolute inset-0 -z-20 bg-gradient-to-br from-[#1c3041] via-[#22394d] to-[#111d28]"></div>
                <div class="pointer-events-none absolute -right-20 -top-24 -z-10 h-80 w-80 rounded-full bg-jm-light/25 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-28 left-1/3 -z-10 h-72 w-72 rounded-full bg-jm-light/10 blur-3xl"></div>
            <?php endif; ?>
            <div class="m-auto w-full max-w-xl px-6 py-8 sm:px-10">
                <span class="inline-block rounded bg-jm-light px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-jm-dark">New arrivals</span>
                <h1 class="mt-3 text-3xl font-bold leading-tight tracking-tight sm:text-5xl drop-shadow">A step above <span class="text-jm-light">class</span>.</h1>
                <p class="mt-3 max-w-md text-base text-slate-200 sm:text-lg"><?php echo e($S('tagline')); ?>. Fresh styles just dropped, delivered to your door.</p>
                <div class="mt-6 flex flex-wrap gap-3 text-sm font-bold uppercase tracking-wide">
                    <a href="<?php echo e(route('shop.index')); ?>" class="rounded-lg bg-white px-6 py-3 text-jm-primary transition hover:bg-slate-100">Shop now</a>
                    <?php if($tel): ?><a href="tel:<?php echo e($tel); ?>" class="rounded-lg bg-white/15 px-5 py-3 ring-1 ring-white/30 backdrop-blur transition hover:bg-white/25"><i class="fas fa-phone"></i> Call to order</a><?php endif; ?>
                </div>
            </div>
        </section>

        <div class="hidden lg:grid gap-3 grid-rows-3">
            <?php if($tel): ?>
                <a href="tel:<?php echo e($tel); ?>" class="flex flex-col justify-center rounded-xl bg-jm-primary p-4 text-white transition hover:bg-[#14212d]"><i class="fas fa-phone-volume text-2xl"></i><div class="mt-1 text-lg font-bold leading-tight">Order by phone</div><div class="text-sm text-white/90"><?php echo e($S('phone')); ?></div></a>
            <?php endif; ?>
            <?php if($wa): ?>
                <a href="https://wa.me/<?php echo e($wa); ?>" target="_blank" rel="noopener" class="flex flex-col justify-center rounded-xl bg-[#25a865] p-4 text-white transition hover:bg-[#1f9158]"><i class="fab fa-whatsapp text-2xl"></i><div class="mt-1 text-lg font-bold leading-tight">Chat on WhatsApp</div><div class="text-sm text-white/90">Ask about a style or size</div></a>
            <?php endif; ?>
            <a href="<?php echo e(route('site.wholesale')); ?>" class="flex flex-col justify-center rounded-xl bg-[#2c4a63] p-4 text-white transition hover:bg-[#355773]"><i class="fas fa-boxes-stacked text-2xl text-jm-light"></i><div class="mt-1 text-lg font-bold leading-tight">Wholesale prices</div><div class="text-sm text-slate-300">Buying in bulk? Ask us</div></a>
        </div>
    </div>

    
    <?php if($categories->count()): ?>
        <div class="no-scrollbar -mx-3 flex gap-3 overflow-x-auto px-3 lg:hidden">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('shop.index', ['category' => $c->category])); ?>" class="flex w-[72px] shrink-0 flex-col items-center gap-1.5 text-center text-[12px] leading-tight">
                    <span class="grid h-14 w-14 place-items-center rounded-full bg-white text-xl text-jm-primary shadow-sm"><i class="fas <?php echo e($iconFor($c->category)); ?>"></i></span><span class="line-clamp-2"><?php echo e($c->category); ?></span></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    
    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <?php $__currentLoopData = [['fa-truck-fast', 'Delivery to your door', 'Accra & other regions'], ['fa-hand-holding-dollar', 'Pay on delivery', 'Cash or MoMo'], ['fa-location-crosshairs', 'Track your order', 'Every step, by SMS'], ['fa-headset', 'Call to order', $S('phone')]]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$ic, $t, $d]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center gap-3 rounded-xl bg-white px-3.5 py-3"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-[#e4eaf0] text-jm-primary"><i class="fas <?php echo e($ic); ?>"></i></span><div class="min-w-0"><div class="truncate text-[14px] font-bold"><?php echo e($t); ?></div><div class="truncate text-xs text-jm-grey"><?php echo e($d); ?></div></div></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <?php if($deals->count()): ?><?php echo $__env->make('shop._row', ['title' => "Today's deals", 'products' => $deals, 'url' => route('shop.index', ['deals' => 1]), 'deal' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php endif; ?>
    <?php if($new->count()): ?>
        <?php echo $__env->make('shop._row', ['title' => 'New arrivals', 'products' => $new, 'url' => route('shop.index', ['sort' => 'new'])], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php else: ?>
        <div class="rounded-xl bg-white p-10 text-center text-jm-grey">New styles are coming soon. <?php if($tel): ?>Call <a class="font-bold text-jm-primary" href="tel:<?php echo e($tel); ?>"><?php echo e($S('phone')); ?></a> to ask what we have.<?php endif; ?></div>
    <?php endif; ?>
    <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo $__env->make('shop._row', ['title' => $r->category, 'products' => $r->products, 'url' => route('shop.index', ['category' => $r->category])], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    
    <div class="grid gap-3 md:grid-cols-2">
        <div class="relative overflow-hidden rounded-xl bg-jm-dark p-6 text-white">
            <i class="fas fa-boxes-stacked absolute -right-1 -top-1 text-8xl text-white/5"></i>
            <h3 class="text-xl font-bold">Buy at wholesale price</h3>
            <p class="mt-1 max-w-sm text-slate-300">Stocking a shop or buying in bulk? Tell us what you need and we will send you wholesale prices.</p>
            <a href="<?php echo e(route('site.wholesale')); ?>" class="mt-4 inline-block rounded-lg bg-white px-5 py-2.5 text-sm font-bold uppercase text-jm-primary transition hover:bg-slate-100">Wholesale orders</a>
        </div>
        <div class="relative overflow-hidden rounded-xl bg-[#168a4f] p-6 text-white">
            <i class="fab fa-whatsapp absolute -right-1 -top-1 text-8xl text-white/10"></i>
            <h3 class="text-xl font-bold">Follow our WhatsApp channel</h3>
            <p class="mt-1 max-w-sm text-white/90">Exclusive deals, new arrivals and giveaways, straight to your phone.</p>
            <?php if($S('whatsapp_channel')): ?><a href="<?php echo e($S('whatsapp_channel')); ?>" target="_blank" rel="noopener" class="mt-4 inline-block rounded-lg bg-white px-5 py-2.5 text-sm font-bold uppercase text-[#168a4f]">Follow on WhatsApp</a><?php endif; ?>
        </div>
    </div>

    
    <section class="grid items-center gap-5 rounded-xl bg-white p-6 md:grid-cols-3">
        <div class="md:col-span-2">
            <h2 class="text-xl font-bold">About <?php echo e($S('business_name')); ?></h2>
            <p class="mt-2 text-[#535357]"><?php echo e(strtok((string) $S('about_text'), "\n")); ?></p>
            <a href="<?php echo e(route('site.about')); ?>" class="mt-3 inline-block text-sm font-bold uppercase text-jm-primary hover:underline">More about us</a>
        </div>
        <div class="space-y-1.5 rounded-lg bg-jm-bg p-4 text-sm">
            <div class="font-bold">Visit or call us</div>
            <?php $__currentLoopData = \App\Models\Setting::phones(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$disp, $digits]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a class="block text-jm-primary" href="tel:<?php echo e($digits); ?>"><i class="fas fa-phone w-5"></i><?php echo e($disp); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($S('address')): ?><div class="text-[#535357]"><i class="fas fa-location-dot w-5"></i><?php echo e($S('address')); ?></div><?php endif; ?>
            <a class="block text-jm-primary" href="<?php echo e(route('site.contact')); ?>"><i class="fas fa-envelope w-5"></i>Send us a message</a>
        </div>
    </section>
</div>
<?php echo $__env->make('shop._seo', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.shop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/site/home.blade.php ENDPATH**/ ?>