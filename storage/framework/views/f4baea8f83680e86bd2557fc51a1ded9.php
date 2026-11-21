
<?php $__env->startSection('title', 'Dashboard · Drezwel Go'); ?>
<?php $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2); ?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => ''.e(now()->format('l, d F Y')).'','title' => 'Operations & Delivery','subtitle' => 'Real-time delivery velocity, cash reconciliation and fleet performance.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => ''.e(now()->format('l, d F Y')).'','title' => 'Operations & Delivery','subtitle' => 'Real-time delivery velocity, cash reconciliation and fleet performance.']); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-calendar-days"></i> <?php echo e($periods[$period] ?? 'All time'); ?></span>
        <span><i class="fas fa-box"></i> <?php echo e($kpi['orders']); ?> orders</span>
        <span><i class="fas fa-coins"></i> <?php echo e($m($kpi['revenue'])); ?></span>
     <?php $__env->endSlot(); ?>
    <form method="GET" class="flex flex-wrap gap-2 text-sm items-center">
        <select name="period" onchange="this.form.submit()" class="rounded-xl border-0 bg-white/10 text-white ring-1 ring-white/20 px-3 py-2.5 focus:outline-none">
            <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option class="text-slate-800" value="<?php echo e($k); ?>" <?php if($period === $k): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php if($period === 'custom'): ?>
            <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="rounded-xl bg-white text-slate-800 px-2 py-2">
            <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="rounded-xl bg-white text-slate-800 px-2 py-2">
            <button class="rounded-xl bg-white text-slate-800 px-3 py-2 font-semibold">Apply</button>
        <?php endif; ?>
        <a href="<?php echo e(route('manager.deliveries.create')); ?>" class="rounded-xl bg-white text-slate-800 px-4 py-2.5 font-semibold hover:bg-gray-100 transition"><i class="fas fa-plus"></i> New delivery</a>
    </form>
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

<?php if($lowProducts->count()): ?>
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
        <div class="flex flex-wrap items-center justify-between gap-2 text-sm text-red-800">
            <span class="font-semibold"><i class="fas fa-triangle-exclamation"></i> Stock alert: <?php echo e($lowProducts->count()); ?> product(s) need restocking</span>
            <a class="font-semibold underline" href="<?php echo e(route('manager.products.index', ['level' => 'low'])); ?>">Manage stock</a>
        </div>
        <div class="mt-2 flex flex-wrap gap-2 text-sm">
            <?php $__currentLoopData = $lowProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('manager.products.show', $p)); ?>" class="rounded-full bg-white px-3 py-1 ring-1 ring-red-200 hover:ring-red-400">
                    <?php echo e($p->name); ?> · <strong class="<?php echo e($p->stock_qty <= 0 ? 'text-red-600' : 'text-amber-600'); ?>"><?php echo e($p->stock_qty <= 0 ? 'out' : $p->stock_qty . ' left'); ?></strong></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 mb-6">
    <?php $salesLink = route('manager.sales', ['period' => $period] + request()->only('from', 'to')); ?>
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'blue','icon' => 'fa-sack-dollar','title' => 'Total sales (goods)','value' => $m($kpi['revenue']),'sub' => $kpi['orders'].' orders · delivery fees ' . $m($kpi['fees']) . ' go to riders','href' => $salesLink]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'blue','icon' => 'fa-sack-dollar','title' => 'Total sales (goods)','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($kpi['revenue'])),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['orders'].' orders · delivery fees ' . $m($kpi['fees']) . ' go to riders'),'href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($salesLink)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'green','icon' => 'fa-money-bill-wave','title' => 'Money collected','value' => $m($kpi['collected']),'sub' => 'For the goods, fees excluded','href' => $salesLink]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green','icon' => 'fa-money-bill-wave','title' => 'Money collected','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($kpi['collected'])),'sub' => 'For the goods, fees excluded','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($salesLink)]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'red','icon' => 'fa-hand-holding-dollar','title' => 'Outstanding','value' => $m($kpi['outstanding']),'sub' => 'Still owed for the goods','href' => route('manager.debtors')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'red','icon' => 'fa-hand-holding-dollar','title' => 'Outstanding','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($kpi['outstanding'])),'sub' => 'Still owed for the goods','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.debtors'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'purple','icon' => 'fa-circle-check','title' => 'Delivered','value' => $kpi['delivered'],'sub' => 'Completed drop-offs','href' => route('manager.deliveries.index', ['status' => 'delivered'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'purple','icon' => 'fa-circle-check','title' => 'Delivered','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['delivered']),'sub' => 'Completed drop-offs','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.deliveries.index', ['status' => 'delivered']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'orange','icon' => 'fa-hourglass-half','title' => 'Pending','value' => $kpi['pending'],'sub' => $kpi['unassigned'].' unassigned · '.$kpi['on_the_road'].' on the road','href' => route('manager.deliveries.index', ['status' => 'pending'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'orange','icon' => 'fa-hourglass-half','title' => 'Pending','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['pending']),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['unassigned'].' unassigned · '.$kpi['on_the_road'].' on the road'),'href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.deliveries.index', ['status' => 'pending']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'slate','icon' => 'fa-bullseye','title' => 'Fulfilment rate','value' => $kpi['rate'].'%','sub' => 'Target 85%','badge' => $kpi['rate'] >= 85 ? 'On target' : 'Below target','href' => route('manager.deliveries.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'slate','icon' => 'fa-bullseye','title' => 'Fulfilment rate','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['rate'].'%'),'sub' => 'Target 85%','badge' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['rate'] >= 85 ? 'On target' : 'Below target'),'href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.deliveries.index'))]); ?>
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

