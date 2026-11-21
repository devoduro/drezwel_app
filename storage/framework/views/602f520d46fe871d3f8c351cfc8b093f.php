
<?php $__env->startSection('title', 'Reports · Drezwel Go'); ?>
<?php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $label = $periods[$period] ?? 'All time';
    $range = ($from || $to) ? ($from?->format('d M Y') ?? '…') . ' – ' . ($to?->format('d M Y') ?? '…') : 'All records';
    $qs = request()->only(['period', 'from', 'to']);
    $th = 'text-left text-slate-500 text-xs';
    $head = 'px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm text-slate-800';
?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => $range,'title' => 'Business report','subtitle' => 'Finances, orders and stock in one place. Print it or download the data as CSV.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($range),'title' => 'Business report','subtitle' => 'Finances, orders and stock in one place. Print it or download the data as CSV.']); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-calendar-days"></i> <?php echo e($label); ?></span>
        <span><i class="fas fa-box"></i> <?php echo e($orders); ?> orders</span>
        <span><i class="fas fa-coins"></i> <?php echo e($m($finance['revenue'])); ?></span>
     <?php $__env->endSlot(); ?>
    <form method="GET" class="no-print flex flex-wrap gap-2 text-sm items-center">
        <select name="period" onchange="this.form.submit()" class="rounded-xl border-0 bg-white/10 text-white ring-1 ring-white/20 px-3 py-2.5 focus:outline-none">
            <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option class="text-slate-800" value="<?php echo e($k); ?>" <?php if($period === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php if($period === 'custom'): ?>
            <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="rounded-xl bg-white text-slate-800 px-2 py-2">
            <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="rounded-xl bg-white text-slate-800 px-2 py-2">
            <button class="rounded-xl bg-white text-slate-800 px-3 py-2 font-semibold">Apply</button>
        <?php endif; ?>
        <button type="button" onclick="window.print()" class="rounded-xl bg-white text-slate-800 px-4 py-2.5 font-semibold hover:bg-gray-100 transition"><i class="fas fa-print"></i> Print / PDF</button>
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

<div class="no-print mb-6 flex flex-wrap gap-2 text-sm">
    <span class="text-slate-500 self-center">Download CSV:</span>
    <a class="rounded-lg bg-white border border-slate-200 px-3 py-1.5 hover:border-[#1c3041]" href="<?php echo e(route('manager.reports.export', ['type' => 'orders'] + $qs)); ?>"><i class="fas fa-file-csv text-[#74a83b]"></i> Orders</a>
    <a class="rounded-lg bg-white border border-slate-200 px-3 py-1.5 hover:border-[#1c3041]" href="<?php echo e(route('manager.reports.export', ['type' => 'sales'] + $qs)); ?>"><i class="fas fa-file-csv text-[#74a83b]"></i> Sales by product</a>
    <a class="rounded-lg bg-white border border-slate-200 px-3 py-1.5 hover:border-[#1c3041]" href="<?php echo e(route('manager.cashbook.export', $qs)); ?>"><i class="fas fa-file-csv text-[#74a83b]"></i> Cash book</a>
    <a class="rounded-lg bg-white border border-slate-200 px-3 py-1.5 hover:border-[#1c3041]" href="<?php echo e(route('manager.reports.export', ['type' => 'stock'])); ?>"><i class="fas fa-file-csv text-[#74a83b]"></i> Current stock</a>
</div>


<h2 class="text-lg font-bold mb-3"><i class="fas fa-sack-dollar text-[#1c3041]"></i> Finances</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'blue','icon' => 'fa-receipt','title' => 'Sales (goods)','value' => $m($finance['revenue']),'sub' => 'Delivery fees ' . $m($finance['fees_charged']) . ' go to riders']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'blue','icon' => 'fa-receipt','title' => 'Sales (goods)','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($finance['revenue'])),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Delivery fees ' . $m($finance['fees_charged']) . ' go to riders')]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'green','icon' => 'fa-money-bill-wave','title' => 'Collected','value' => $m($finance['collected']),'sub' => 'For the goods · ' . $finance['collection_rate'] . '% of sales']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green','icon' => 'fa-money-bill-wave','title' => 'Collected','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($finance['collected'])),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('For the goods · ' . $finance['collection_rate'] . '% of sales')]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'red','icon' => 'fa-hand-holding-dollar','title' => 'Outstanding','value' => $m($finance['outstanding']),'sub' => 'Still owed for the goods']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'red','icon' => 'fa-hand-holding-dollar','title' => 'Outstanding','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($finance['outstanding'])),'sub' => 'Still owed for the goods']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => $finance['net'] >= 0 ? 'purple' : 'red','icon' => 'fa-chart-line','title' => 'Net profit','value' => $m($finance['net']),'sub' => 'Gross profit minus cash book expenses']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($finance['net'] >= 0 ? 'purple' : 'red'),'icon' => 'fa-chart-line','title' => 'Net profit','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($finance['net'])),'sub' => 'Gross profit minus cash book expenses']); ?>
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

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'red','icon' => 'fa-arrow-trend-down','title' => 'Money spent','value' => $m($finance['cash_out']),'sub' => 'Purchases + expenses (cash book)','href' => route('manager.cashbook', $qs)]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'red','icon' => 'fa-arrow-trend-down','title' => 'Money spent','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($finance['cash_out'])),'sub' => 'Purchases + expenses (cash book)','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.cashbook', $qs))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'blue','icon' => 'fa-cart-flatbed','title' => 'Purchases','value' => $m($finance['purchases']),'sub' => 'Stock and goods bought']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'blue','icon' => 'fa-cart-flatbed','title' => 'Purchases','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($finance['purchases'])),'sub' => 'Stock and goods bought']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'orange','icon' => 'fa-file-invoice-dollar','title' => 'Expenses','value' => $m($finance['expenses']),'sub' => 'Rent, fuel, wages and other running costs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'orange','icon' => 'fa-file-invoice-dollar','title' => 'Expenses','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($finance['expenses'])),'sub' => 'Rent, fuel, wages and other running costs']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => $finance['cash_position'] >= 0 ? 'green' : 'red','icon' => 'fa-scale-balanced','title' => 'Cash position','value' => $m($finance['cash_position']),'sub' => 'Collected from customers minus money spent']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($finance['cash_position'] >= 0 ? 'green' : 'red'),'icon' => 'fa-scale-balanced','title' => 'Cash position','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($finance['cash_position'])),'sub' => 'Collected from customers minus money spent']); ?>
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

