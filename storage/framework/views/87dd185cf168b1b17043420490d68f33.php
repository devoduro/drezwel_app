
<?php $__env->startSection('title', ($customer ? 'My orders' : 'Track your order') . ' · ' . \App\Models\Setting::get('business_name')); ?>
<?php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $field = 'h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-[15px] focus:border-jm-primary focus:outline-none focus:ring-1 focus:ring-jm-primary';
    $btn = 'h-11 w-full rounded-lg bg-jm-primary text-[14px] font-bold uppercase tracking-wide text-white shadow transition hover:bg-[#14212d]';
    $tel = \App\Models\Setting::phoneLink();
    $ongoing = ['pending', 'assigned', 'out_for_delivery', 'undelivered'];
    $tab = request('tab', 'ongoing');
    $statusClass = fn ($s) => match ($s) { 'delivered' => 'text-green-700', 'cancelled', 'undelivered' => 'text-jm-red', default => 'text-jm-primary' };
    $statusText = fn ($s) => match ($s) { 'pending' => 'Order placed', 'assigned' => 'Rider assigned', 'out_for_delivery' => 'Out for delivery', 'delivered' => 'Delivered', 'undelivered' => 'Delivery attempt failed', 'cancelled' => 'Cancelled', default => $s };
?>
<?php $__env->startSection('content'); ?>
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 py-3 space-y-3">