<div class="grid xl:grid-cols-2 gap-5">
    <section class="bg-white rounded-xl shadow-sm overflow-hidden xl:col-span-2">
        <div class="px-4 py-3 bg-white text-slate-800 font-semibold text-sm border-b border-gray-200 border-l-4 border-l-[#1c3041]">Today's queue (<?php echo e($todayQueue->count()); ?>)</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Reference</th><th>Customer</th><th>Location</th><th>Rider</th><th class="text-right">Amount</th><th class="p-3">Status</th></tr></thead>
            <?php $__empty_1 = true; $__currentLoopData = $todayQueue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t"><td class="p-3"><a class="text-brand-accent" href="<?php echo e(route('manager.deliveries.show', $d)); ?>"><?php echo e($d->reference); ?></a></td>
                    <td><?php echo e($d->customer->name); ?></td><td><?php echo e($d->location); ?></td><td><?php echo e($d->rider?->name ?? '—'); ?></td>
                    <td class="text-right"><?php echo e($m($d->amount_due)); ?></td><td class="p-3"><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $d->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($d->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="6" class="p-4 text-slate-400">Nothing waiting for today.</td></tr> <?php endif; ?>
        </table></div>
        <?php if($upcoming->count()): ?>
            <div class="px-4 py-2 text-xs text-slate-500 bg-slate-50 border-t">Upcoming: <?php echo e($upcoming->count()); ?> advance booking(s), next on <?php echo e($upcoming->first()->delivery_date->format('d M')); ?>.</div>
        <?php endif; ?>
    </section>

    <section class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-white text-slate-800 font-semibold text-sm border-b border-gray-200 border-l-4 border-l-[#1c3041]">Daily delivery &amp; revenue velocity</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Date</th><th class="text-right">Orders</th><th class="text-right">Delivered</th><th class="text-right">Pending</th><th class="text-right">Revenue</th><th class="text-right p-3">Cash</th></tr></thead>
            <?php $__empty_1 = true; $__currentLoopData = $daily; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t"><td class="p-3"><?php echo e(\Carbon\Carbon::parse($r->day)->format('D d M')); ?></td><td class="text-right"><?php echo e($r->orders); ?></td><td class="text-right"><?php echo e($r->delivered); ?></td><td class="text-right"><?php echo e($r->pending); ?></td><td class="text-right"><?php echo e($m($r->revenue)); ?></td><td class="text-right p-3"><?php echo e($m($r->cash)); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="6" class="p-4 text-slate-400">No deliveries in this period.</td></tr> <?php endif; ?>
        </table></div>
    </section>

    <section class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-white text-slate-800 font-semibold text-sm border-b border-gray-200 border-l-4 border-l-[#1c3041]">Rider fulfilment performance</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Rider</th><th class="text-right">Assigned</th><th class="text-right">Delivered</th><th class="text-right">Pending</th><th class="text-right">Rate</th><th class="text-right p-3">Revenue</th></tr></thead>
            <?php $__currentLoopData = $riders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="border-t"><td class="p-3 font-medium"><?php echo e($r->rider->name); ?></td><td class="text-right"><?php echo e($r->assigned); ?></td><td class="text-right"><?php echo e($r->delivered); ?></td><td class="text-right"><?php echo e($r->pending); ?></td><td class="text-right"><?php echo e($r->rate); ?>%</td><td class="text-right p-3"><?php echo e($m($r->revenue)); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table></div>
    </section>

    <section class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-white text-slate-800 font-semibold text-sm border-b border-gray-200 border-l-4 border-l-[#1c3041] flex justify-between"><span>Live rider cash &amp; items</span><a class="underline text-white/80" href="<?php echo e(route('manager.remittances')); ?>">Manage</a></div>
        <div class="px-4 py-3 text-sm">Total cash with riders: <strong class="<?php echo e($cashHeld > 0 ? 'text-red-600' : 'text-green-600'); ?>"><?php echo e($m($cashHeld)); ?></strong></div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Rider</th><th class="text-right">Cash held</th><th class="text-right">Items to return</th><th class="p-3">Status</th></tr></thead>
            <?php $__currentLoopData = $riders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="border-t"><td class="p-3"><?php echo e($r->rider->name); ?></td><td class="text-right"><?php echo e($m($r->custody['cash_held'])); ?></td><td class="text-right"><?php echo e($r->custody['items_pending']); ?></td>
                    <td class="p-3"><?php echo $r->custody['settled'] ? '<span class="text-green-600">Cleared</span>' : ($r->custody['cleared'] ? '<span class="text-amber-600">Has open orders</span>' : '<span class="text-amber-600">Outstanding</span>'); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table></div>
    </section>

    <section class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-white text-slate-800 font-semibold text-sm border-b border-gray-200 border-l-4 border-l-[#1c3041]">Delivery mode &amp; order type</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Mode</th><th class="text-right">Orders</th><th class="text-right p-3">Revenue</th></tr></thead>
            <?php $__currentLoopData = \App\Models\Delivery::MODES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="border-t"><td class="p-3"><?php echo e($label); ?></td><td class="text-right"><?php echo e($modes[$k]->orders ?? 0); ?></td><td class="text-right p-3"><?php echo e($m($modes[$k]->revenue ?? 0)); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <thead class="text-left text-slate-500 text-xs border-t"><tr><th class="p-3">Order type</th><th class="text-right">Items sent</th><th class="text-right p-3">Items returned</th></tr></thead>
            <?php $__currentLoopData = \App\Models\Delivery::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="border-t"><td class="p-3"><?php echo e($label); ?> (<?php echo e($types[$k]->orders ?? 0); ?>)</td><td class="text-right"><?php echo e($types[$k]->sent ?? 0); ?></td><td class="text-right p-3"><?php echo e($types[$k]->returned ?? 0); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table></div>
    </section>

    <section class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-white text-slate-800 font-semibold text-sm border-b border-gray-200 border-l-4 border-l-[#1c3041]">Top purchasing customers</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Customer</th><th class="text-right">Orders</th><th class="text-right">Spend</th><th class="text-right p-3">Balance</th></tr></thead>
            <?php $__empty_1 = true; $__currentLoopData = $topCustomers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t"><td class="p-3"><a class="text-brand-accent" href="<?php echo e(route('manager.customers.show', $c->id)); ?>"><?php echo e($c->name); ?></a></td><td class="text-right"><?php echo e($c->orders); ?></td><td class="text-right"><?php echo e($m($c->spend)); ?></td><td class="text-right p-3 <?php echo e($c->spend - $c->paid > 0 ? 'text-red-600' : ''); ?>"><?php echo e($m($c->spend - $c->paid)); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="4" class="p-4 text-slate-400">No data yet.</td></tr> <?php endif; ?>
        </table></div>
    </section>

    <?php echo $__env->make('manager._top-products', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/dashboard.blade.php ENDPATH**/ ?>