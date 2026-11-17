<!doctype html>
<html lang="en">
<?php
    $S = fn ($k) => \App\Models\Setting::get($k);
    $biz = $S('business_name');
    $phones = \App\Models\Setting::phones();
    $tel = \App\Models\Setting::phoneLink();
    $wa = preg_replace('/\D/', '', (string) $S('whatsapp'));
    $cartCount = array_sum((array) session('cart', []));
    $staff = auth()->user();
    $staffHome = $staff ? route($staff->isManager() ? 'manager.dashboard' : 'rider.mine') : null;
    $pageTitle = trim($__env->yieldContent('title')) ?: $biz . ' | ' . $S('tagline');
    $pageDesc = trim($__env->yieldContent('description')) ?: $S('tagline') . '. Shop clothing, jeans, shirts, sneakers and accessories online and pay on delivery.';
    $ogImage = trim($__env->yieldContent('og_image')) ?: asset('images/logo.png');
    $socials = array_filter([
        ['fab fa-facebook-f', 'Facebook', $S('facebook')], ['fab fa-instagram', 'Instagram', $S('instagram')], ['fab fa-x-twitter', 'X', $S('x')],
        ['fab fa-tiktok', 'TikTok', $S('tiktok')], ['fab fa-youtube', 'YouTube', $S('youtube')],
    ], fn ($x) => $x[2]);
    $menuCats = \App\Models\Product::inStore()->whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category')->limit(12)->pluck('category');
    $hasDeals = \App\Models\Product::inStore()->onOffer()->where('stock_qty', '>', 0)->exists();