<?php if($customer): ?>
    
    <?php
        $counts = ['ongoing' => $orders->whereIn('status', $ongoing)->count(), 'delivered' => $orders->where('status', 'delivered')->count(), 'cancelled' => $orders->where('status', 'cancelled')->count()];
        $shown = $orders->filter(fn ($o) => match ($tab) { 'delivered' => $o->status === 'delivered', 'cancelled' => $o->status === 'cancelled', default => in_array($o->status, $ongoing) });
    ?>
    <div class="grid items-start gap-3 lg:grid-cols-[240px_1fr]">
        <aside class="overflow-hidden rounded-xl bg-white text-[15px]">
            <div class="border-b border-slate-200 p-4"><div class="truncate font-bold"><?php echo e($customer->name); ?></div><div class="text-sm text-jm-grey"><?php echo e($customer->phone); ?></div></div>
            <nav class="py-1">
                <a href="<?php echo e(route('track')); ?>" class="flex items-center gap-3 bg-[#e4eaf0]/50 px-4 py-2.5 font-medium text-jm-primary"><i class="fas fa-box w-5 text-center"></i>Orders</a>
                <a href="<?php echo e(route('shop.index')); ?>" class="flex items-center gap-3 px-4 py-2.5 hover:bg-jm-bg"><i class="fas fa-bag-shopping w-5 text-center text-jm-grey"></i>Keep shopping</a>
                <a href="<?php echo e(route('site.contact')); ?>" class="flex items-center gap-3 px-4 py-2.5 hover:bg-jm-bg"><i class="fas fa-headset w-5 text-center text-jm-grey"></i>Contact us</a>
            </nav>
            <form method="POST" action="<?php echo e(route('track.logout')); ?>" class="border-t border-slate-200 p-3"><?php echo csrf_field(); ?><button class="h-10 w-full rounded-lg border border-jm-primary text-sm font-bold uppercase text-jm-primary transition hover:bg-[#e4eaf0]/60">Sign out</button></form>
        </aside>

        <div class="min-w-0 space-y-3">
        <?php if($delivery): ?>
            <a href="<?php echo e(route('track', ['tab' => $tab])); ?>" class="inline-block text-sm font-bold uppercase text-jm-primary hover:underline"><i class="fas fa-chevron-left text-[10px]"></i> Back to orders</a>
            <?php echo $__env->make('track._detail', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php if($searched): ?><div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">That order is not on your account.</div><?php endif; ?>
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="border-b border-slate-200 px-4 pt-3">
                    <h1 class="pb-2 text-[17px] font-bold">Orders (<?php echo e($orders->count()); ?>)</h1>
                    <div class="flex gap-5 text-[14px] font-medium">
                        <?php $__currentLoopData = ['ongoing' => 'Ongoing', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('track', ['tab' => $k])); ?>" class="-mb-px border-b-2 pb-2.5 <?php echo e($tab === $k ? 'border-jm-primary text-jm-primary' : 'border-transparent text-jm-grey hover:text-jm-dark'); ?>"><?php echo e($l); ?> (<?php echo e($counts[$k]); ?>)</a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <div class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $shown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $first = $o->items->first(); ?>
                        <div class="flex gap-3 p-4 sm:gap-4">
                            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                <?php if($first?->product?->primary_image_url): ?><img src="<?php echo e($first->product->primary_image_url); ?>" alt="" class="h-full w-full object-cover"><?php else: ?><span class="grid h-full place-items-center text-2xl text-slate-300"><i class="fas fa-shirt"></i></span><?php endif; ?>
                            </div>
                            <div class="min-w-0 flex-1 text-sm">
                                <div class="line-clamp-2 text-[15px] leading-snug"><?php echo e($o->package_description); ?></div>
                                <div class="mt-0.5 text-jm-grey">Order <?php echo e($o->reference); ?></div>
                                <span class="mt-1 inline-block rounded px-2 py-0.5 text-[12px] font-bold uppercase <?php echo e($o->status === 'delivered' ? 'bg-green-100 text-green-700' : (in_array($o->status, ['cancelled', 'undelivered']) ? 'bg-red-100 text-jm-red' : 'bg-[#e4eaf0] text-jm-primary')); ?>"><?php echo e($statusText($o->status)); ?></span>
                                <div class="mt-1 text-jm-grey">Placed on <?php echo e($o->created_at->format('d-m-Y')); ?> · <?php echo e($m($o->amount_due)); ?></div>
                            </div>
                            <a href="<?php echo e(route('track', ['ref' => $o->reference, 'tab' => $tab])); ?>" class="self-center shrink-0 text-[13px] font-bold uppercase text-jm-primary hover:underline">See details</a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="p-12 text-center text-jm-grey"><span class="mx-auto mb-3 grid h-16 w-16 place-items-center rounded-full bg-jm-bg text-2xl text-slate-300"><i class="fas fa-box-open"></i></span>
                            You have no <?php echo e($tab); ?> orders.<div class="mt-4"><a href="<?php echo e(route('shop.index')); ?>" class="inline-block rounded-lg bg-jm-primary px-6 py-2.5 text-sm font-bold uppercase text-white">Start shopping</a></div></div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    
    <div class="mx-auto max-w-4xl">
        <h1 class="mb-3 text-xl font-bold">Track your order</h1>
        <div class="grid items-start gap-3 md:grid-cols-2">
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Track an order</div>
                <form method="GET" action="<?php echo e(route('track')); ?>" class="space-y-3 p-4">
                    <p class="text-sm text-jm-grey">Enter the order number from your SMS and the phone number you ordered with.</p>
                    <label class="block text-sm font-medium">Order number<input name="ref" value="<?php echo e(request('ref')); ?>" placeholder="e.g. DEL-261114-7163" required class="<?php echo e($field); ?> mt-1 uppercase"></label>
                    <label class="block text-sm font-medium">Phone number<input name="phone" value="<?php echo e(request('phone')); ?>" placeholder="0241234567" inputmode="tel" required class="<?php echo e($field); ?> mt-1"></label>
                    <button class="<?php echo e($btn); ?>">Track order</button>
                </form>
            </section>

            <section class="overflow-hidden rounded-xl bg-white">
                <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Sign in to see all your orders</div>
                <div class="p-4">
                    <?php if($otpPhone): ?>
                        <p class="mb-3 text-sm text-jm-grey">Enter the 6-digit code we texted to <strong class="text-jm-dark"><?php echo e($otpPhone); ?></strong>.</p>
                        <form method="POST" action="<?php echo e(route('track.verify')); ?>" class="space-y-3"><?php echo csrf_field(); ?>
                            <input name="code" inputmode="numeric" pattern="\d{6}" maxlength="6" autocomplete="one-time-code" required autofocus placeholder="• • • • • •" class="<?php echo e($field); ?> text-center text-2xl font-bold tracking-[0.4em]">
                            <button class="<?php echo e($btn); ?>">Sign in</button>
                        </form>
                        <div class="mt-3 flex justify-between text-sm">
                            <form method="POST" action="<?php echo e(route('track.code')); ?>"><?php echo csrf_field(); ?><input type="hidden" name="phone" value="<?php echo e($otpPhone); ?>"><button class="font-medium text-jm-primary hover:underline">Resend code</button></form>
                            <form method="POST" action="<?php echo e(route('track.number')); ?>"><?php echo csrf_field(); ?><button class="text-jm-grey hover:underline">Use a different number</button></form>
                        </div>
                    <?php else: ?>
                        <p class="mb-3 text-sm text-jm-grey">Use the phone number you ordered with. We will text you a code, with no password needed.</p>
                        <form method="POST" action="<?php echo e(route('track.code')); ?>" class="space-y-3"><?php echo csrf_field(); ?>
                            <label class="block text-sm font-medium">Phone number<input name="phone" value="<?php echo e(old('phone')); ?>" inputmode="tel" required placeholder="0241234567" class="<?php echo e($field); ?> mt-1"></label>
                            <button class="<?php echo e($btn); ?>">Send me a code</button>
                        </form>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <?php if($searched && ! $delivery): ?>
            <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">We could not find an order with those details. Check the order number and phone number.</div>
        <?php endif; ?>
        <?php if($delivery): ?><div class="mt-3"><?php echo $__env->make('track._detail', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div><?php endif; ?>
        <?php if($tel): ?><p class="mt-4 text-center text-sm text-jm-grey">Need help with an order? Call <a class="font-bold text-jm-primary" href="tel:<?php echo e($tel); ?>"><?php echo e(\App\Models\Setting::get('phone')); ?></a></p><?php endif; ?>
    </div>
<?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.shop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/track.blade.php ENDPATH**/ ?>