<div class="grid xl:grid-cols-2 gap-5 mb-8">
    <section class="bg-white rounded-xl overflow-hidden">
        <div class="<?php echo e($head); ?>">Profit &amp; loss (orders with products)</div>
        <table class="w-full text-sm">
            <?php $__currentLoopData = [
                ['Sales of products', $finance['line_revenue'], ''],
                ['Cost of goods sold', -$finance['cogs'], 'text-slate-500'],
                ['Gross profit', $finance['gross'], 'font-semibold border-t'],
                ['Expenses (from the cash book)', -$finance['expenses'], 'text-slate-500'],
                ['Net profit', $finance['net'], 'font-bold border-t text-base ' . ($finance['net'] >= 0 ? 'text-green-700' : 'text-red-600')],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$l, $v, $c]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="<?php echo e($c); ?>"><td class="px-4 py-2"><?php echo e($l); ?></td><td class="px-4 py-2 text-right"><?php echo e($v < 0 ? '- ' . $m(abs($v)) : $m($v)); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr class="border-t bg-slate-50 text-xs text-slate-500"><td class="px-4 py-2">Gross margin</td><td class="px-4 py-2 text-right"><?php echo e($finance['margin']); ?>%</td></tr>
            <?php if($finance['untracked'] > 0): ?>
                <tr class="bg-amber-50 text-xs text-amber-800"><td class="px-4 py-2" colspan="2"><i class="fas fa-circle-info"></i> <?php echo e($m($finance['untracked'])); ?> of revenue comes from orders with no products attached, so its cost is not counted.</td></tr>
            <?php endif; ?>
        </table>
    </section>

    <section class="bg-white rounded-xl overflow-hidden">
        <div class="<?php echo e($head); ?>">Delivery fees (collected for riders)</div>
        <table class="w-full text-sm">
            <?php $__currentLoopData = [
                ['Charged to customers', $finance['fees_charged']],
                ['Earned by riders (delivered orders)', $finance['fees_earned']],
                ['Paid back to riders', $finance['fees_paid']],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$l, $v]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="border-t border-slate-100"><td class="px-4 py-2"><?php echo e($l); ?></td><td class="px-4 py-2 text-right"><?php echo e($m($v)); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr class="border-t font-semibold bg-purple-50"><td class="px-4 py-2">Still owed to riders</td><td class="px-4 py-2 text-right"><?php echo e($m($finance['fees_owed'])); ?></td></tr>
            <tr class="bg-slate-50 text-xs text-slate-500"><td class="px-4 py-2" colspan="2">Customers pay goods + delivery fee to the rider, the rider hands it all in, and the fee is paid back to them. It is not business income or cost.</td></tr>
        </table>
    </section>

    <section class="bg-white rounded-xl overflow-hidden">
        <div class="<?php echo e($head); ?>">Money received by method</div>
        <table class="w-full text-sm">
            <thead class="<?php echo e($th); ?>"><tr><th class="px-4 py-2">Method</th><th class="text-right">Payments</th><th class="text-right px-4">Amount</th></tr></thead>
            <?php $__currentLoopData = \App\Models\Payment::METHODS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="border-t border-slate-100"><td class="px-4 py-2"><?php echo e($l); ?></td><td class="text-right"><?php echo e($payments[$k]->n ?? 0); ?></td><td class="text-right px-4"><?php echo e($m($payments[$k]->total ?? 0)); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <tr class="border-t font-semibold bg-slate-50"><td class="px-4 py-2">Total</td><td class="text-right"><?php echo e($payments->sum('n')); ?></td><td class="text-right px-4"><?php echo e($m($payments->sum('total'))); ?></td></tr>
        </table>
    </section>

    <section class="bg-white rounded-xl overflow-hidden xl:col-span-2">
        <div class="<?php echo e($head); ?>">Daily orders &amp; revenue</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="<?php echo e($th); ?>"><tr><th class="px-4 py-2">Date</th><th class="text-right">Orders</th><th class="text-right">Delivered</th><th class="text-right">Revenue</th><th class="text-right px-4">Collected</th></tr></thead>
            <?php $__empty_1 = true; $__currentLoopData = $daily; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t border-slate-100"><td class="px-4 py-2"><?php echo e(\Carbon\Carbon::parse($r->day)->format('D d M Y')); ?></td><td class="text-right"><?php echo e($r->orders); ?></td><td class="text-right"><?php echo e($r->delivered); ?></td><td class="text-right"><?php echo e($m($r->revenue)); ?></td><td class="text-right px-4"><?php echo e($m($r->paid)); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="5" class="p-6 text-center text-slate-400">No orders in this period.</td></tr> <?php endif; ?>
        </table></div>
    </section>
