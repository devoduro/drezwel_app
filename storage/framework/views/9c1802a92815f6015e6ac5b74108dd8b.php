<?php $__env->startSection('title', 'Sales · Drezwel Go'); ?>
<?php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $field = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]';
    $range = ($from || $to) ? ($from?->format('d M Y') ?? '…') . ' – ' . ($to?->format('d M Y') ?? '…') : 'All records';
    $qs = request()->only(['period', 'from', 'to']);
    $head = 'px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm text-slate-800';
    $badge = ['online' => 'bg-purple-100 text-purple-700', 'phone' => 'bg-sky-100 text-sky-700', 'pos' => 'bg-emerald-100 text-emerald-700', 'manager' => 'bg-slate-100 text-slate-600'];
?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => $range,'title' => 'Sales','subtitle' => 'What you sold, through which channel, and how you were paid.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($range),'title' => 'Sales','subtitle' => 'What you sold, through which channel, and how you were paid.']); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-calendar-days"></i> <?php echo e($periods[$period] ?? 'Last 30 days'); ?></span>
        <span><i class="fas fa-receipt"></i> <?php echo e($kpi['orders']); ?> sales</span>
        <span><i class="fas fa-coins"></i> <?php echo e($m($kpi['billed'])); ?></span>
     <?php $__env->endSlot(); ?>
    <form method="GET" class="flex flex-wrap items-center gap-2 text-sm">
        <?php $__currentLoopData = request()->only('source', 'method', 'q'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><input type="hidden" name="<?php echo e($k); ?>" value="<?php echo e($v); ?>"><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <select name="period" onchange="this.form.submit()" class="rounded-xl border-0 bg-white/10 text-white ring-1 ring-white/20 px-3 py-2.5 focus:outline-none">
            <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option class="text-slate-800" value="<?php echo e($k); ?>" <?php if($period === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php if($period === 'custom'): ?>
            <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="rounded-xl bg-white text-slate-800 px-2 py-2">
            <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="rounded-xl bg-white text-slate-800 px-2 py-2">
            <button class="rounded-xl bg-white text-slate-800 px-3 py-2 font-semibold">Apply</button>
        <?php endif; ?>
        <a href="<?php echo e(route('manager.reports.export', ['type' => 'orders'] + $qs)); ?>" class="rounded-xl bg-white text-slate-800 px-4 py-2.5 font-semibold hover:bg-gray-100 transition"><i class="fas fa-file-csv"></i> Download CSV</a>
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

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'blue','icon' => 'fa-receipt','title' => 'Total sales','value' => $m($kpi['billed']),'sub' => $kpi['orders'] . ' orders · avg ' . $m($kpi['average'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'blue','icon' => 'fa-receipt','title' => 'Total sales','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($kpi['billed'])),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['orders'] . ' orders · avg ' . $m($kpi['average']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'green','icon' => 'fa-money-bill-wave','title' => 'Money received','value' => $m($kpi['collected']),'sub' => 'Paid so far']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green','icon' => 'fa-money-bill-wave','title' => 'Money received','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($kpi['collected'])),'sub' => 'Paid so far']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'red','icon' => 'fa-hand-holding-dollar','title' => 'Still owed','value' => $m($kpi['outstanding']),'sub' => 'Unpaid balances','href' => route('manager.debtors')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'red','icon' => 'fa-hand-holding-dollar','title' => 'Still owed','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($kpi['outstanding'])),'sub' => 'Unpaid balances','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.debtors'))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => $kpi['profit'] >= 0 ? 'purple' : 'red','icon' => 'fa-chart-line','title' => 'Product profit','value' => $m($kpi['profit']),'sub' => 'Sales of products minus their cost']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['profit'] >= 0 ? 'purple' : 'red'),'icon' => 'fa-chart-line','title' => 'Product profit','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($kpi['profit'])),'sub' => 'Sales of products minus their cost']); ?>
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

