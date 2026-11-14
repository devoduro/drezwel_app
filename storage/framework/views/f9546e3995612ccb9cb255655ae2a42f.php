<?php $__env->startSection('title', 'Checkout · ' . \App\Models\Setting::get('business_name')); ?>
<?php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $field = 'mt-1 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-[15px] focus:border-jm-orange focus:outline-none focus:ring-1 focus:ring-jm-orange';
    $open = \App\Models\Setting::get('store_open') === '1';
    $tel = \App\Models\Setting::phoneLink();
    $count = $lines->sum('qty');
    $step = 'grid h-6 w-6 shrink-0 place-items-center rounded-full bg-jm-orange text-[13px] font-bold text-white';
?>
<?php $__env->startSection('content'); ?>
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 py-3 space-y-3">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">Checkout</h1>
        <a href="<?php echo e(route('shop.cart')); ?>" class="text-sm font-bold uppercase text-jm-orange hover:underline"><i class="fas fa-chevron-left text-[10px]"></i> Back to cart</a>
    </div>
    <?php if(! $open): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">Online ordering is paused. Please call <?php echo e(\App\Models\Setting::get('phone') ?: 'us'); ?> to place your order.</div><?php endif; ?>

    <form method="POST" action="<?php echo e(route('shop.checkout.place')); ?>" class="grid items-start gap-3 lg:grid-cols-[1fr_320px]"><?php echo csrf_field(); ?>
        <div class="space-y-3">
            
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3"><span class="<?php echo e($step); ?>">1</span><h2 class="text-[15px] font-bold uppercase tracking-wide">Customer address</h2></div>
                <div class="grid gap-4 p-4 sm:grid-cols-2">
                    <label class="block text-sm font-medium">Full name<input name="name" value="<?php echo e(old('name')); ?>" required autocomplete="name" class="<?php echo e($field); ?>"></label>
                    <label class="block text-sm font-medium">Phone number<input name="phone" value="<?php echo e(old('phone')); ?>" required inputmode="tel" autocomplete="tel" placeholder="0241234567" class="<?php echo e($field); ?>"></label>
                    <label class="block text-sm font-medium sm:col-span-2">Delivery address<input name="location" value="<?php echo e(old('location')); ?>" required autocomplete="street-address" placeholder="Area, street, landmark, house number" class="<?php echo e($field); ?>"></label>
                    <label class="block text-sm font-medium sm:col-span-2">Additional information (optional)<textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[15px] focus:border-jm-orange focus:outline-none focus:ring-1 focus:ring-jm-orange" placeholder="Anything the rider should know?"><?php echo e(old('notes')); ?></textarea></label>
                    <p class="text-xs text-jm-grey sm:col-span-2"><i class="fas fa-circle-info"></i> We text your order updates and delivery code to this number, and the rider will call it.</p>
                </div>
                <div style="position:absolute;left:-9999px" aria-hidden="true"><label>Company website<input name="company_website" tabindex="-1" autocomplete="off"></label></div>
            </section>

            
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3"><span class="<?php echo e($step); ?>">2</span><h2 class="text-[15px] font-bold uppercase tracking-wide">Delivery details</h2></div>
                <div class="p-4">
                    <div class="flex items-start gap-3 rounded-lg border-2 border-jm-orange bg-[#fffaf4] p-3">
                        <i class="fas fa-truck-fast mt-0.5 text-xl text-jm-orange"></i>
                        <div class="text-sm"><div class="font-bold">Door delivery</div>
                            <div class="text-[#535357]">Delivery fee: <strong><?php echo e($fee > 0 ? $m($fee) : 'confirmed when we call you'); ?></strong></div>
                            <div class="text-jm-grey">Accra by rider. For other regions we send by station bus or parcel office. We call to confirm before dispatch.</div></div>
                    </div>

                    <div class="mt-4 text-[13px] font-bold uppercase tracking-wide text-jm-grey">Shipment (<?php echo e($count); ?> <?php echo e($count === 1 ? 'item' : 'items'); ?>)</div>
                    <div class="mt-2 divide-y divide-slate-100 rounded-lg border border-slate-200">
                        <?php $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $p = $l->variant->product; ?>
                            <div class="flex items-center gap-3 p-3">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                    <?php if($p->primary_image_url): ?><img src="<?php echo e($p->primary_image_url); ?>" alt="" class="h-full w-full object-cover"><?php else: ?><span class="grid h-full place-items-center text-xl text-slate-300"><i class="fas fa-shirt"></i></span><?php endif; ?>
                                </div>
                                <div class="min-w-0 flex-1 text-sm"><div class="line-clamp-2 leading-snug"><?php echo e($p->name); ?></div><?php if (! ($l->variant->is_plain)): ?><div class="text-jm-grey"><?php echo e($l->variant->label); ?></div><?php endif; ?><div class="text-jm-grey">Qty: <?php echo e($l->qty); ?></div></div>
                                <div class="shrink-0 text-right text-sm font-medium"><?php echo e($m($l->qty * $l->price)); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <a href="<?php echo e(route('shop.cart')); ?>" class="mt-3 inline-block text-sm font-bold uppercase text-jm-orange hover:underline">Modify cart</a>
                </div>
            </section>

            
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3"><span class="<?php echo e($step); ?>">3</span><h2 class="text-[15px] font-bold uppercase tracking-wide">Payment method</h2></div>
                <div class="p-4">
                    <label class="flex items-start gap-3 rounded-lg border-2 border-jm-orange bg-[#fffaf4] p-3">
                        <input type="radio" checked class="mt-1 accent-[#f68b1e]" aria-label="Pay on delivery">
                        <div class="text-sm"><div class="font-bold">Pay on delivery <span class="ml-1 rounded bg-green-100 px-1.5 py-0.5 text-[11px] font-bold text-green-700">Available</span></div>
                            <div class="text-[#535357]">Pay the rider in cash or MoMo when your order arrives. Check your items first.</div></div>
                    </label>
                    <div class="mt-3 rounded-lg bg-jm-bg p-3 text-xs text-[#535357]"><i class="fas fa-shield-halved text-jm-orange"></i> When your rider sets off, you get a 4-digit code by SMS. Give it to the rider <strong>only after you have received your order</strong>.</div>
                </div>
            </section>
        </div>

        
        <aside class="space-y-3 lg:sticky lg:top-36">
            <div class="overflow-hidden rounded-xl bg-white">
                <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Order summary</div>
                <div class="space-y-2 p-4 text-[15px]">
                    <div class="flex justify-between"><span>Item's total (<?php echo e($count); ?>)</span><span><?php echo e($m($subtotal)); ?></span></div>
                    <div class="flex justify-between"><span>Delivery fees</span><span><?php echo e($fee > 0 ? $m($fee) : 'To be confirmed'); ?></span></div>
                    <div class="flex items-center justify-between border-t pt-3"><span class="font-bold">Total</span><span class="text-[20px] font-bold"><?php echo e($m($total)); ?></span></div>
                </div>
                <div class="border-t border-slate-200 p-3">
                    <button <?php if(! $open): echo 'disabled'; endif; ?> class="h-12 w-full rounded-lg bg-jm-orange text-[15px] font-bold uppercase tracking-wide text-white shadow transition hover:bg-[#e07b10] disabled:cursor-not-allowed disabled:opacity-40">Confirm order</button>
                    <a href="<?php echo e(route('shop.cart')); ?>" class="mt-2 block text-center text-sm font-bold uppercase text-jm-orange hover:underline">Modify cart</a>
                    <p class="mt-3 text-center text-[11px] leading-snug text-jm-grey">By placing your order you accept our <a class="text-jm-orange hover:underline" href="<?php echo e(route('site.page', 'terms')); ?>" target="_blank">Terms &amp; conditions</a> and <a class="text-jm-orange hover:underline" href="<?php echo e(route('site.page', 'return-policy')); ?>" target="_blank">Return policy</a>.</p>
                </div>
            </div>
            <?php if($tel): ?><div class="rounded-xl bg-white p-4 text-sm"><div class="font-bold"><i class="fas fa-headset text-jm-orange"></i> Need help?</div><div class="text-jm-grey">Call <a class="font-bold text-jm-orange" href="tel:<?php echo e($tel); ?>"><?php echo e(\App\Models\Setting::get('phone')); ?></a></div></div><?php endif; ?>
        </aside>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.shop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/shop/checkout.blade.php ENDPATH**/ ?>