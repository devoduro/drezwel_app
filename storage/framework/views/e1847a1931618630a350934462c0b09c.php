
<?php $__env->startSection('title', 'Your cart · ' . \App\Models\Setting::get('business_name')); ?>
<?php $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2); $tel = \App\Models\Setting::phoneLink(); $count = $lines->sum('qty'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 py-3 space-y-3">
<?php if($errors->has('cart')): ?><div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><?php echo e($errors->first('cart')); ?></div><?php endif; ?>

<?php if($lines->isEmpty()): ?>
    <div class="rounded-xl bg-white p-12 text-center">
        <span class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-jm-bg text-4xl text-slate-300"><i class="fas fa-cart-shopping"></i></span>
        <h1 class="mt-4 text-xl font-bold">Your cart is empty!</h1>
        <p class="mt-1 text-jm-grey">Browse our categories and discover our best deals!</p>
        <a href="<?php echo e(route('shop.index')); ?>" class="mt-5 inline-block rounded-lg bg-jm-primary px-8 py-3 text-sm font-bold uppercase tracking-wide text-white shadow transition hover:bg-[#14212d]">Start shopping</a>
    </div>
<?php else: ?>
    <div class="grid items-start gap-3 lg:grid-cols-[1fr_300px]">
        
        <section class="overflow-hidden rounded-xl bg-white">
            <div class="border-b border-slate-200 px-4 py-3 text-[17px] font-bold">Cart (<?php echo e($count); ?>)</div>
            <div class="divide-y divide-slate-100">
                <?php $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $p = $l->variant->product; $off = $p->discount_percent; ?>
                    <div class="p-4">
                        <div class="flex gap-3 sm:gap-4">
                            <a href="<?php echo e(route('shop.product', $p->slug)); ?>" class="h-[88px] w-[88px] shrink-0 overflow-hidden rounded-lg bg-slate-100 sm:h-24 sm:w-24">
                                <?php if($p->primary_image_url): ?><img src="<?php echo e($p->primary_image_url); ?>" alt="" class="h-full w-full object-cover"><?php else: ?><span class="grid h-full place-items-center text-3xl text-slate-300"><i class="fas fa-shirt"></i></span><?php endif; ?>
                            </a>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <a href="<?php echo e(route('shop.product', $p->slug)); ?>" class="line-clamp-2 text-[15px] leading-snug text-jm-dark hover:text-jm-primary"><?php echo e($p->name); ?></a>
                                        <?php if (! ($l->variant->is_plain)): ?><div class="mt-0.5 text-[13px] text-jm-grey"><?php echo e($l->variant->label); ?></div><?php endif; ?>
                                        <?php if($l->variant->stock_qty <= 5): ?><div class="mt-0.5 text-[12px] text-jm-red">Few units left</div><?php endif; ?>
                                    </div>
                                    <div class="shrink-0 text-right">
                                        <div class="text-[17px] font-medium"><?php echo e($m($l->price)); ?></div>
                                        <?php if($off): ?><div class="text-[12px] text-jm-grey line-through"><?php echo e($m($p->compare_at_price)); ?></div><span class="rounded bg-[#e4eaf0] px-1.5 py-0.5 text-[11px] font-bold text-jm-primary">-<?php echo e($off); ?>%</span><?php endif; ?>
                                    </div>
                                </div>
                                <?php if($l->short): ?><div class="mt-1 text-xs text-amber-700">Only <?php echo e($l->qty); ?> available, so we adjusted the quantity.</div><?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <form method="POST" action="<?php echo e(route('shop.cart.update')); ?>"><?php echo csrf_field(); ?><input type="hidden" name="variant_id" value="<?php echo e($l->variant->id); ?>"><input type="hidden" name="qty" value="0">
                                <button class="inline-flex items-center gap-1.5 text-[13px] font-bold uppercase text-jm-primary hover:underline"><i class="far fa-trash-can"></i> Remove</button>
                            </form>
                            
                            <form method="POST" action="<?php echo e(route('shop.cart.update')); ?>" class="flex items-center gap-3"><?php echo csrf_field(); ?><input type="hidden" name="variant_id" value="<?php echo e($l->variant->id); ?>">
                                <button name="qty" value="<?php echo e($l->qty - 1); ?>" class="grid h-9 w-9 place-items-center rounded bg-jm-primary text-lg font-bold text-white transition hover:bg-[#14212d]" aria-label="Decrease">−</button>
                                <span class="w-6 text-center font-medium"><?php echo e($l->qty); ?></span>
                                <button name="qty" value="<?php echo e($l->qty + 1); ?>" <?php if($l->qty >= min(20, $l->variant->stock_qty)): echo 'disabled'; endif; ?> class="grid h-9 w-9 place-items-center rounded bg-jm-primary text-lg font-bold text-white transition hover:bg-[#14212d] disabled:cursor-not-allowed disabled:opacity-30" aria-label="Increase">+</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="border-t border-slate-200 px-4 py-3"><a href="<?php echo e(route('shop.index')); ?>" class="text-sm font-bold uppercase text-jm-primary hover:underline"><i class="fas fa-chevron-left text-[10px]"></i> Continue shopping</a></div>
        </section>

        
        <aside class="space-y-3 lg:sticky lg:top-36">
            <div class="overflow-hidden rounded-xl bg-white">
                <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Cart summary</div>
                <div class="space-y-2 p-4 text-[15px]">
                    <div class="flex items-center justify-between"><span>Subtotal</span><span class="text-[18px] font-medium"><?php echo e($m($subtotal)); ?></span></div>
                    <div class="flex items-center justify-between text-sm text-jm-grey"><span>Delivery</span><span>Worked out at checkout</span></div>
                    <p class="pt-1 text-xs text-jm-grey">Choose your region and town at checkout to see the delivery fee. Delivery fees are not included in the subtotal.</p>
                </div>
                <div class="border-t border-slate-200 p-3">
                    <a href="<?php echo e(route('shop.checkout')); ?>" class="flex items-center justify-between rounded-lg bg-jm-primary px-4 py-3 text-[15px] font-bold uppercase tracking-wide text-white shadow transition hover:bg-[#14212d]"><span>Checkout</span><span>(<?php echo e($m($total)); ?>)</span></a>
                </div>
            </div>
            <?php if($tel): ?>
                <div class="rounded-xl bg-white p-4 text-sm"><div class="font-bold"><i class="fas fa-phone-volume text-jm-primary"></i> Order by phone</div><a class="font-bold text-jm-primary" href="tel:<?php echo e($tel); ?>"><?php echo e(\App\Models\Setting::get('phone')); ?></a><div class="text-jm-grey">We can place the order for you.</div></div>
            <?php endif; ?>
        </aside>
    </div>
<?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.shop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/shop/cart.blade.php ENDPATH**/ ?>