</div>

<section class="bg-white rounded-xl overflow-hidden mb-8">
    <div class="<?php echo e($head); ?> flex items-center justify-between"><span>Cash book: where the money went</span><a class="no-print text-xs font-semibold text-[#1c3041] hover:underline" href="<?php echo e(route('manager.cashbook', $qs)); ?>">Open cash book <i class="fas fa-arrow-right"></i></a></div>
    <table class="w-full text-sm">
        <thead class="<?php echo e($th); ?>"><tr><th class="px-4 py-2">Category</th><th>Type</th><th class="text-right">Entries</th><th class="text-right px-4">Amount</th></tr></thead>
        <?php $__empty_1 = true; $__currentLoopData = $cashbookCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-t border-slate-100"><td class="px-4 py-2"><?php echo e($c->category); ?></td><td class="text-xs text-slate-500"><?php echo e($c->type === 'purchase' ? 'Purchase' : 'Expense'); ?></td><td class="text-right"><?php echo e($c->n); ?></td><td class="text-right px-4 font-medium"><?php echo e($m($c->total)); ?></td></tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">Nothing recorded in the cash book for this period.</td></tr>
        <?php endif; ?>
        <?php if($cashbookCategories->count()): ?><tr class="border-t font-semibold bg-slate-50"><td class="px-4 py-2" colspan="3">Total spent</td><td class="text-right px-4"><?php echo e($m($finance['cash_out'])); ?></td></tr><?php endif; ?>
    </table>
