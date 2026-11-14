
<?php $__env->startSection('title', 'Rider cash & items · Drezwel Go'); ?>
<?php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $input = 'rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-sm';
    $held = $riders->sum(fn ($r) => max($r->c['cash_held'], 0));
    $items = $riders->sum(fn ($r) => $r->c['items_pending']);
    $outstanding = $riders->where('c.cleared', false)->count();
    $feesOwed = $riders->sum(fn ($r) => $r->c['fees_owed']);
?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => 'Reconciliation','title' => 'Rider cash & items','subtitle' => 'Riders collect goods + delivery fee from the customer and hand it all in. Receive it here, then pay the rider their delivery fees back.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => 'Reconciliation','title' => 'Rider cash & items','subtitle' => 'Riders collect goods + delivery fee from the customer and hand it all in. Receive it here, then pay the rider their delivery fees back.']); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-wallet"></i> <?php echo e($m($held)); ?> held</span>
        <span><i class="fas fa-box-open"></i> <?php echo e($items); ?> items due back</span>
     <?php $__env->endSlot(); ?>
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

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => $held > 0 ? 'red' : 'green','icon' => 'fa-wallet','title' => 'Cash with riders','value' => $m($held),'sub' => 'Not yet handed in']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($held > 0 ? 'red' : 'green'),'icon' => 'fa-wallet','title' => 'Cash with riders','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($held)),'sub' => 'Not yet handed in']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b)): ?>
<?php $attributes = $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b; ?>
<?php unset($__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b)): ?>
<?php $component = $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b; ?>
<?php unset($__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => $items > 0 ? 'orange' : 'green','icon' => 'fa-box-open','title' => 'Items due back','value' => $items,'sub' => 'Pending check-in']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($items > 0 ? 'orange' : 'green'),'icon' => 'fa-box-open','title' => 'Items due back','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($items),'sub' => 'Pending check-in']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b)): ?>
<?php $attributes = $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b; ?>
<?php unset($__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b)): ?>
<?php $component = $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b; ?>
<?php unset($__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => $feesOwed > 0 ? 'purple' : 'green','icon' => 'fa-motorcycle','title' => 'Fees owed to riders','value' => $m($feesOwed),'sub' => 'Pay back after they hand in']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($feesOwed > 0 ? 'purple' : 'green'),'icon' => 'fa-motorcycle','title' => 'Fees owed to riders','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($feesOwed)),'sub' => 'Pay back after they hand in']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b)): ?>
<?php $attributes = $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b; ?>
<?php unset($__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b)): ?>
<?php $component = $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b; ?>
<?php unset($__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => $outstanding ? 'orange' : 'green','icon' => 'fa-clipboard-check','title' => 'Riders outstanding','value' => $outstanding,'sub' => $riders->count() - $outstanding . ' cleared']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($outstanding ? 'orange' : 'green'),'icon' => 'fa-clipboard-check','title' => 'Riders outstanding','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($outstanding),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($riders->count() - $outstanding . ' cleared')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b)): ?>
<?php $attributes = $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b; ?>
<?php unset($__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b)): ?>
<?php $component = $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b; ?>
<?php unset($__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b); ?>
<?php endif; ?>
</div>

