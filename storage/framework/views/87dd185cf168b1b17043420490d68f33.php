
<?php $__env->startSection('title', 'Track your delivery · Drezwel Go'); ?>
<?php $__env->startSection('content'); ?>
<div class="text-center mb-5 mt-2">
    <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Drezwel Go" class="mx-auto h-14 w-auto">
    <h1 class="mt-3 text-xl font-bold">Track your delivery</h1>
    <p class="text-sm text-slate-500">Use the reference from your SMS and the phone number you ordered with.</p>
</div>

<form method="GET" action="<?php echo e(route('track')); ?>" class="bg-white rounded-2xl shadow p-5 space-y-3">
    <input name="ref" value="<?php echo e(request('ref')); ?>" placeholder="Reference e.g. DEL-261114-7163" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 uppercase">
    <input name="phone" value="<?php echo e(request('phone')); ?>" placeholder="Phone number e.g. 0241234567" inputmode="tel" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5">
    <button class="w-full rounded-lg bg-gradient-to-br from-slate-900 to-slate-700 text-white py-2.5 font-semibold shadow">Track</button>
</form>

<?php if($searched && ! $delivery): ?>
    <div class="mt-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">We could not find a delivery with those details. Check the reference and phone number.</div>
<?php endif; ?>

<?php if($delivery): ?>
    <?php
        $steps = ['pending' => 'Order received', 'assigned' => 'Rider assigned', 'out_for_delivery' => 'Out for delivery', 'delivered' => 'Delivered'];
        $order = array_keys($steps);
        $current = array_search($delivery->status, $order);
    ?>
    <div class="mt-5 bg-white rounded-2xl shadow p-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="text-xs text-slate-500">Reference</div>
                <div class="font-bold"><?php echo e($delivery->reference); ?></div>
            </div>
            <?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
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
<?php endif; ?>
        </div>
        <div class="mt-3 text-sm text-slate-600"><?php echo e($delivery->package_description); ?> · <?php echo e($delivery->location); ?></div>

        <?php if(in_array($delivery->status, ['undelivered', 'cancelled'])): ?>
            <div class="mt-4 rounded-lg bg-slate-100 px-4 py-3 text-sm">
                <?php echo e($delivery->status === 'cancelled' ? 'This order was cancelled.' : 'We could not complete this delivery. We will contact you to reschedule.'); ?>

            </div>
        <?php else: ?>
            <ol class="mt-5 space-y-4">
                <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $i = array_search($key, $order); $done = $current !== false && $i <= $current; ?>
                    <li class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full grid place-items-center text-xs <?php echo e($done ? 'bg-green-500 text-white' : 'bg-slate-200 text-slate-500'); ?>"><?php echo e($done ? '✓' : $i + 1); ?></span>
                        <span class="<?php echo e($done ? 'font-medium' : 'text-slate-400'); ?>"><?php echo e($label); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ol>
        <?php endif; ?>

        <?php if($delivery->rider && in_array($delivery->status, ['assigned', 'out_for_delivery'])): ?>
            <div class="mt-5 rounded-lg bg-blue-50 border border-blue-100 px-4 py-3 text-sm">
                Your rider: <strong><?php echo e($delivery->rider->name); ?></strong>
                <?php if($delivery->rider->phone): ?> · <a class="text-brand-accent" href="tel:<?php echo e($delivery->rider->phone); ?>"><?php echo e($delivery->rider->phone); ?></a><?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if($delivery->status === 'out_for_delivery'): ?>
            <div class="mt-3 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm">
                We texted you a <strong>4-digit code</strong>. Give it to the rider <strong>only when you have received your order</strong>.
            </div>
        <?php endif; ?>

        <?php if($delivery->balance > 0 && $delivery->status !== 'cancelled'): ?>
            <div class="mt-3 text-sm">Balance to pay: <strong>GH₵ <?php echo e(number_format($delivery->balance, 2)); ?></strong></div>
        <?php endif; ?>

        <h2 class="mt-6 text-sm font-semibold text-slate-500 uppercase tracking-wide">Updates</h2>
        <ul class="mt-2 space-y-2 text-sm">
            <?php $__currentLoopData = $delivery->events->whereIn('type', ['created', 'assigned', 'started', 'delivered', 'undelivered', 'reopened', 'cancelled'])->reverse(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                    <span><?php echo e(match($e->type) {
                        'created' => 'Order received', 'assigned' => 'Rider assigned', 'started' => 'Rider is on the way',
                        'delivered' => 'Delivered', 'undelivered' => 'Delivery attempt failed', 'reopened' => 'Rescheduled', 'cancelled' => 'Cancelled', default => $e->type }); ?></span>
                    <span class="text-slate-400 whitespace-nowrap"><?php echo e($e->created_at->format('d M, g:ia')); ?></span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/track.blade.php ENDPATH**/ ?>