</section>


<h2 class="text-lg font-bold mb-3 print-break"><i class="fas fa-truck-fast text-[#1c3041]"></i> Orders</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'blue','icon' => 'fa-box','title' => 'Orders','value' => $orders,'sub' => 'Excluding cancelled']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'blue','icon' => 'fa-box','title' => 'Orders','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($orders),'sub' => 'Excluding cancelled']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'green','icon' => 'fa-circle-check','title' => 'Delivered','value' => $delivered,'sub' => ($orders ? round($delivered / $orders * 100, 1) : 0) . '% fulfilment']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green','icon' => 'fa-circle-check','title' => 'Delivered','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($delivered),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($orders ? round($delivered / $orders * 100, 1) : 0) . '% fulfilment')]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'orange','icon' => 'fa-hourglass-half','title' => 'Pending','value' => ($byStatus['pending'] ?? 0) + ($byStatus['assigned'] ?? 0) + ($byStatus['out_for_delivery'] ?? 0),'sub' => 'Not delivered yet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'orange','icon' => 'fa-hourglass-half','title' => 'Pending','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(($byStatus['pending'] ?? 0) + ($byStatus['assigned'] ?? 0) + ($byStatus['out_for_delivery'] ?? 0)),'sub' => 'Not delivered yet']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'slate','icon' => 'fa-tag','title' => 'Average order','value' => $m($orders ? $finance['revenue'] / $orders : 0),'sub' => 'Revenue per order']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'slate','icon' => 'fa-tag','title' => 'Average order','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($orders ? $finance['revenue'] / $orders : 0)),'sub' => 'Revenue per order']); ?>
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

