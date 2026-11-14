
<?php
    $steps = ['pending' => 'Order placed', 'assigned' => 'Rider assigned', 'out_for_delivery' => 'Out for delivery', 'delivered' => 'Delivered'];
    $keys = array_keys($steps);
    $current = array_search($delivery->status, $keys);
    $failed = in_array($delivery->status, ['undelivered', 'cancelled']);
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $invoice = \Illuminate\Support\Facades\URL::temporarySignedRoute('invoice.public', now()->addDays(30), ['reference' => $delivery->reference]);
?>
<div class="space-y-3" id="detail">
    <section class="overflow-hidden rounded-xl bg-white">
        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200 p-4">
            <div>
                <h2 class="text-[17px] font-bold">Order <?php echo e($delivery->reference); ?></h2>
                <div class="mt-0.5 text-sm text-jm-grey">Placed on <?php echo e($delivery->created_at->format('d-m-Y')); ?><?php if($delivery->items->count()): ?> · <?php echo e($delivery->items->sum('qty')); ?> <?php echo e($delivery->items->sum('qty') === 1 ? 'item' : 'items'); ?><?php endif; ?> · Total: <strong class="text-jm-dark"><?php echo e($m($delivery->amount_due)); ?></strong></div>
            </div>
            <span class="rounded px-2.5 py-1 text-[12px] font-bold uppercase <?php echo e($delivery->status === 'delivered' ? 'bg-green-100 text-green-700' : ($failed ? 'bg-red-100 text-jm-red' : 'bg-[#fdecd5] text-jm-orange')); ?>"><?php echo e(\App\Models\Delivery::STATUSES[$delivery->status] ?? $delivery->status); ?></span>
        </div>

        
        <div class="p-4 sm:p-6">
            <?php if($failed): ?>
                <div class="rounded-lg bg-jm-bg px-4 py-3 text-sm"><?php echo e($delivery->status === 'cancelled' ? 'This order was cancelled.' : 'We could not complete this delivery. We will contact you to reschedule.'); ?></div>
            <?php else: ?>
                <ol class="grid grid-cols-4 text-center">
                    <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $i = array_search($key, $keys); $done = $current !== false && $i <= $current; $now = $current === $i; ?>
                        <li class="relative">
                            <?php if(! $loop->first): ?><span class="absolute right-1/2 top-3 h-0.5 w-full <?php echo e($done ? 'bg-jm-orange' : 'bg-slate-200'); ?>"></span><?php endif; ?>
                            <span class="relative z-10 mx-auto grid h-6 w-6 place-items-center rounded-full text-[11px] font-bold <?php echo e($done ? 'bg-jm-orange text-white' : 'bg-slate-200 text-slate-500'); ?> <?php echo e($now ? 'ring-4 ring-[#fdecd5]' : ''); ?>"><?php echo $done ? '<i class="fas fa-check"></i>' : $i + 1; ?></span>
                            <div class="mt-2 px-1 text-[12px] leading-tight sm:text-[13px] <?php echo e($done ? 'font-bold text-jm-dark' : 'text-jm-grey'); ?>"><?php echo e($label); ?></div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ol>
            <?php endif; ?>

            <?php if($delivery->status === 'out_for_delivery'): ?>
                <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm"><i class="fas fa-shield-halved text-jm-orange"></i> We texted you a <strong>4-digit code</strong>. Give it to the rider <strong>only when you have received your order</strong>.</div>
            <?php endif; ?>
        </div>
    </section>

    
    <section class="overflow-hidden rounded-xl bg-white">
        <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Items in your order</div>
        <div class="divide-y divide-slate-100">
            <?php $__empty_1 = true; $__currentLoopData = $delivery->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex items-center gap-3 p-4">
                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-slate-100"><?php if($i->product->primary_image_url): ?><img src="<?php echo e($i->product->primary_image_url); ?>" alt="" class="h-full w-full object-cover"><?php else: ?><span class="grid h-full place-items-center text-xl text-slate-300"><i class="fas fa-shirt"></i></span><?php endif; ?></div>
                    <div class="min-w-0 flex-1 text-sm"><div class="line-clamp-2 leading-snug"><?php echo e($i->product->name); ?></div><?php if($i->variant && ! $i->variant->is_plain): ?><div class="text-jm-grey"><?php echo e($i->variant->label); ?></div><?php endif; ?><div class="text-jm-grey">Qty: <?php echo e($i->qty); ?></div></div>
                    <div class="shrink-0 text-sm font-medium"><?php echo e($m($i->line_total)); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="p-4 text-sm"><div><?php echo e($delivery->package_description); ?></div><div class="text-jm-grey">Qty: <?php echo e($delivery->items_sent); ?></div></div>
            <?php endif; ?>
        </div>
    </section>

    <div class="grid gap-3 md:grid-cols-2">
        <section class="overflow-hidden rounded-xl bg-white">
            <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Payment information</div>
            <div class="space-y-1.5 p-4 text-sm">
                <div class="font-medium">Pay on delivery</div>
                <div class="flex justify-between text-jm-grey"><span>Items</span><span><?php echo e($m($delivery->total_amount)); ?></span></div>
                <?php if($delivery->delivery_fee > 0): ?><div class="flex justify-between text-jm-grey"><span>Delivery fee</span><span><?php echo e($m($delivery->delivery_fee)); ?></span></div><?php endif; ?>
                <div class="flex justify-between border-t pt-2 font-bold"><span>Total</span><span><?php echo e($m($delivery->amount_due)); ?></span></div>
                <?php if($delivery->amount_paid > 0): ?><div class="flex justify-between text-green-700"><span>Paid</span><span><?php echo e($m($delivery->amount_paid)); ?></span></div><?php endif; ?>
                <?php if($delivery->status !== 'cancelled'): ?><div class="flex justify-between font-bold <?php echo e($delivery->balance > 0 ? 'text-jm-red' : 'text-green-700'); ?>"><span><?php echo e($delivery->balance > 0 ? 'To pay on delivery' : 'Fully paid'); ?></span><span><?php echo e($m($delivery->balance)); ?></span></div><?php endif; ?>
            </div>
        </section>
        <section class="overflow-hidden rounded-xl bg-white">
            <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Delivery information</div>
            <div class="space-y-1.5 p-4 text-sm">
                <div class="font-medium"><?php echo e(\App\Models\Delivery::MODES[$delivery->delivery_mode] ?? 'Door delivery'); ?></div>
                <div class="text-jm-grey"><i class="fas fa-location-dot w-5"></i><?php echo e($delivery->location); ?></div>
                <?php if($delivery->rider && in_array($delivery->status, ['assigned', 'out_for_delivery'])): ?>
                    <div class="mt-2 rounded-lg bg-jm-bg p-3"><div class="text-xs font-bold uppercase text-jm-grey">Your rider</div><div class="font-medium"><?php echo e($delivery->rider->name); ?></div>
                        <?php if($delivery->rider->phone): ?><a class="font-bold text-jm-orange" href="tel:<?php echo e($delivery->rider->phone); ?>"><i class="fas fa-phone"></i> <?php echo e($delivery->rider->phone); ?></a><?php endif; ?></div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    
    <section class="overflow-hidden rounded-xl bg-white">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 px-4 py-3"><span class="text-[15px] font-bold uppercase tracking-wide">Order updates</span>
            <a target="_blank" href="<?php echo e($invoice); ?>" class="inline-flex items-center gap-2 rounded-lg bg-jm-orange px-4 py-2 text-[13px] font-bold uppercase text-white shadow transition hover:bg-[#e07b10]"><i class="fas fa-print"></i> Print invoice</a></div>
        <ul class="divide-y divide-slate-100 text-sm">
            <?php $__currentLoopData = $delivery->events->whereIn('type', ['created', 'assigned', 'started', 'delivered', 'undelivered', 'reopened', 'cancelled'])->reverse(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex justify-between gap-3 px-4 py-3">
                    <span><?php echo e(match($e->type) {
                        'created' => 'Order placed', 'assigned' => 'Rider assigned', 'started' => 'Rider is on the way',
                        'delivered' => 'Delivered', 'undelivered' => 'Delivery attempt failed', 'reopened' => 'Rescheduled', 'cancelled' => 'Cancelled', default => $e->type }); ?></span>
                    <span class="whitespace-nowrap text-jm-grey"><?php echo e($e->created_at->format('d M, g:ia')); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </section>
</div>
<?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/track/_detail.blade.php ENDPATH**/ ?>