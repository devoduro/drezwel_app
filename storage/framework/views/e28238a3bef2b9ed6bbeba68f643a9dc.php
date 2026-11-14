
<?php $__env->startSection('title', 'Order received · ' . \App\Models\Setting::get('business_name')); ?>
<?php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $tel = \App\Models\Setting::phoneLink();
    $invoice = \Illuminate\Support\Facades\URL::temporarySignedRoute('invoice.public', now()->addDays(30), ['reference' => $delivery->reference]);
?>
<?php $__env->startSection('content'); ?>
<div class="max-w-[1180px] mx-auto px-4 py-6">
<div class="mx-auto max-w-2xl">
    <div class="rounded-xl bg-white p-6 text-center">
        <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-green-100 text-2xl text-green-600"><i class="fas fa-check"></i></div>
        <h1 class="mt-3 text-2xl font-bold">Thank you, <?php echo e($delivery->customer->name); ?>!</h1>
        <p class="mt-1 text-slate-500">Your order <strong class="text-slate-800"><?php echo e($delivery->reference); ?></strong> has been received. We will call you to confirm delivery.</p>
        <p class="mt-1 text-sm text-slate-400">A confirmation SMS is on its way to <?php echo e($delivery->customer->phone); ?>.</p>
    </div>

    <div class="mt-4 rounded-xl bg-white p-5">
        <h2 class="font-semibold mb-3">Order summary</h2>
        <div class="divide-y text-sm">
            <?php $__currentLoopData = $delivery->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex justify-between gap-3 py-2"><span><?php echo e($i->product->name); ?><?php if($i->variant && ! $i->variant->is_plain): ?><span class="text-slate-500"> (<?php echo e($i->variant->label); ?>)</span><?php endif; ?> × <?php echo e($i->qty); ?></span><span class="font-medium"><?php echo e($m($i->line_total)); ?></span></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div class="mt-2 border-t pt-3 text-sm space-y-1">
            <div class="flex justify-between"><span>Items</span><span><?php echo e($m($delivery->total_amount)); ?></span></div>
            <div class="flex justify-between"><span>Delivery</span><span><?php echo e($delivery->delivery_fee > 0 ? $m($delivery->delivery_fee) : 'To be confirmed'); ?></span></div>
            <div class="flex justify-between text-lg font-bold pt-1"><span>Pay on delivery</span><span><?php echo e($m($delivery->amount_due)); ?></span></div>
        </div>
        <div class="mt-3 text-sm text-slate-500"><i class="fas fa-location-dot w-5"></i> <?php echo e($delivery->location); ?></div>
    </div>

    <div class="mt-4 flex flex-wrap justify-center gap-3 text-sm font-semibold">
        <a href="<?php echo e($invoice); ?>" target="_blank" class="rounded-xl bg-jm-orange text-white px-5 py-2.5"><i class="fas fa-print"></i> Print invoice</a>
        <a href="<?php echo e(route('track', ['ref' => $delivery->reference, 'phone' => $delivery->customer->phone])); ?>" class="rounded-xl bg-white px-5 py-2.5"><i class="fas fa-truck-fast"></i> Track my order</a>
        <a href="<?php echo e(route('shop.index')); ?>" class="rounded-xl bg-white px-5 py-2.5">Keep shopping</a>
        <?php if($tel): ?><a href="tel:<?php echo e($tel); ?>" class="rounded-xl bg-white px-5 py-2.5"><i class="fas fa-phone"></i> Call us</a><?php endif; ?>
    </div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.shop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/shop/order.blade.php ENDPATH**/ ?>