<div class="grid xl:grid-cols-2 gap-5 mb-5">
    <section class="bg-white rounded-xl overflow-hidden">
        <div class="<?php echo e($head); ?>">By status, mode &amp; type</div>
        <table class="w-full text-sm">
            <thead class="<?php echo e($th); ?>"><tr><th class="px-4 py-2">Status</th><th class="text-right px-4">Orders</th></tr></thead>
            <?php $__currentLoopData = \App\Models\Delivery::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php if($k === 'cancelled') continue; ?>
                <tr class="border-t border-slate-100"><td class="px-4 py-2"><?php if (isset($component)) { $__componentOriginal2ddbc40e602c342e508ac696e52f8719 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2ddbc40e602c342e508ac696e52f8719 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge','data' => ['status' => $k]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($k)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $attributes = $__attributesOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__attributesOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2ddbc40e602c342e508ac696e52f8719)): ?>
<?php $component = $__componentOriginal2ddbc40e602c342e508ac696e52f8719; ?>
<?php unset($__componentOriginal2ddbc40e602c342e508ac696e52f8719); ?>
<?php endif; ?></td><td class="text-right px-4"><?php echo e($byStatus[$k] ?? 0); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <thead class="<?php echo e($th); ?> border-t"><tr><th class="px-4 py-2">Delivery mode</th><th class="text-right px-4">Orders · Revenue</th></tr></thead>
            <?php $__currentLoopData = \App\Models\Delivery::MODES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="border-t border-slate-100"><td class="px-4 py-2"><?php echo e($l); ?></td><td class="text-right px-4"><?php echo e($modes[$k]->n ?? 0); ?> · <?php echo e($m($modes[$k]->revenue ?? 0)); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <thead class="<?php echo e($th); ?> border-t"><tr><th class="px-4 py-2">Order type</th><th class="text-right px-4">Orders · Revenue</th></tr></thead>
            <?php $__currentLoopData = \App\Models\Delivery::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="border-t border-slate-100"><td class="px-4 py-2"><?php echo e($l); ?></td><td class="text-right px-4"><?php echo e($types[$k]->n ?? 0); ?> · <?php echo e($m($types[$k]->revenue ?? 0)); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table>
    </section>

    <section class="bg-white rounded-xl overflow-hidden">
        <div class="<?php echo e($head); ?>">Top customers</div>
        <table class="w-full text-sm">
            <thead class="<?php echo e($th); ?>"><tr><th class="px-4 py-2">Customer</th><th class="text-right">Orders</th><th class="text-right">Spend</th><th class="text-right px-4">Owes</th></tr></thead>
            <?php $__empty_1 = true; $__currentLoopData = $topCustomers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t border-slate-100"><td class="px-4 py-2"><?php echo e($c->name); ?></td><td class="text-right"><?php echo e($c->orders); ?></td><td class="text-right"><?php echo e($m($c->spend)); ?></td><td class="text-right px-4 <?php echo e($c->spend - $c->paid > 0 ? 'text-red-600' : ''); ?>"><?php echo e($m($c->spend - $c->paid)); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="4" class="p-6 text-center text-slate-400">No data.</td></tr> <?php endif; ?>
        </table>
    </section>

    <section class="bg-white rounded-xl overflow-hidden xl:col-span-2">
        <div class="<?php echo e($head); ?>">Rider performance &amp; custody</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="<?php echo e($th); ?>"><tr><th class="px-4 py-2">Rider</th><th class="text-right">Assigned</th><th class="text-right">Delivered</th><th class="text-right">Undelivered</th><th class="text-right">Rate</th><th class="text-right">Value</th><th class="text-right">Fees</th><th class="text-right">Cash held</th><th class="text-right px-4">Items to return</th></tr></thead>
            <?php $__empty_1 = true; $__currentLoopData = $riders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t border-slate-100"><td class="px-4 py-2 font-medium"><?php echo e($r->rider->name); ?></td><td class="text-right"><?php echo e($r->assigned); ?></td><td class="text-right"><?php echo e($r->delivered); ?></td><td class="text-right"><?php echo e($r->undelivered); ?></td><td class="text-right"><?php echo e($r->rate); ?>%</td>
                    <td class="text-right"><?php echo e($m($r->value)); ?></td><td class="text-right"><?php echo e($m($r->fees)); ?></td><td class="text-right <?php echo e($r->custody['cash_held'] > 0 ? 'text-red-600 font-medium' : ''); ?>"><?php echo e($m($r->custody['cash_held'])); ?></td><td class="text-right px-4"><?php echo e($r->custody['items_pending']); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="9" class="p-6 text-center text-slate-400">No rider activity.</td></tr> <?php endif; ?>
        </table></div>
    </section>
</div>


<h2 class="text-lg font-bold mb-3 mt-8 print-break"><i class="fas fa-boxes-stacked text-[#1c3041]"></i> Stock</h2>
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
    <?php if (isset($component)) { $__componentOriginalec2208e5b554b62f9a0aeb9b4b09865b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalec2208e5b554b62f9a0aeb9b4b09865b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'blue','icon' => 'fa-cubes','title' => 'Units in stock','value' => number_format($stock['units']),'sub' => $stock['products'] . ' products']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'blue','icon' => 'fa-cubes','title' => 'Units in stock','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($stock['units'])),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stock['products'] . ' products')]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'orange','icon' => 'fa-coins','title' => 'Stock at cost','value' => $m($stock['cost_value']),'sub' => 'Money tied up in stock']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'orange','icon' => 'fa-coins','title' => 'Stock at cost','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($stock['cost_value'])),'sub' => 'Money tied up in stock']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'green','icon' => 'fa-sack-dollar','title' => 'Stock at selling price','value' => $m($stock['retail_value']),'sub' => 'Potential profit ' . $m($stock['potential_profit'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green','icon' => 'fa-sack-dollar','title' => 'Stock at selling price','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($stock['retail_value'])),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Potential profit ' . $m($stock['potential_profit']))]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => $needing->count() ? 'red' : 'green','icon' => 'fa-triangle-exclamation','title' => 'Need restocking','value' => $needing->count(),'sub' => 'Low or out of stock']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($needing->count() ? 'red' : 'green'),'icon' => 'fa-triangle-exclamation','title' => 'Need restocking','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($needing->count()),'sub' => 'Low or out of stock']); ?>
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
    <section class="bg-white rounded-xl overflow-hidden">
        <div class="<?php echo e($head); ?>">Reorder list</div>
        <table class="w-full text-sm">
            <thead class="<?php echo e($th); ?>"><tr><th class="px-4 py-2">Product</th><th class="text-right">In stock</th><th class="text-right">Reorder at</th><th class="px-4">Level</th></tr></thead>
            <?php $__empty_1 = true; $__currentLoopData = $needing; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t border-slate-100"><td class="px-4 py-2"><a class="text-[#1c3041] hover:underline" href="<?php echo e(route('manager.products.show', $p)); ?>"><?php echo e($p->name); ?></a></td><td class="text-right font-bold <?php echo e($p->level === 'out' ? 'text-red-600' : 'text-amber-600'); ?>"><?php echo e($p->stock_qty); ?></td><td class="text-right"><?php echo e($p->reorder_level); ?></td><td class="px-4"><?php echo $__env->make('manager.products._level', ['p' => $p], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="4" class="p-6 text-center text-slate-400"><i class="fas fa-circle-check text-green-500"></i> Everything is above its reorder level.</td></tr> <?php endif; ?>
        </table>
    </section>

    <section class="bg-white rounded-xl overflow-hidden">
        <div class="<?php echo e($head); ?>">Stock movement in period</div>
        <table class="w-full text-sm">
            <thead class="<?php echo e($th); ?>"><tr><th class="px-4 py-2">Type</th><th class="text-right">Entries</th><th class="text-right px-4">Units</th></tr></thead>
            <?php $__currentLoopData = \App\Models\StockMovement::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="border-t border-slate-100"><td class="px-4 py-2"><?php echo e($l); ?></td><td class="text-right"><?php echo e($movements[$k]->n ?? 0); ?></td><td class="text-right px-4 <?php echo e(($movements[$k]->units ?? 0) < 0 ? 'text-red-600' : 'text-green-600'); ?>"><?php echo e(($movements[$k]->units ?? 0) > 0 ? '+' : ''); ?><?php echo e($movements[$k]->units ?? 0); ?></td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table>
    </section>

    <section class="bg-white rounded-xl overflow-hidden xl:col-span-2">
        <div class="<?php echo e($head); ?>">Best sellers &amp; profit by product</div>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="<?php echo e($th); ?>"><tr><th class="px-4 py-2">Product</th><th class="text-right">Units sold</th><th class="text-right">Revenue</th><th class="text-right">Cost</th><th class="text-right">Profit</th><th class="text-right px-4">Margin</th></tr></thead>
            <?php $__empty_1 = true; $__currentLoopData = $sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $profit = $s->revenue - $s->cost; ?>
                <tr class="border-t border-slate-100"><td class="px-4 py-2"><?php echo e($s->name); ?><div class="text-xs text-slate-400"><?php echo e($s->sku); ?></div></td><td class="text-right font-semibold"><?php echo e($s->units); ?></td><td class="text-right"><?php echo e($m($s->revenue)); ?></td><td class="text-right text-slate-500"><?php echo e($m($s->cost)); ?></td>
                    <td class="text-right <?php echo e($profit >= 0 ? 'text-green-700' : 'text-red-600'); ?>"><?php echo e($m($profit)); ?></td><td class="text-right px-4"><?php echo e($s->revenue > 0 ? round($profit / $s->revenue * 100, 1) : 0); ?>%</td></tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="6" class="p-6 text-center text-slate-400">No product sales in this period. Add products to orders to see them here.</td></tr> <?php endif; ?>
        </table></div>
    </section>
</div>

<p class="mt-6 text-xs text-slate-400">Generated <?php echo e(now()->format('d M Y, g:ia')); ?> · Revenue and orders are by delivery date; money received is by payment date.</p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/reports.blade.php ENDPATH**/ ?>