
<?php $__env->startSection('title', $delivery->reference . ' · Drezwel Go'); ?>
<?php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $input = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm';
    $btn = 'rounded-lg px-3 py-2 text-sm font-semibold';
?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6 !p-5','eyebrow' => $delivery->customer->name . ' · ' . $delivery->location,'title' => $delivery->reference,'subtitle' => $delivery->package_description]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6 !p-5','eyebrow' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($delivery->customer->name . ' · ' . $delivery->location),'title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($delivery->reference),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($delivery->package_description)]); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-flag"></i> <?php echo e($delivery->status_label); ?></span>
        <span><i class="fas fa-coins"></i> <?php echo e($m($delivery->amount_due)); ?></span>
        <span><i class="fas fa-user"></i> <?php echo e($delivery->rider?->name ?? 'No rider yet'); ?></span>
     <?php $__env->endSlot(); ?>
    <a href="<?php echo e(route('manager.deliveries.index')); ?>"><i class="fas fa-arrow-left"></i> Deliveries</a>
    <a href="<?php echo e(route('manager.deliveries.invoice', $delivery)); ?>" target="_blank"><i class="fas fa-print"></i> Invoice</a>
    <?php if($delivery->status === 'delivered' && $delivery->order_type !== 'exchange'): ?>
        <a href="<?php echo e(route('manager.deliveries.create', ['exchange_of' => $delivery->id])); ?>"><i class="fas fa-rotate-left"></i> Return / exchange</a>
    <?php endif; ?>
    <?php if($delivery->status !== 'cancelled'): ?>
        <a href="<?php echo e(route('manager.deliveries.edit', $delivery)); ?>" class="primary"><i class="fas fa-pen"></i> Edit</a>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb08eb6e359b0cda244088e0d0db6136c)): ?>
<?php $attributes = $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c; ?>
<?php unset($__attributesOriginalb08eb6e359b0cda244088e0d0db6136c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb08eb6e359b0cda244088e0d0db6136c)): ?>
<?php $component = $__componentOriginalb08eb6e359b0cda244088e0d0db6136c; ?>
<?php unset($__componentOriginalb08eb6e359b0cda244088e0d0db6136c); ?>
<?php endif; ?>

