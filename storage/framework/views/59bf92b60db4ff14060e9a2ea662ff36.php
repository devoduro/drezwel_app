
<?php $__env->startSection('title', $delivery->reference . ' · Drezwel Go'); ?>
<?php $input = 'w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]'; $mine = $delivery->rider_id === auth()->id(); ?>
<?php $__env->startSection('content'); ?>
<a href="<?php echo e(url()->previous()); ?>" class="text-sm text-slate-500">← Back</a>
<div class="bg-white rounded-xl p-4 mt-2">
    <div class="flex justify-between items-start"><div><div class="font-bold text-lg"><?php echo e($delivery->customer->name); ?></div><div class="text-xs text-slate-500"><?php echo e($delivery->reference); ?></div></div><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $delivery->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($delivery->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?></div>
    <dl class="mt-3 text-sm space-y-1.5">
        <div><dt class="inline text-slate-500">Location: </dt><dd class="inline"><?php echo e($delivery->location); ?></dd></div>
        <div><dt class="inline text-slate-500">Package: </dt><dd class="inline"><?php echo e($delivery->package_description); ?> (<?php echo e($delivery->items_sent); ?> item<?php echo e($delivery->items_sent > 1 ? 's' : ''); ?>) · <?php echo e(\App\Models\Delivery::TYPES[$delivery->order_type]); ?></dd></div>
        <div><dt class="inline text-slate-500">When: </dt><dd class="inline"><?php echo e($delivery->delivery_date->format('D d M')); ?> <?php echo e($delivery->time_window); ?></dd></div>
        <div><dt class="inline text-slate-500">To collect: </dt><dd class="inline font-semibold"><?php echo e($delivery->balance > 0 ? 'GH₵ ' . number_format($delivery->balance, 2) : 'Nothing - fully paid'); ?></dd>
            <div class="text-xs text-slate-500 mt-0.5">Goods GH₵ <?php echo e(number_format($delivery->total_amount, 2)); ?> + delivery fee GH₵ <?php echo e(number_format($delivery->delivery_fee, 2)); ?>. Hand it all to the business; your delivery fee is paid back to you.</div></div>
        <?php if($delivery->notes): ?><div><dt class="inline text-slate-500">Notes: </dt><dd class="inline"><?php echo e($delivery->notes); ?></dd></div><?php endif; ?>
    </dl>
    <?php if($mine): ?>
        <a href="tel:<?php echo e($delivery->customer->phone); ?>" class="mt-4 block text-center rounded-lg border border-brand-accent text-brand-accent py-2.5 font-semibold">📞 Call <?php echo e($delivery->customer->phone); ?></a>
    <?php endif; ?>
</div>

<?php if(! $mine && $delivery->status === 'pending'): ?>
    <form method="POST" action="<?php echo e(route('rider.claim', $delivery)); ?>" class="mt-4"><?php echo csrf_field(); ?><button class="w-full rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white py-3 font-semibold transition shadow">Pick this delivery</button></form>
<?php endif; ?>

<?php if($mine && $delivery->status === 'assigned'): ?>
    <form method="POST" action="<?php echo e(route('rider.start', $delivery)); ?>" class="mt-4"><?php echo csrf_field(); ?>
        <button class="w-full rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white py-3 font-semibold transition shadow">Start delivery</button>
        <p class="text-xs text-slate-500 mt-2 text-center">Tap this when you leave with the order.</p>
    </form>
    <form method="POST" action="<?php echo e(route('rider.release', $delivery)); ?>" class="mt-3"><?php echo csrf_field(); ?><button class="w-full text-sm text-slate-500 py-2" onclick="return confirm('Hand this delivery back?')">Release this delivery</button></form>
<?php endif; ?>

<?php if($mine && $delivery->status === 'out_for_delivery'): ?>
    <form method="POST" action="<?php echo e(route('rider.deliver', $delivery)); ?>" class="mt-4 bg-white rounded-xl p-4 space-y-3"><?php echo csrf_field(); ?>
        <h2 class="font-semibold">Delivered?</h2>
        <p class="text-sm text-slate-500">Tap <strong>Delivered</strong> once the customer has received the order. They get a text message to say so.</p>
        <?php if($delivery->items_sent > 1 || $delivery->order_type !== 'standard'): ?>
            <label class="block text-sm font-medium">Items the customer kept (of <?php echo e($delivery->items_sent); ?>)
                <input type="number" name="items_kept" min="0" max="<?php echo e($delivery->items_sent); ?>" value="<?php echo e($delivery->items_sent); ?>" class="<?php echo e($input); ?> mt-1"></label>
        <?php endif; ?>
        <?php if($delivery->balance > 0): ?>
            <label class="block text-sm font-medium">Cash collected (GH₵) - owed <?php echo e(number_format($delivery->balance, 2)); ?>

                <input type="number" step="0.01" min="0" max="<?php echo e($delivery->balance); ?>" name="cash" value="<?php echo e($delivery->balance); ?>" class="<?php echo e($input); ?> mt-1"></label>
        <?php endif; ?>
        <button class="w-full rounded-lg bg-[#8cc152] hover:bg-[#74a83b] text-white py-3 text-lg font-semibold transition shadow" onclick="return confirm('Has the customer received the order? They will be sent a text message.')"><i class="fas fa-circle-check"></i> Delivered</button>
    </form>
    <form method="POST" action="<?php echo e(route('rider.undeliver', $delivery)); ?>" class="mt-2 bg-white rounded-xl p-4 space-y-2"><?php echo csrf_field(); ?>
        <h2 class="font-semibold text-sm">Could not deliver?</h2>
        <input name="reason" required placeholder="Reason (e.g. customer unreachable)" class="<?php echo e($input); ?>">
        <button class="w-full rounded-lg border border-red-300 text-red-700 py-2.5 font-semibold" onclick="return confirm('Mark this delivery as undelivered?')">Mark undelivered</button>
    </form>
<?php endif; ?>

<?php if($mine && $delivery->status === 'assigned'): ?>
    <form method="POST" action="<?php echo e(route('rider.undeliver', $delivery)); ?>" class="mt-2 bg-white rounded-xl p-4 space-y-2"><?php echo csrf_field(); ?>
        <input name="reason" required placeholder="Reason, if you cannot deliver" class="<?php echo e($input); ?>">
        <button class="w-full rounded-lg border border-red-300 text-red-700 py-2.5 font-semibold">Mark undelivered</button>
    </form>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/rider/show.blade.php ENDPATH**/ ?>