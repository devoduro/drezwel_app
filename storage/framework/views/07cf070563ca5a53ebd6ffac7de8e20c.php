<?php $__env->startSection('title', (request('category') ?: (request()->boolean('deals') ? "Today's deals" : 'Shop')) . ' · ' . \App\Models\Setting::get('business_name')); ?>
<?php
    $tel = \App\Models\Setting::phoneLink();
    $filtered = $products !== null;
    $keep = request()->except('page', 'category');
?>
<?php $__env->startSection('content'); ?>
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 py-4">
    <?php if(\App\Models\Setting::get('store_open') !== '1'): ?>
        <div class="mb-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"><i class="fas fa-circle-info"></i> Online ordering is paused right now. Please call us to place an order.</div>
    <?php endif; ?>

    <div class="grid gap-4 lg:grid-cols-[230px_1fr]">
        
        <aside class="hidden lg:block">
            <div class="rounded-xl bg-white p-4">
                <div class="mb-2 text-[13px] font-bold uppercase tracking-wide text-jm-grey">Category</div>
                <ul class="space-y-0.5 text-[14px]">
                    <li><a href="<?php echo e(route('shop.index', request()->except('page', 'category'))); ?>" class="flex justify-between rounded px-2 py-1.5 hover:bg-jm-bg <?php echo e(! request('category') ? 'font-bold text-jm-orange' : ''); ?>">All products <span class="text-jm-grey"><?php echo e($categoryCounts->sum()); ?></span></a></li>
                    <?php $__currentLoopData = $categoryCounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c => $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><a href="<?php echo e(route('shop.index', $keep + ['category' => $c])); ?>" class="flex justify-between rounded px-2 py-1.5 hover:bg-jm-bg <?php echo e(request('category') === $c ? 'font-bold text-jm-orange' : ''); ?>"><?php echo e($c); ?> <span class="text-jm-grey"><?php echo e($n); ?></span></a></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>

            <form method="GET" action="<?php echo e(route('shop.index')); ?>" class="mt-3 rounded-xl bg-white p-4">
                <?php $__currentLoopData = request()->only('q', 'category', 'sort', 'deals'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><input type="hidden" name="<?php echo e($k); ?>" value="<?php echo e($v); ?>"><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="mb-2 text-[13px] font-bold uppercase tracking-wide text-jm-grey">Price (GH₵)</div>
                <div class="flex items-center gap-2">
                    <input type="number" min="0" name="min_price" value="<?php echo e(request('min_price')); ?>" placeholder="Min" class="h-10 w-full min-w-0 rounded-lg border border-slate-300 px-2 text-sm focus:border-jm-orange focus:outline-none">
                    <span class="text-jm-grey">-</span>
                    <input type="number" min="0" name="max_price" value="<?php echo e(request('max_price')); ?>" placeholder="Max" class="h-10 w-full min-w-0 rounded-lg border border-slate-300 px-2 text-sm focus:border-jm-orange focus:outline-none">
                </div>
                <button class="mt-3 h-10 w-full rounded-lg bg-jm-orange text-sm font-bold uppercase text-white transition hover:bg-[#e07b10]">Apply</button>
                <?php if(request()->filled('min_price') || request()->filled('max_price')): ?><a href="<?php echo e(route('shop.index', request()->except('min_price', 'max_price', 'page'))); ?>" class="mt-2 block text-center text-sm text-jm-orange">Clear price</a><?php endif; ?>
            </form>

            <div class="mt-3 rounded-xl bg-white p-4 text-sm">
                <div class="mb-1 font-bold">Prefer to call?</div>
                <p class="text-jm-grey">We take orders by phone too.</p>
                <?php if($tel): ?><a href="tel:<?php echo e($tel); ?>" class="mt-2 inline-flex items-center gap-2 font-bold text-jm-orange"><i class="fas fa-phone"></i> <?php echo e(\App\Models\Setting::get('phone')); ?></a><?php endif; ?>
            </div>
        </aside>

        <div class="min-w-0">
            <?php if($filtered): ?>
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2 rounded-xl bg-white px-4 py-3">
                    <h1 class="text-[17px] font-bold">
                        <?php echo e(request('category') ?: (request()->boolean('deals') ? "Today's deals" : (request()->filled('q') ? 'Results for “' . request('q') . '”' : 'All products'))); ?>

                        <span class="text-sm font-normal text-jm-grey">· <?php echo e($products->total()); ?> <?php echo e(\Illuminate\Support\Str::plural('product', $products->total())); ?> found</span>
                    </h1>
                    <form method="GET" action="<?php echo e(route('shop.index')); ?>" class="flex items-center gap-2 text-sm">
                        <?php $__currentLoopData = request()->except('sort', 'page'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><input type="hidden" name="<?php echo e($k); ?>" value="<?php echo e($v); ?>"><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <label class="text-jm-grey" for="sort">Sort by:</label>
                        <select id="sort" name="sort" onchange="this.form.submit()" class="h-9 rounded-lg border border-slate-300 bg-white px-2 focus:border-jm-orange focus:outline-none">
                            <?php $__currentLoopData = ['new' => 'Newest', 'price_asc' => 'Price: Low to High', 'price_desc' => 'Price: High to Low']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(request('sort', 'new') === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </form>
                </div>
                
                <div class="no-scrollbar mb-3 flex gap-2 overflow-x-auto lg:hidden">
                    <a href="<?php echo e(route('shop.index', $keep)); ?>" class="shrink-0 rounded-full border px-3.5 py-1.5 text-sm <?php echo e(request('category') ? 'bg-white' : 'border-jm-orange bg-jm-orange text-white'); ?>">All</a>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a href="<?php echo e(route('shop.index', $keep + ['category' => $c])); ?>" class="shrink-0 rounded-full border px-3.5 py-1.5 text-sm <?php echo e(request('category') === $c ? 'border-jm-orange bg-jm-orange text-white' : 'bg-white'); ?>"><?php echo e($c); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <?php if($products->count()): ?>
                    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 xl:grid-cols-4"><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo $__env->make('shop._card', ['p' => $p], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>
                    <div class="mt-6"><?php echo e($products->links()); ?></div>
                <?php else: ?>
                    <div class="rounded-xl bg-white p-12 text-center text-jm-grey"><i class="fas fa-shirt mb-3 block text-4xl text-slate-300"></i>No products found. <?php if($tel): ?>Call <a class="font-bold text-jm-orange" href="tel:<?php echo e($tel); ?>"><?php echo e(\App\Models\Setting::get('phone')); ?></a> and we will help.<?php endif; ?></div>
                <?php endif; ?>
            <?php else: ?>
                
                <div class="no-scrollbar mb-3 flex gap-2 overflow-x-auto lg:hidden">
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a href="<?php echo e(route('shop.index', ['category' => $c])); ?>" class="shrink-0 rounded-full border bg-white px-3.5 py-1.5 text-sm"><?php echo e($c); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php echo $__env->make('shop._row', ['title' => $s['title'], 'products' => $s['products'], 'url' => $s['url'], 'deal' => $s['deal'] ?? false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="rounded-xl bg-white p-12 text-center text-jm-grey"><i class="fas fa-shirt mb-3 block text-4xl text-slate-300"></i>New styles are coming soon. <?php if($tel): ?>Call <a class="font-bold text-jm-orange" href="tel:<?php echo e($tel); ?>"><?php echo e(\App\Models\Setting::get('phone')); ?></a> to ask what we have.<?php endif; ?></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.shop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/shop/index.blade.php ENDPATH**/ ?>