<div class="grid lg:grid-cols-2 gap-4">
    <?php $__currentLoopData = $riders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $c = $r->c; ?>
        <section class="bg-white rounded-xl p-4 border-l-4 <?php echo e($c['cleared'] ? 'border-l-[#8cc152]' : 'border-l-[#f5922f]'); ?> <?php echo e($r->rider->is_active ? '' : 'opacity-60'); ?>">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-[#42a5dc] to-[#2c8fc6] font-bold text-white"><?php echo e(mb_strtoupper(mb_substr($r->rider->name, 0, 1))); ?></span>
                    <div class="min-w-0">
                        <div class="font-semibold truncate"><?php echo e($r->rider->name); ?>

                            <span class="ml-1 text-xs rounded-full px-2 py-0.5 <?php echo e($c['cleared'] ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'); ?>"><?php echo e($c['cleared'] ? 'Cleared' : 'Outstanding'); ?></span></div>
                        <div class="text-xs text-slate-500"><?php echo e($c['open_runs']); ?> open run(s)</div>
                    </div>
                </div>
                <?php if (! ($c['cleared'])): ?>
                    <form method="POST" action="<?php echo e(route('manager.remittances.clear', $r->rider)); ?>"><?php echo csrf_field(); ?>
                        <button class="rounded-lg bg-[#8cc152] hover:bg-[#74a83b] text-white px-3 py-2 text-sm font-semibold transition whitespace-nowrap" onclick="return confirm('Receive <?php echo e($m(max($c['cash_held'],0))); ?> and <?php echo e($c['items_pending']); ?> item(s) from <?php echo e($r->rider->name); ?> and clear them?')"><i class="fas fa-check"></i> Clear rider</button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="mt-3 grid grid-cols-3 gap-2 text-center text-xs">
                <div class="rounded-lg bg-slate-50 py-2"><div class="text-base font-bold"><?php echo e($m($c['cash_collected'])); ?></div><div class="uppercase tracking-wide text-slate-500">Collected</div></div>
                <div class="rounded-lg bg-slate-50 py-2"><div class="text-base font-bold text-green-600"><?php echo e($m($c['cash_remitted'])); ?></div><div class="uppercase tracking-wide text-slate-500">Remitted</div></div>
                <div class="rounded-lg bg-slate-50 py-2"><div class="text-base font-bold <?php echo e($c['cash_held'] > 0 ? 'text-red-600' : ''); ?>"><?php echo e($m($c['cash_held'])); ?></div><div class="uppercase tracking-wide text-slate-500">Holding</div></div>
            </div>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-lg <?php echo e($c['fees_owed'] > 0 ? 'bg-purple-50 border border-purple-200' : 'bg-slate-50'); ?> px-3 py-2 text-sm">
                <span><i class="fas fa-motorcycle text-[#7d62c4]"></i> Delivery fees owed to <?php echo e($r->rider->name); ?>: <strong><?php echo e($m($c['fees_owed'])); ?></strong> <span class="text-xs text-slate-500">(paid so far <?php echo e($m($c['fees_paid'])); ?>)</span></span>
                <?php if($c['fees_owed'] > 0): ?>
                    <form method="POST" action="<?php echo e(route('manager.remittances.pay-fees', $r->rider)); ?>"><?php echo csrf_field(); ?>
                        <button class="rounded-lg bg-[#967adc] hover:bg-[#7d62c4] text-white px-3 py-1.5 font-semibold transition" onclick="return confirm('Pay <?php echo e($r->rider->name); ?> <?php echo e($m($c['fees_owed'])); ?> in delivery fees?')"><i class="fas fa-hand-holding-dollar"></i> Pay fees back</button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="mt-2 text-xs text-slate-500">Items due back <?php echo e($c['items_due']); ?> · checked in <?php echo e($c['items_remitted']); ?> · <strong class="text-slate-700"><?php echo e($c['items_pending']); ?> pending</strong></div>
            <form method="POST" action="<?php echo e(route('manager.remittances.store')); ?>" class="mt-3 flex flex-wrap gap-2 items-center"><?php echo csrf_field(); ?>
                <input type="hidden" name="rider_id" value="<?php echo e($r->rider->id); ?>">
                <input type="number" step="0.01" min="0" name="cash" value="0" class="<?php echo e($input); ?> w-24" title="Cash received">
                <input type="number" min="0" name="items" value="0" class="<?php echo e($input); ?> w-16" title="Items checked in">
                <select name="method" class="<?php echo e($input); ?>"><?php $__currentLoopData = \App\Models\Remittance::METHODS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>"><?php echo e(\Illuminate\Support\Str::before($l, ' /')); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                <input name="notes" placeholder="Notes" class="<?php echo e($input); ?> flex-1 min-w-20">
                <button class="rounded-lg bg-[#42a5dc] hover:bg-[#2c8fc6] text-white px-3 py-1.5 text-sm font-semibold transition">Record partial</button>
            </form>
        </section>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="bg-white rounded-xl overflow-hidden mt-8">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#42a5dc] font-semibold text-sm">Recent remittances</div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">When</th><th>Rider</th><th class="text-right">Cash</th><th class="text-right">Items</th><th>Method</th><th>Received by</th><th class="p-3">Notes</th></tr></thead>
        <?php $__empty_1 = true; $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-t border-slate-100 hover:bg-sky-50/50"><td class="p-3 whitespace-nowrap"><?php echo e($h->remitted_at->format('d M, g:ia')); ?></td><td class="font-medium"><?php echo e($h->rider->name); ?></td><td class="text-right"><?php echo e($m($h->cash)); ?></td><td class="text-right"><?php echo e($h->items); ?></td><td><?php echo e(\App\Models\Remittance::METHODS[$h->method] ?? $h->method); ?></td><td><?php echo e($h->recorder?->name); ?></td><td class="p-3 text-slate-500"><?php echo e($h->notes); ?></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="7" class="p-8 text-center text-slate-400">Nothing recorded yet.</td></tr> <?php endif; ?>
    </table></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/remittances.blade.php ENDPATH**/ ?>