<div class="grid gap-5 lg:grid-cols-2 mb-6">
    <section class="bg-white rounded-xl overflow-hidden">
        <div class="<?php echo e($head); ?>">Sales by channel</div>
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="px-4 py-2">Channel</th><th class="text-right">Orders</th><th class="text-right">Sales</th><th class="text-right px-4">Received</th></tr></thead>
            <?php $__currentLoopData = $sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $r = $bySource[$k] ?? null; ?>
                <tr class="border-t border-slate-100"><td class="px-4 py-2"><a class="rounded-full px-2 py-0.5 text-xs font-medium <?php echo e($badge[$k]); ?>" href="<?php echo e(route('manager.sales', array_merge(request()->except('page', 'source'), ['source' => $k]))); ?>"><?php echo e($l); ?></a></td>
                    <td class="text-right"><?php echo e($r->n ?? 0); ?></td><td class="text-right"><?php echo e($m($r->value ?? 0)); ?></td><td class="text-right px-4"><?php echo e($m($r->paid ?? 0)); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table>
    </section>
    <section class="bg-white rounded-xl overflow-hidden">
        <div class="<?php echo e($head); ?>">How customers paid</div>
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="px-4 py-2">Method</th><th class="text-right">Payments</th><th class="text-right px-4">Amount</th></tr></thead>
            <?php $__currentLoopData = \App\Models\Payment::METHODS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="border-t border-slate-100"><td class="px-4 py-2"><a class="hover:underline" href="<?php echo e(route('manager.sales', array_merge(request()->except('page', 'method'), ['method' => $k]))); ?>"><?php echo e($l); ?></a></td><td class="text-right"><?php echo e($byMethod[$k]->n ?? 0); ?></td><td class="text-right px-4"><?php echo e($m($byMethod[$k]->total ?? 0)); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr class="border-t bg-slate-50 font-semibold"><td class="px-4 py-2">Total</td><td class="text-right"><?php echo e($byMethod->sum('n')); ?></td><td class="text-right px-4"><?php echo e($m($byMethod->sum('total'))); ?></td></tr>
        </table>
    </section>
</div>

<form method="GET" class="mb-4 grid grid-cols-2 gap-2 rounded-xl bg-white p-3 text-sm md:grid-cols-5">
    <?php $__currentLoopData = $qs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><input type="hidden" name="<?php echo e($k); ?>" value="<?php echo e($v); ?>"><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <div class="relative col-span-2"><i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input name="q" value="<?php echo e(request('q')); ?>" placeholder="Search name, phone, reference…" class="<?php echo e($field); ?> w-full pl-9"></div>
    <select name="source" class="<?php echo e($field); ?>"><option value="">All channels</option><?php $__currentLoopData = $sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(request('source') === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
    <select name="method" class="<?php echo e($field); ?>"><option value="">Any payment method</option><?php $__currentLoopData = \App\Models\Payment::METHODS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(request('method') === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
    <button class="rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-4 py-2 font-semibold transition"><i class="fas fa-filter"></i> Filter</button>
</form>

<div class="bg-white rounded-xl overflow-hidden">
    <div class="<?php echo e($head); ?>">Sales (<?php echo e($sales->total()); ?>)</div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Date</th><th>Invoice</th><th>Customer</th><th>Channel</th><th>What was sold</th><th class="text-right">Total</th><th class="text-right">Paid</th><th>Paid by</th><th class="text-right p-3">Balance</th></tr></thead>
        <?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-t border-slate-100 hover:bg-sky-50/50 align-top">
                <td class="p-3 whitespace-nowrap text-slate-600"><?php echo e($s->delivery_date->format('d M Y')); ?></td>
                <td><a class="font-semibold text-[#1c3041] hover:underline" target="_blank" href="<?php echo e(route('manager.deliveries.invoice', $s)); ?>"><?php echo e($s->reference); ?></a></td>
                <td><?php echo e($s->customer->phone === \App\Services\SalesService::WALK_IN_PHONE ? 'Walk-in customer' : $s->customer->name); ?></td>
                <td><span class="rounded-full px-2 py-0.5 text-xs font-medium <?php echo e($badge[$s->source] ?? $badge['manager']); ?>"><?php echo e($sources[$s->source] ?? $s->source); ?></span></td>
                <td class="max-w-[240px] truncate text-slate-600" title="<?php echo e($s->package_description); ?>"><?php echo e($s->package_description); ?></td>
                <td class="text-right"><?php echo e(number_format($s->amount_due, 2)); ?></td>
                <td class="text-right text-slate-600"><?php echo e(number_format($s->amount_paid, 2)); ?></td>
                <td class="text-xs text-slate-500"><?php echo e($s->payments->map(fn ($p) => \App\Models\Payment::METHODS[$p->method] ?? $p->method)->unique()->implode(', ') ?: '—'); ?></td>
                <td class="text-right p-3 <?php echo e($s->balance > 0 ? 'text-red-600 font-medium' : 'text-green-600'); ?>"><?php echo e(number_format($s->balance, 2)); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="9" class="p-10 text-center text-slate-400"><i class="fas fa-receipt text-3xl mb-2 block"></i>No sales in this period.</td></tr> <?php endif; ?>
    </table></div>
</div>
<div class="mt-4"><?php echo e($sales->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/sales.blade.php ENDPATH**/ ?>