?>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1c3041">
    <title><?php echo e($pageTitle); ?></title>
    <meta name="description" content="<?php echo e($pageDesc); ?>">
    <link rel="canonical" href="<?php echo e(url()->current()); ?>">
    <meta property="og:type" content="website"><meta property="og:site_name" content="<?php echo e($biz); ?>">
    <meta property="og:title" content="<?php echo e($pageTitle); ?>"><meta property="og:description" content="<?php echo e($pageDesc); ?>">
    <meta property="og:url" content="<?php echo e(url()->current()); ?>"><meta property="og:image" content="<?php echo e($ogImage); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/icon.png')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap">
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>tailwind.config = {theme: {extend: {colors: {brand: {DEFAULT: '#1c3041', accent: '#1c3041'}, jm: {primary: '#1c3041', dark: '#1c3041', light: '#8fb8d8', grey: '#75757a', bg: '#f1f1f2', red: '#be1d3e'}}}}}</script>
    <style>
        body{font-family:Ubuntu,-apple-system,BlinkMacSystemFont,"Segoe UI","Helvetica Neue",Arial,sans-serif}
        .line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .no-scrollbar{-ms-overflow-style:none;scrollbar-width:none} .no-scrollbar::-webkit-scrollbar{display:none} html{scroll-behavior:smooth}
    </style>
    <?php echo $__env->yieldPushContent('head'); ?>
    <script type="application/ld+json"><?php echo json_encode(array_filter([
        '@context' => 'https://schema.org', '@type' => 'ClothingStore', 'name' => $biz, 'url' => url('/'), 'image' => asset('images/logo.png'),
        'description' => $S('tagline'), 'telephone' => $phones[0][0] ?? null, 'email' => $S('email') ?: null,
        'address' => $S('address') ? ['@type' => 'PostalAddress', 'streetAddress' => $S('address'), 'addressCountry' => 'GH'] : null,
        'priceRange' => 'GH₵', 'sameAs' => array_values(array_map(fn ($x) => $x[2], $socials)),
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
</head>
<body class="min-h-screen flex flex-col bg-jm-bg text-jm-dark">
    
    <header class="bg-white shadow-sm sticky top-0 z-40">
        <div class="max-w-[1180px] mx-auto px-3 sm:px-4 py-2.5 flex items-center gap-3 sm:gap-6">
            <button type="button" id="navBtn" class="lg:hidden text-2xl leading-none text-jm-dark" aria-label="Menu">☰</button>
            <a href="<?php echo e(route('home')); ?>" class="shrink-0"><img src="<?php echo e(asset('images/logo.png')); ?>" alt="<?php echo e($biz); ?>" class="h-8 sm:h-10 w-auto"></a>

            <form method="GET" action="<?php echo e(route('shop.index')); ?>" class="hidden md:flex flex-1 max-w-2xl">
                <div class="relative flex-1">
                    <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-jm-grey"></i>
                    <input name="q" value="<?php echo e(request('q')); ?>" placeholder="Search products, categories and brands" autocomplete="off"
                           class="h-11 w-full rounded-l-lg border border-r-0 border-slate-300 bg-slate-50 pl-10 pr-3 text-[15px] focus:border-jm-primary focus:bg-white focus:outline-none">
                </div>
                <button class="h-11 rounded-r-lg bg-jm-primary px-6 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-[#14212d]">Search</button>
            </form>

            <nav class="ml-auto flex items-center gap-1 sm:gap-2 text-[15px] font-medium">
                <div class="relative group hidden sm:block">
                    <a href="<?php echo e($staff ? $staffHome : route('login')); ?>" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 hover:bg-jm-bg"><i class="far fa-user text-lg"></i> <?php echo e($staff ? \Illuminate\Support\Str::before($staff->name, ' ') : 'Login'); ?> <i class="fas fa-chevron-down text-[10px]"></i></a>
                    <div class="invisible absolute right-0 top-full w-56 rounded-lg bg-white py-2 text-sm opacity-0 shadow-xl ring-1 ring-black/5 transition group-hover:visible group-hover:opacity-100">
                        <?php if($staff): ?>
                            <a class="block px-4 py-2 font-bold text-jm-primary hover:bg-jm-bg" href="<?php echo e($staffHome); ?>"><i class="fas fa-gauge-high w-5"></i> My dashboard</a>
                            <form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button class="block w-full px-4 py-2 text-left hover:bg-jm-bg"><i class="fas fa-right-from-bracket w-5"></i> Log out</button></form>
                        <?php else: ?>
                            <a class="block px-4 py-2 font-bold text-jm-primary hover:bg-jm-bg" href="<?php echo e(route('login')); ?>"><i class="fas fa-right-to-bracket w-5"></i> Login <span class="font-normal text-jm-grey">(staff &amp; riders)</span></a>
                        <?php endif; ?>
                        <a class="block px-4 py-2 hover:bg-jm-bg" href="<?php echo e(route('track')); ?>"><i class="fas fa-box w-5"></i> My orders</a>
                    </div>
                </div>
                
                <a href="<?php echo e($staff ? $staffHome : route('login')); ?>" class="sm:hidden inline-flex items-center rounded-lg px-2.5 py-2 hover:bg-jm-bg" aria-label="<?php echo e($staff ? 'My dashboard' : 'Login'); ?>"><i class="far fa-user text-xl"></i></a>
                <div class="relative group hidden sm:block">
                    <a href="<?php echo e(route('site.page', 'delivery-info')); ?>" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 hover:bg-jm-bg"><i class="far fa-circle-question text-lg"></i> Help <i class="fas fa-chevron-down text-[10px]"></i></a>
                    <div class="invisible absolute right-0 top-full w-60 rounded-lg bg-white py-2 text-sm opacity-0 shadow-xl ring-1 ring-black/5 transition group-hover:visible group-hover:opacity-100">
                        <a class="block px-4 py-2 hover:bg-jm-bg" href="<?php echo e(route('site.page', 'delivery-info')); ?>">Delivery &amp; payment</a>
                        <a class="block px-4 py-2 hover:bg-jm-bg" href="<?php echo e(route('track')); ?>">Track your order</a>
                        <a class="block px-4 py-2 hover:bg-jm-bg" href="<?php echo e(route('site.page', 'return-policy')); ?>">Returns policy</a>
                        <a class="block px-4 py-2 hover:bg-jm-bg" href="<?php echo e(route('site.contact')); ?>">Contact us</a>
                        <?php if($tel): ?><a class="block px-4 py-2 font-semibold text-jm-primary hover:bg-jm-bg" href="tel:<?php echo e($tel); ?>"><i class="fas fa-phone"></i> Call to order</a><?php endif; ?>
                    </div>
                </div>
                <a href="<?php echo e(route('shop.cart')); ?>" class="relative inline-flex items-center gap-1.5 rounded-lg px-3 py-2 hover:bg-jm-bg">
                    <i class="fas fa-cart-shopping text-lg"></i> <span class="hidden sm:inline">Cart</span>
                    <?php if($cartCount): ?><span class="absolute -top-0.5 left-6 grid h-5 min-w-5 place-items-center rounded-full bg-jm-primary px-1 text-[11px] font-bold text-white"><?php echo e($cartCount); ?></span><?php endif; ?>
                </a>
            </nav>
        </div>
        
        <form method="GET" action="<?php echo e(route('shop.index')); ?>" class="md:hidden px-3 pb-2.5">
            <div class="relative">
                <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-jm-grey"></i>
                <input name="q" value="<?php echo e(request('q')); ?>" placeholder="Search products, categories and brands" class="h-10 w-full rounded-lg border border-slate-300 bg-slate-50 pl-9 pr-3 text-sm focus:border-jm-primary focus:bg-white focus:outline-none">
            </div>
        </form>

        
        <div class="border-t bg-white">
            <nav class="no-scrollbar max-w-[1180px] mx-auto px-3 sm:px-4 flex items-center gap-1 overflow-x-auto text-[14px] font-medium whitespace-nowrap">
                <a href="<?php echo e(route('shop.index')); ?>" class="py-2.5 px-3 hover:text-jm-primary <?php echo e(request()->routeIs('shop.index') && ! request('category') ? 'text-jm-primary' : ''); ?>"><i class="fas fa-border-all mr-1"></i> All products</a>
                <?php if($hasDeals): ?><a href="<?php echo e(route('shop.index', ['deals' => 1])); ?>" class="py-2.5 px-3 font-bold text-jm-red hover:underline"><i class="fas fa-bolt"></i> Today's deals</a><?php endif; ?>
                <?php $__currentLoopData = $menuCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a href="<?php echo e(route('shop.index', ['category' => $c])); ?>" class="py-2.5 px-3 hover:text-jm-primary <?php echo e(request('category') === $c ? 'text-jm-primary' : ''); ?>"><?php echo e($c); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('site.wholesale')); ?>" class="py-2.5 px-3 hover:text-jm-primary">Wholesale</a>
                <a href="<?php echo e(route('site.about')); ?>" class="py-2.5 px-3 hover:text-jm-primary">About</a>
                <a href="<?php echo e(route('site.contact')); ?>" class="py-2.5 px-3 hover:text-jm-primary">Contact</a>
                <a href="<?php echo e($staff ? $staffHome : route('login')); ?>" class="ml-auto py-2.5 px-3 font-bold text-jm-primary hover:underline"><i class="fas fa-right-to-bracket mr-1"></i><?php echo e($staff ? 'My dashboard' : 'Login'); ?></a>
            </nav>
        </div>
        
        <nav id="mobileNav" class="hidden lg:hidden border-t bg-white px-4 py-1 text-[15px]">
            <?php $__currentLoopData = [['home', 'Home', 'fa-house'], ['shop.index', 'Shop', 'fa-bag-shopping'], ['site.wholesale', 'Wholesale', 'fa-boxes-stacked'], ['site.about', 'About us', 'fa-circle-info'], ['site.contact', 'Contact', 'fa-envelope'], ['track', 'Track order / My orders', 'fa-box']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$r, $l, $i]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route($r)); ?>" class="flex items-center gap-3 py-3 border-b border-slate-100 last:border-0"><i class="fas <?php echo e($i); ?> w-5 text-jm-primary"></i><?php echo e($l); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($staff): ?>
                <a href="<?php echo e($staffHome); ?>" class="flex items-center gap-3 py-3 border-b border-slate-100 font-bold text-jm-primary"><i class="fas fa-gauge-high w-5"></i>My dashboard</a>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="flex items-center gap-3 py-3 font-bold text-jm-primary"><i class="fas fa-right-to-bracket w-5"></i>Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="flex-1">
        <div class="max-w-[1180px] mx-auto px-3 sm:px-4 pt-3"><?php if (! (session('cart_added'))): ?><?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php endif; ?></div>
        <?php if(session('cart_added') && ! request()->routeIs('shop.cart')): ?>
            
            <div id="cartToast" role="status" class="fixed inset-x-3 bottom-4 z-50 mx-auto flex max-w-md items-center gap-3 rounded-xl bg-jm-dark px-4 py-3 text-sm text-white shadow-2xl sm:bottom-auto sm:top-24 sm:left-auto sm:right-4 sm:mx-0">
                <i class="fas fa-circle-check text-lg text-green-400"></i>
                <div class="min-w-0 flex-1"><div class="truncate font-semibold"><?php echo e(session('success')); ?></div></div>
                <a href="<?php echo e(route('shop.cart')); ?>" class="shrink-0 rounded-lg bg-white px-3 py-1.5 text-xs font-bold uppercase text-jm-primary">View cart</a>
                <button type="button" onclick="this.parentNode.remove()" class="shrink-0 text-white/60 hover:text-white" aria-label="Close"><i class="fas fa-xmark"></i></button>
            </div>
            <script>setTimeout(function () { var t = document.getElementById('cartToast'); if (t) t.remove(); }, 7000);</script>
        <?php endif; ?>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    
    <footer class="mt-10 bg-jm-dark text-slate-300">
        <div class="bg-[#16232f]"><div class="max-w-[1180px] mx-auto px-4 py-5 grid gap-4 sm:grid-cols-3 text-sm">
            <?php $__currentLoopData = [['fa-truck-fast', 'Delivered to your door', 'In Accra by rider, other regions by station bus'], ['fa-hand-holding-dollar', 'Pay on delivery', 'Cash or MoMo when it arrives'], ['fa-headset', 'We are here to help', $phones ? 'Call ' . $phones[0][0] : 'Contact us anytime']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$ic, $t, $d]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center gap-3"><span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-white/10 text-lg text-jm-light"><i class="fas <?php echo e($ic); ?>"></i></span><div><div class="font-semibold text-white"><?php echo e($t); ?></div><div class="text-slate-400"><?php echo e($d); ?></div></div></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div></div>
        <div class="max-w-[1180px] mx-auto px-4 py-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-4 text-sm">
            <div class="space-y-2">
                <div class="font-bold uppercase tracking-wide text-white">Need help?</div>
                <a class="block hover:text-white" href="<?php echo e(route('site.page', 'delivery-info')); ?>">Delivery &amp; payment</a>
                <a class="block hover:text-white" href="<?php echo e(route('track')); ?>">Track your order</a>
                <a class="block hover:text-white" href="<?php echo e(route('site.page', 'return-policy')); ?>">Returns &amp; exchanges</a>
                <a class="block hover:text-white" href="<?php echo e(route('site.page', 'faqs')); ?>">FAQs</a>
                <a class="block hover:text-white" href="<?php echo e(route('site.contact')); ?>">Contact us</a>
            </div>
            <div class="space-y-2">
                <div class="font-bold uppercase tracking-wide text-white">About <?php echo e($biz); ?></div>
                <a class="block hover:text-white" href="<?php echo e(route('site.about')); ?>">About us</a>
                <a class="block hover:text-white" href="<?php echo e(route('site.wholesale')); ?>">Wholesale orders</a>
                <a class="block hover:text-white" href="<?php echo e(route('site.page', 'terms')); ?>">Terms &amp; conditions</a>
                <a class="block hover:text-white" href="<?php echo e(route('site.page', 'privacy-policy')); ?>">Privacy policy</a>
            </div>
            <div class="space-y-2">
                <div class="font-bold uppercase tracking-wide text-white">Shop</div>
                <a class="block hover:text-white" href="<?php echo e(route('shop.index')); ?>">All products</a>
                <?php $__currentLoopData = $menuCats->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a class="block hover:text-white" href="<?php echo e(route('shop.index', ['category' => $c])); ?>"><?php echo e($c); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="space-y-2">
                <div class="font-bold uppercase tracking-wide text-white">Contact</div>
                <?php $__currentLoopData = $phones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$disp, $digits]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a class="block hover:text-white" href="tel:<?php echo e($digits); ?>"><i class="fas fa-phone w-5 text-jm-light"></i><?php echo e($disp); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($wa): ?><a class="block hover:text-white" href="https://wa.me/<?php echo e($wa); ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp w-5 text-green-400"></i>WhatsApp us</a><?php endif; ?>
                <?php if($S('whatsapp_channel')): ?><a class="block hover:text-white" href="<?php echo e($S('whatsapp_channel')); ?>" target="_blank" rel="noopener"><i class="fas fa-bullhorn w-5 text-jm-light"></i>Follow our WhatsApp channel</a><?php endif; ?>
                <?php if($S('email')): ?><a class="block hover:text-white" href="mailto:<?php echo e($S('email')); ?>"><i class="fas fa-envelope w-5 text-jm-light"></i><?php echo e($S('email')); ?></a><?php endif; ?>
                <?php if($S('address')): ?><div><i class="fas fa-location-dot w-5 text-jm-light"></i><?php echo e($S('address')); ?></div><?php endif; ?>
                <?php if($S('hours')): ?><div><i class="fas fa-clock w-5 text-jm-light"></i><?php echo e($S('hours')); ?></div><?php endif; ?>
                <?php if($socials): ?><div class="flex gap-2 pt-2"><?php $__currentLoopData = $socials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$icon, $name, $url]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a href="<?php echo e($url); ?>" target="_blank" rel="noopener" aria-label="<?php echo e($name); ?>" class="grid h-9 w-9 place-items-center rounded-full bg-white/10 transition hover:bg-jm-light hover:text-jm-dark"><i class="<?php echo e($icon); ?>"></i></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php endif; ?>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="max-w-[1180px] mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                <span>© <?php echo e(date('Y')); ?> <?php echo e($biz); ?>. All rights reserved. Prices in Ghana cedis (GH₵).</span>
                <a href="<?php echo e(route('login')); ?>" class="hover:text-slate-300">Staff login</a>
            </div>
        </div>
    </footer>

    <script>document.getElementById('navBtn').addEventListener('click', () => document.getElementById('mobileNav').classList.toggle('hidden'));</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/layouts/shop.blade.php ENDPATH**/ ?>