<div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
        <section class="bg-white rounded-xl p-5 grid sm:grid-cols-2 gap-3 text-sm">
            <div><div class="text-xs text-slate-500">Customer</div><a class="font-medium text-brand-accent" href="<?php echo e(route('manager.customers.show', $delivery->customer)); ?>"><?php echo e($delivery->customer->name); ?></a> · <?php echo e($delivery->customer->phone); ?></div>
            <div><div class="text-xs text-slate-500">Location</div><?php echo e($delivery->location); ?></div>
            <?php if($delivery->exchangeOf): ?><div class="sm:col-span-2 rounded-lg bg-amber-50 px-3 py-2 text-amber-900"><i class="fas fa-rotate-left"></i> Exchange for <a class="font-semibold underline" href="<?php echo e(route('manager.deliveries.show', $delivery->exchangeOf)); ?>"><?php echo e($delivery->exchangeOf->reference); ?></a></div><?php endif; ?>
            <?php if($delivery->exchanges->count()): ?><div class="sm:col-span-2 rounded-lg bg-amber-50 px-3 py-2 text-amber-900"><i class="fas fa-rotate-left"></i> Returns / exchanges against this sale: <?php $__currentLoopData = $delivery->exchanges; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $x): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a class="font-semibold underline" href="<?php echo e(route('manager.deliveries.show', $x)); ?>"><?php echo e($x->reference); ?></a><?php if (! ($loop->last)): ?>, <?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php endif; ?>
            <div><div class="text-xs text-slate-500">Package</div><?php echo e($delivery->package_description); ?> (<?php echo e($delivery->items_sent); ?> item<?php echo e($delivery->items_sent > 1 ? 's' : ''); ?>)</div>
            <div><div class="text-xs text-slate-500">Date / window</div><?php echo e($delivery->delivery_date->format('D d M Y')); ?> <?php echo e($delivery->time_window); ?></div>
            <div><div class="text-xs text-slate-500">Mode · Type</div><?php echo e(\App\Models\Delivery::MODES[$delivery->delivery_mode]); ?> · <?php echo e(\App\Models\Delivery::TYPES[$delivery->order_type]); ?></div>
            <div>
                <div class="text-xs text-slate-500">Rider</div>
                <?php if($delivery->rider): ?>
                    <span class="font-medium"><?php echo e($delivery->rider->name); ?></span>
                    <div class="mt-1 space-y-0.5">
                        <?php if($delivery->rider->phone): ?>
                            <a class="block text-[#1c3041] hover:underline" href="tel:<?php echo e($delivery->rider->phone); ?>"><i class="fas fa-phone w-4"></i> <?php echo e($delivery->rider->phone); ?></a>
                        <?php else: ?>
                            <span class="block text-amber-600 text-xs"><i class="fas fa-triangle-exclamation w-4"></i> No phone on file - add one on the Riders page</span>
                        <?php endif; ?>
                        <?php if($delivery->rider->email): ?>
                            <a class="block text-slate-500 hover:underline" href="mailto:<?php echo e($delivery->rider->email); ?>"><i class="fas fa-envelope w-4"></i> <?php echo e($delivery->rider->email); ?></a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    Not assigned
                <?php endif; ?>
            </div>
            <div class="sm:col-span-2 rounded-lg bg-slate-50 p-3">
                <div class="text-xs text-slate-500 mb-1">Money - the customer pays goods + delivery fee to the rider</div>
                <div class="flex flex-wrap gap-x-6 gap-y-1">
                    <span>Goods <?php echo e($m($delivery->total_amount)); ?></span>
                    <span>+ Delivery fee <?php echo e($m($delivery->delivery_fee)); ?></span>
                    <span class="font-semibold">= Customer pays <?php echo e($m($delivery->amount_due)); ?></span>
                    <span>Paid <?php echo e($m($delivery->amount_paid)); ?></span>
                    <span class="<?php echo e($delivery->balance > 0 ? 'text-red-600 font-semibold' : 'text-green-600'); ?>">Balance <?php echo e($m($delivery->balance)); ?></span>
                </div>
                <?php if($delivery->delivery_fee > 0): ?>
                    <div class="mt-1 text-xs <?php echo e($delivery->rider_fee_status === 'paid' ? 'text-green-700' : 'text-slate-500'); ?>">
                        <i class="fas fa-motorcycle"></i> Rider's fee <?php echo e($delivery->rider_fee_status === 'paid' ? 'paid back ' . $delivery->rider_fee_paid_at?->format('d M, g:ia') : ($delivery->status === 'delivered' ? 'still to be paid back to the rider' : 'is paid back after delivery')); ?>

                    </div>
                <?php endif; ?>
            </div>
            <?php if($delivery->notes): ?><div class="sm:col-span-2"><div class="text-xs text-slate-500">Notes</div><?php echo e($delivery->notes); ?></div><?php endif; ?>
            <?php if($delivery->undelivered_reason): ?><div class="sm:col-span-2 text-red-700"><div class="text-xs">Undelivered reason</div><?php echo e($delivery->undelivered_reason); ?></div><?php endif; ?>
            <?php if($delivery->confirmed_by_manager): ?><div class="sm:col-span-2 text-amber-700 text-xs">Receipt was confirmed by the manager without the customer's code.</div><?php endif; ?>
        </section>

        <?php if($delivery->returned_items->count()): ?>
            <section class="rounded-xl border border-amber-200 bg-amber-50/50 p-5">
                <h2 class="text-slate-800 font-semibold mb-3"><i class="fas fa-rotate-left text-amber-700"></i> Returned by the customer</h2>
                <table class="w-full text-sm">
                    <thead class="text-left text-slate-500 text-xs"><tr><th>Product</th><th class="text-right">Qty</th><th class="text-right">Credit each</th><th class="text-right">Credit</th></tr></thead>
                    <?php $__currentLoopData = $delivery->returned_items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-t border-amber-100"><td class="py-1.5"><?php echo e($i->product->name); ?><?php if($i->variant && ! $i->variant->is_plain): ?> <span class="text-slate-500">(<?php echo e($i->variant->label); ?>)</span><?php endif; ?></td><td class="text-right"><?php echo e(-$i->qty); ?></td><td class="text-right"><?php echo e(number_format($i->unit_price, 2)); ?></td><td class="text-right font-medium text-amber-800">- <?php echo e(number_format(-$i->line_total, 2)); ?></td></tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </table>
            </section>
        <?php endif; ?>

        <?php if($delivery->sent_items->count()): ?>
            <section class="bg-white rounded-xl p-5">
                <h2 class="text-slate-800 font-semibold mb-3"><i class="fas fa-shirt text-[#1c3041]"></i> <?php echo e($delivery->order_type === 'exchange' ? 'New items given to the customer' : 'Products'); ?></h2>
                <table class="w-full text-sm">
                    <thead class="text-left text-slate-500 text-xs"><tr><th>Product</th><th class="text-right">Qty</th><th class="text-right">Price</th><th class="text-right">Total</th></tr></thead>
                    <?php $__currentLoopData = $delivery->sent_items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-t border-slate-100"><td class="py-1.5"><a class="text-[#1c3041] hover:underline" href="<?php echo e(route('manager.products.show', $i->product)); ?>"><?php echo e($i->product->name); ?></a></td><td class="text-right"><?php echo e($i->qty); ?></td><td class="text-right"><?php echo e(number_format($i->unit_price, 2)); ?></td><td class="text-right font-medium"><?php echo e(number_format($i->line_total, 2)); ?></td></tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </table>
            </section>
        <?php endif; ?>

        <?php if($delivery->order_type === 'exchange'): ?>
            <?php $newSum = $delivery->sent_items->sum('line_total'); $retSum = -$delivery->returned_items->sum('line_total'); $net = $newSum - $retSum; ?>
            <section class="rounded-xl border-2 border-[#1c3041] bg-[#f3f6f9] p-5 text-sm">
                <div class="flex justify-between"><span>New items</span><strong><?php echo e($m($newSum)); ?></strong></div>
                <div class="flex justify-between"><span>Returned items</span><strong>- <?php echo e($m($retSum)); ?></strong></div>
                <div class="mt-2 flex justify-between border-t border-slate-300 pt-2 text-base"><span class="font-bold"><?php echo e($net < 0 ? 'Refund to customer' : ($net == 0 ? 'Even swap' : 'Customer pays')); ?></span><strong class="text-lg"><?php echo e($m(abs($net))); ?></strong></div>
                <?php if($delivery->refund_due > 0 && $delivery->status !== 'cancelled'): ?>
                    <form method="POST" action="<?php echo e(route('manager.deliveries.refund', $delivery)); ?>" class="mt-3 flex flex-wrap gap-2 border-t border-slate-300 pt-3"><?php echo csrf_field(); ?>
                        <input type="number" step="0.01" name="amount" value="<?php echo e($delivery->refund_due); ?>" max="<?php echo e($delivery->refund_due); ?>" min="0.01" class="<?php echo e($input); ?> w-28">
                        <select name="method" class="<?php echo e($input); ?> bg-white"><?php $__currentLoopData = \App\Models\Payment::METHODS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>"><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                        <input name="notes" placeholder="Note" class="<?php echo e($input); ?> flex-1 min-w-24">
                        <button class="<?php echo e($btn); ?> bg-amber-600 text-white">Record refund of <?php echo e($m($delivery->refund_due)); ?></button>
                    </form>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <section class="bg-white rounded-xl p-5">
            <h2 class="text-slate-800 font-semibold mb-3">Payments</h2>
            <?php if($delivery->payment_method === 'speso'): ?>
                <?php $ps = ['awaiting' => ['Waiting for the customer to approve the MoMo prompt. Do not send a rider yet.', 'bg-amber-50 text-amber-800'], 'paid' => ['Paid online via Speso' . ($delivery->payment_reference ? ' · ' . $delivery->payment_reference : ''), 'bg-green-50 text-green-800'], 'failed' => ['Online payment not completed. Do not send a rider unless the customer switches to pay on delivery.', 'bg-red-50 text-red-700'], 'abandoned' => ['Online payment never completed; order cancelled', 'bg-slate-100 text-slate-600']][$delivery->payment_status] ?? null; ?>
                <?php if($ps): ?>
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm <?php echo e($ps[1]); ?>">
                        <span><i class="fas fa-mobile-screen-button"></i> <?php echo e($ps[0]); ?><?php if($delivery->payment_mobile && $delivery->payment_status !== 'paid'): ?> · <?php echo e($delivery->payment_mobile); ?><?php endif; ?></span>
                        <?php if(in_array($delivery->payment_status, ['awaiting', 'failed'], true)): ?>
                            <form method="POST" action="<?php echo e(route('manager.deliveries.verify-payment', $delivery)); ?>"><?php echo csrf_field(); ?><button class="rounded-md bg-white px-3 py-1 text-xs font-semibold text-slate-800 ring-1 ring-slate-300 hover:bg-slate-50"><i class="fas fa-rotate"></i> Verify with Speso</button></form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <?php $__empty_1 = true; $__currentLoopData = $delivery->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="flex justify-between text-sm border-b py-1.5"><span><?php echo e($p->paid_at->format('d M, g:ia')); ?> · <?php echo e($p->amount < 0 ? 'Refund · ' : ''); ?><?php echo e(\App\Models\Payment::METHODS[$p->method] ?? $p->method); ?><?php echo e($p->rider ? ' · via ' . $p->rider->name : ''); ?></span><strong class="<?php echo e($p->amount < 0 ? 'text-amber-700' : ''); ?>"><?php echo e($p->amount < 0 ? '- ' . $m(abs($p->amount)) : $m($p->amount)); ?></strong></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <p class="text-sm text-slate-400">No payments yet.</p> <?php endif; ?>
            <?php if($delivery->balance > 0 && $delivery->status !== 'cancelled'): ?>
                <form method="POST" action="<?php echo e(route('manager.deliveries.payment', $delivery)); ?>" class="mt-3 flex flex-wrap gap-2"><?php echo csrf_field(); ?>
                    <input type="number" step="0.01" name="amount" value="<?php echo e($delivery->balance); ?>" max="<?php echo e($delivery->balance); ?>" class="<?php echo e($input); ?> w-28">
                    <select name="method" class="<?php echo e($input); ?> bg-white"><?php $__currentLoopData = \App\Models\Payment::METHODS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>"><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                    <input name="notes" placeholder="Note" class="<?php echo e($input); ?> flex-1 min-w-24">
                    <button class="<?php echo e($btn); ?> bg-green-600 text-white">Record payment</button>
                </form>
            <?php endif; ?>
        </section>

        <section class="bg-white rounded-xl p-5">
            <h2 class="text-slate-800 font-semibold mb-3">Timeline</h2>
            <ol class="space-y-2 text-sm">
                <?php $__currentLoopData = $delivery->events->reverse(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex justify-between gap-3 border-b border-slate-100 pb-1.5"><span><span class="font-medium capitalize"><?php echo e(str_replace('_', ' ', $e->type)); ?></span><?php if($e->note): ?> · <?php echo e($e->note); ?><?php endif; ?> <?php if($e->user): ?><span class="text-slate-400">(<?php echo e($e->user->name); ?>)</span><?php endif; ?></span><span class="text-slate-400 whitespace-nowrap"><?php echo e($e->created_at->format('d M, g:ia')); ?></span></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ol>
        </section>
    </div>

    <div class="space-y-5">
        <?php if(in_array($delivery->status, ['pending', 'assigned'])): ?>
            <section class="bg-white rounded-xl p-5">
                <h2 class="text-slate-800 font-semibold mb-2"><?php echo e($delivery->status === 'pending' ? 'Assign a rider' : 'Reassign rider'); ?></h2>
                <form method="POST" action="<?php echo e(route('manager.deliveries.assign', $delivery)); ?>" class="flex gap-2"><?php echo csrf_field(); ?>
                    <select name="rider_id" required class="<?php echo e($input); ?> bg-white flex-1"><option value="">Choose rider</option><?php $__currentLoopData = $riders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($r->id); ?>" <?php if($delivery->rider_id === $r->id): echo 'selected'; endif; ?>><?php echo e($r->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                    <button class="<?php echo e($btn); ?> bg-brand text-white">Assign</button>
                </form>
                <?php if($delivery->status === 'pending'): ?><p class="text-xs text-slate-500 mt-2">Or leave it: riders can pick it from their "Pick Up" tab.</p><?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if($delivery->status === 'out_for_delivery'): ?>
            <section class="bg-white rounded-xl p-5 space-y-3">
                <h2 class="text-slate-800 font-semibold">Delivery code</h2>
                <p class="text-sm text-slate-500">Sent <?php echo e($delivery->code_sent_at?->diffForHumans() ?? 'never'); ?> · wrong attempts: <?php echo e($delivery->code_attempts); ?>/<?php echo e(\App\Models\Delivery::MAX_CODE_ATTEMPTS); ?></p>
                <form method="POST" action="<?php echo e(route('manager.deliveries.resend', $delivery)); ?>"><?php echo csrf_field(); ?><button class="<?php echo e($btn); ?> bg-brand text-white w-full">Send a new code to customer</button></form>
            </section>
        <?php endif; ?>

        <?php if(in_array($delivery->status, ['assigned', 'out_for_delivery', 'undelivered'])): ?>
            <section class="bg-white rounded-xl p-5">
                <h2 class="text-slate-800 font-semibold mb-1">Confirm without code</h2>
                <p class="text-xs text-slate-500 mb-2">Only if the customer confirmed another way. This is recorded.</p>
                <form method="POST" action="<?php echo e(route('manager.deliveries.confirm', $delivery)); ?>" class="space-y-2"><?php echo csrf_field(); ?>
                    <input name="reason" required placeholder="Reason" class="<?php echo e($input); ?> w-full">
                    <div class="flex gap-2"><input type="number" name="items_kept" min="0" max="<?php echo e($delivery->items_sent); ?>" placeholder="Items kept" class="<?php echo e($input); ?> w-1/2"><input type="number" step="0.01" name="cash" placeholder="Cash collected" class="<?php echo e($input); ?> w-1/2"></div>
                    <button class="<?php echo e($btn); ?> bg-amber-600 text-white w-full" onclick="return confirm('Confirm this delivery without the customer code?')">Confirm delivery</button>
                </form>
            </section>
        <?php endif; ?>

        <section class="bg-white rounded-xl p-5 space-y-3">
            <h2 class="text-slate-800 font-semibold">Other actions</h2>
            <?php if(in_array($delivery->status, ['assigned', 'out_for_delivery'])): ?>
                <form method="POST" action="<?php echo e(route('manager.deliveries.undeliver', $delivery)); ?>" class="flex gap-2"><?php echo csrf_field(); ?>
                    <input name="reason" required placeholder="Why undelivered?" class="<?php echo e($input); ?> flex-1"><button class="<?php echo e($btn); ?> bg-red-600 text-white">Undelivered</button></form>
            <?php endif; ?>
            <?php if(in_array($delivery->status, ['undelivered', 'cancelled'])): ?>
                <form method="POST" action="<?php echo e(route('manager.deliveries.reopen', $delivery)); ?>" class="flex gap-2"><?php echo csrf_field(); ?>
                    <input type="date" name="delivery_date" value="<?php echo e(today()->toDateString()); ?>" class="<?php echo e($input); ?>"><button class="<?php echo e($btn); ?> bg-brand text-white">Reopen</button></form>
            <?php endif; ?>
            <?php if(! in_array($delivery->status, ['delivered', 'cancelled'])): ?>
                <form method="POST" action="<?php echo e(route('manager.deliveries.cancel', $delivery)); ?>"><?php echo csrf_field(); ?><button class="<?php echo e($btn); ?> border border-red-300 text-red-700 w-full" onclick="return confirm('Cancel this delivery?')">Cancel delivery</button></form>
            <?php endif; ?>
            <form method="POST" action="<?php echo e(route('manager.deliveries.destroy', $delivery)); ?>" onsubmit="return confirm('Permanently delete <?php echo e($delivery->reference); ?>? Its payments and history are removed and its items go back to stock. This cannot be undone.')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="<?php echo e($btn); ?> w-full bg-red-600 text-white hover:bg-red-700"><i class="fas fa-trash"></i> Delete record</button></form>
        </section>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/deliveries/show.blade.php ENDPATH**/ ?>