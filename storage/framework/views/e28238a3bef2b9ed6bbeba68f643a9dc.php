
<?php $__env->startSection('title', 'Order received · ' . \App\Models\Setting::get('business_name')); ?>
<?php
    use Illuminate\Support\Facades\URL;
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $tel = \App\Models\Setting::phoneLink();
    $invoice = URL::temporarySignedRoute('invoice.public', now()->addDays(30), ['reference' => $delivery->reference]);
    $pay = $delivery->payment_status;                    // null (pay on delivery) | awaiting | paid | failed | abandoned
    $ref = ['reference' => $delivery->reference];
    $field = 'mt-1 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-[15px] focus:border-jm-primary focus:outline-none focus:ring-1 focus:ring-jm-primary';
?>
<?php $__env->startSection('content'); ?>
<div class="max-w-[1180px] mx-auto px-4 py-6">
<div class="mx-auto max-w-2xl">

    <?php if($pay === 'awaiting'): ?>
        <div class="rounded-xl bg-white p-6 text-center" id="waiting">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-[#e4eaf0] text-2xl text-jm-primary"><i class="fas fa-mobile-screen-button fa-beat"></i></div>
            <h1 class="mt-3 text-2xl font-bold">Approve the payment on your phone</h1>
            <p class="mt-1 text-slate-500">We sent a prompt for <strong class="text-slate-800"><?php echo e($m($delivery->balance)); ?></strong> to <strong class="text-slate-800"><?php echo e($delivery->payment_mobile); ?></strong>. Enter your MoMo PIN to pay for order <strong class="text-slate-800"><?php echo e($delivery->reference); ?></strong>.</p>
            <p class="mt-2 text-sm text-slate-400"><?php echo e(\App\Support\MomoApproval::hint($delivery->payment_network)); ?></p>
            <p class="mt-4 text-sm text-jm-grey"><i class="fas fa-circle-notch fa-spin"></i> Waiting for your payment… this page updates by itself.</p>
        </div>
        <script>
            (function () {
                var url = <?php echo json_encode(URL::temporarySignedRoute('shop.order.status', now()->addHours(2), $ref)) ?>, stop = false;
                function check() {
                    if (stop) return;
                    fetch(url, {headers: {'Accept': 'application/json'}}).then(function (r) { return r.json(); }).then(function (d) {
                        if (d.state !== 'awaiting') { stop = true; location.reload(); }
                    }).catch(function () {});
                }
                setInterval(check, 4000);
            })();
        </script>
    <?php elseif($pay === 'failed'): ?>
        <div class="rounded-xl bg-white p-6 text-center">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-red-100 text-2xl text-red-600"><i class="fas fa-triangle-exclamation"></i></div>
            <h1 class="mt-3 text-2xl font-bold">Payment not completed</h1>
            <p class="mt-1 text-slate-500">No money was taken for order <strong class="text-slate-800"><?php echo e($delivery->reference); ?></strong>. Your items are held for a short while. <?php echo e($delivery->pay_now_only ? 'Please try the payment again.' : 'Try again or pay the rider on delivery.'); ?></p>
            <?php if(session('pay_error')): ?><p class="mt-2 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700"><?php echo e(session('pay_error')); ?></p><?php endif; ?>
            <?php $__errorArgs = ['momo_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <?php if(\App\Services\SpesoClient::configured()): ?>
                <form method="POST" action="<?php echo e(URL::temporarySignedRoute('shop.order.pay', now()->addHours(2), $ref)); ?>" class="mt-4 grid gap-3 text-left sm:grid-cols-2"><?php echo csrf_field(); ?>
                    <label class="block text-sm font-medium">Mobile money number<input name="momo_number" value="<?php echo e(old('momo_number', $delivery->payment_mobile)); ?>" inputmode="tel" class="<?php echo e($field); ?>"></label>
                    <label class="block text-sm font-medium">Network
                        <select name="network" class="<?php echo e($field); ?>"><?php $__currentLoopData = \App\Support\MomoApproval::NETWORKS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(old('network', $delivery->payment_network) === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                    <button class="h-12 rounded-lg bg-jm-primary text-[15px] font-bold uppercase tracking-wide text-white hover:bg-[#14212d] sm:col-span-2">Pay <?php echo e($m($delivery->balance)); ?> now</button>
                </form>
            <?php endif; ?>
            <?php if (! ($delivery->pay_now_only)): ?>
            <form method="POST" action="<?php echo e(URL::temporarySignedRoute('shop.order.cod', now()->addHours(2), $ref)); ?>" class="mt-3"><?php echo csrf_field(); ?>
                <button class="text-sm font-bold uppercase text-jm-primary hover:underline">Pay on delivery instead</button>
            </form>
            <?php endif; ?>
        </div>
    <?php elseif($pay === 'abandoned' || $delivery->status === 'cancelled'): ?>
        <div class="rounded-xl bg-white p-6 text-center">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-slate-100 text-2xl text-slate-500"><i class="fas fa-ban"></i></div>
            <h1 class="mt-3 text-2xl font-bold">This order was cancelled</h1>
            <p class="mt-1 text-slate-500">Order <strong class="text-slate-800"><?php echo e($delivery->reference); ?></strong> <?php echo e($pay === 'abandoned' ? 'was not paid in time, so we released the items.' : 'has been cancelled.'); ?> You are welcome to order again.</p>
            <a href="<?php echo e(route('shop.index')); ?>" class="mt-4 inline-block rounded-xl bg-jm-primary px-5 py-2.5 text-sm font-semibold text-white">Back to the shop</a>
        </div>
    <?php else: ?>
        <div class="rounded-xl bg-white p-6 text-center">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-green-100 text-2xl text-green-600"><i class="fas fa-check"></i></div>
            <h1 class="mt-3 text-2xl font-bold">Thank you, <?php echo e($delivery->customer->name); ?>!</h1>
            <?php if($pay === 'paid'): ?>
                <p class="mt-1 text-slate-500">Payment received. Your order <strong class="text-slate-800"><?php echo e($delivery->reference); ?></strong> is confirmed and we will call you to arrange delivery.</p>
            <?php else: ?>
                <p class="mt-1 text-slate-500">Your order <strong class="text-slate-800"><?php echo e($delivery->reference); ?></strong> has been received. We will call you to confirm delivery.</p>
            <?php endif; ?>
            <p class="mt-1 text-sm text-slate-400">A confirmation SMS is on its way to <?php echo e($delivery->customer->phone); ?>.</p>
        </div>
    <?php endif; ?>

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
            <?php if($pay === 'paid'): ?>
                <div class="flex justify-between font-semibold text-green-700"><span><i class="fas fa-circle-check"></i> Paid online (Mobile Money)</span><span><?php echo e($m($delivery->amount_paid)); ?></span></div>
                <div class="flex justify-between text-lg font-bold pt-1"><span>Left to pay</span><span><?php echo e($m($delivery->balance)); ?></span></div>
            <?php else: ?>
                <div class="flex justify-between text-lg font-bold pt-1"><span><?php echo e($pay === 'awaiting' || $pay === 'failed' ? 'Total to pay' : 'Pay on delivery'); ?></span><span><?php echo e($m($delivery->amount_due)); ?></span></div>
            <?php endif; ?>
        </div>
        <div class="mt-3 text-sm text-slate-500"><i class="fas fa-location-dot w-5"></i> <?php echo e($delivery->location); ?></div>
    </div>

    <div class="mt-4 flex flex-wrap justify-center gap-3 text-sm font-semibold">
        <?php if (! (in_array($pay, ['awaiting', 'failed', 'abandoned'], true))): ?>
            <a href="<?php echo e($invoice); ?>" target="_blank" class="rounded-xl bg-jm-primary text-white px-5 py-2.5"><i class="fas fa-print"></i> Print invoice</a>
            <a href="<?php echo e(route('track', ['ref' => $delivery->reference, 'phone' => $delivery->customer->phone])); ?>" class="rounded-xl bg-white px-5 py-2.5"><i class="fas fa-truck-fast"></i> Track my order</a>
        <?php endif; ?>
        <a href="<?php echo e(route('shop.index')); ?>" class="rounded-xl bg-white px-5 py-2.5">Keep shopping</a>
        <?php if($tel): ?><a href="tel:<?php echo e($tel); ?>" class="rounded-xl bg-white px-5 py-2.5"><i class="fas fa-phone"></i> Call us</a><?php endif; ?>
    </div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.shop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/shop/order.blade.php ENDPATH**/ ?>