<?php $__env->startSection('title', 'Cash book · Drezwel Go'); ?>
<?php
    use App\Models\CashbookEntry as CB;
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $field = 'w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]';
    $head = 'px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm text-slate-800';
    $range = ($from || $to) ? ($from?->format('d M Y') ?? '…') . ' – ' . ($to?->format('d M Y') ?? '…') : 'All records';
    $keep = request()->only(['period', 'from', 'to']);
    $type = old('type', $edit?->type ?? 'expense');
    $badge = ['purchase' => 'bg-sky-100 text-sky-700', 'expense' => 'bg-amber-100 text-amber-700'];
?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => $range,'title' => 'Cash book','subtitle' => 'Everything you bought and spent, set against what customers have paid.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($range),'title' => 'Cash book','subtitle' => 'Everything you bought and spent, set against what customers have paid.']); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-calendar-days"></i> <?php echo e($periods[$period] ?? 'Last 30 days'); ?></span>
        <span><i class="fas fa-arrow-trend-down"></i> <?php echo e($m($kpi['out'])); ?> spent</span>
     <?php $__env->endSlot(); ?>
    <form method="GET" class="flex flex-wrap items-center gap-2 text-sm">
        <?php $__currentLoopData = request()->only('type', 'category', 'method', 'q'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><input type="hidden" name="<?php echo e($k); ?>" value="<?php echo e($v); ?>"><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <select name="period" onchange="this.form.submit()" class="rounded-xl border-0 bg-white/10 text-white ring-1 ring-white/20 px-3 py-2.5 focus:outline-none">
            <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option class="text-slate-800" value="<?php echo e($k); ?>" <?php if($period === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <?php if($period === 'custom'): ?>
            <input type="date" name="from" value="<?php echo e(request('from')); ?>" class="rounded-xl bg-white text-slate-800 px-2 py-2">
            <input type="date" name="to" value="<?php echo e(request('to')); ?>" class="rounded-xl bg-white text-slate-800 px-2 py-2">
            <button class="rounded-xl bg-white text-slate-800 px-3 py-2 font-semibold">Apply</button>
        <?php endif; ?>
        <a href="<?php echo e(route('manager.cashbook.export', $keep + request()->only('type'))); ?>" class="rounded-xl bg-white text-slate-800 px-4 py-2.5 font-semibold hover:bg-gray-100 transition"><i class="fas fa-file-csv"></i> Download CSV</a>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'red','icon' => 'fa-arrow-trend-down','title' => 'Money out','value' => $m($kpi['out']),'sub' => 'Purchases and expenses']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'red','icon' => 'fa-arrow-trend-down','title' => 'Money out','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($kpi['out'])),'sub' => 'Purchases and expenses']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'blue','icon' => 'fa-cart-flatbed','title' => 'Purchases','value' => $m($kpi['purchases']),'sub' => 'Stock and goods bought']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'blue','icon' => 'fa-cart-flatbed','title' => 'Purchases','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($kpi['purchases'])),'sub' => 'Stock and goods bought']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'orange','icon' => 'fa-file-invoice-dollar','title' => 'Expenses','value' => $m($kpi['expenses']),'sub' => 'Running costs']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'orange','icon' => 'fa-file-invoice-dollar','title' => 'Expenses','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($kpi['expenses'])),'sub' => 'Running costs']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => $kpi['net'] >= 0 ? 'green' : 'red','icon' => 'fa-scale-balanced','title' => 'Cash position','value' => $m($kpi['net']),'sub' => $m($kpi['in']) . ' received from customers − spent']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($kpi['net'] >= 0 ? 'green' : 'red'),'icon' => 'fa-scale-balanced','title' => 'Cash position','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($kpi['net'])),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m($kpi['in']) . ' received from customers − spent')]); ?>
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


<section class="mb-6 rounded-xl bg-white" id="entry-form">
    <div class="<?php echo e($head); ?>"><?php echo e($edit ? 'Edit entry' : 'Add an entry'); ?></div>
    <form method="POST" action="<?php echo e($edit ? route('manager.cashbook.update', $edit) : route('manager.cashbook.store')); ?>" class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4"><?php echo csrf_field(); ?> <?php if($edit): ?><?php echo method_field('PUT'); ?><?php endif; ?>
        <?php $__currentLoopData = $keep; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><input type="hidden" name="<?php echo e($k); ?>" value="<?php echo e($v); ?>"><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php if($errors->any()): ?><div class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 sm:col-span-2 lg:col-span-4"><?php echo e($errors->first()); ?></div><?php endif; ?>
        <label class="text-sm font-medium">Date<input type="date" name="entry_date" value="<?php echo e(old('entry_date', ($edit?->entry_date ?? today())->format('Y-m-d'))); ?>" max="<?php echo e(today()->format('Y-m-d')); ?>" required class="<?php echo e($field); ?> mt-1"></label>
        <label class="text-sm font-medium">Type
            <select name="type" id="cbType" class="<?php echo e($field); ?> mt-1"><?php $__currentLoopData = CB::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if($type === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
        <label class="text-sm font-medium">Category
            <select name="category" id="cbCategory" class="<?php echo e($field); ?> mt-1" data-current="<?php echo e(old('category', $edit?->category)); ?>"></select></label>
        <label class="text-sm font-medium">Amount (GH₵)<input type="number" step="0.01" min="0.01" name="amount" value="<?php echo e(old('amount', $edit?->amount)); ?>" required class="<?php echo e($field); ?> mt-1"></label>
        <label class="text-sm font-medium sm:col-span-2">What was it for?<input name="description" value="<?php echo e(old('description', $edit?->description)); ?>" required maxlength="200" placeholder="e.g. 20 pairs of jeans from Kantamanto" class="<?php echo e($field); ?> mt-1"></label>
        <label class="text-sm font-medium">Paid to / supplier<input name="payee" value="<?php echo e(old('payee', $edit?->payee)); ?>" maxlength="120" class="<?php echo e($field); ?> mt-1"></label>
        <label class="text-sm font-medium">Receipt / reference<input name="reference" value="<?php echo e(old('reference', $edit?->reference)); ?>" maxlength="60" class="<?php echo e($field); ?> mt-1"></label>
        <label class="text-sm font-medium">Paid with
            <select name="method" class="<?php echo e($field); ?> mt-1"><?php $__currentLoopData = CB::METHODS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(old('method', $edit?->method ?? 'cash') === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
        <div class="flex items-end gap-2 sm:col-span-2">
            <button class="rounded-lg bg-[#1c3041] px-5 py-2 text-sm font-semibold text-white hover:bg-[#14212d]"><i class="fas fa-plus"></i> <?php echo e($edit ? 'Save changes' : 'Add to cash book'); ?></button>
            <?php if($edit): ?><a href="<?php echo e(route('manager.cashbook', $keep)); ?>" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Cancel</a><?php endif; ?>
        </div>
    </form>
</section>
<script>
    (function () {
        const cats = <?php echo json_encode(CB::CATEGORIES, 15, 512) ?>, type = document.getElementById('cbType'), sel = document.getElementById('cbCategory');
        function fill() {
            const cur = sel.dataset.current;
            sel.innerHTML = (cats[type.value] || []).map(c => '<option' + (c === cur ? ' selected' : '') + '>' + c.replace(/&/g, '&amp;') + '</option>').join('');
            sel.dataset.current = '';
        }
        type.addEventListener('change', fill); fill();
    })();
</script>

<div class="mb-6 grid gap-5 lg:grid-cols-3">
    <section class="rounded-xl bg-white overflow-hidden lg:col-span-1">
        <div class="<?php echo e($head); ?>">Where the money went</div>
        <table class="w-full text-sm">
            <?php $__empty_1 = true; $__currentLoopData = $byCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-2"><a class="hover:underline" href="<?php echo e(route('manager.cashbook', array_merge(request()->except('page', 'edit', 'category'), ['category' => $c->category]))); ?>"><?php echo e($c->category); ?></a><div class="text-xs text-slate-400"><?php echo e($c->n); ?> <?php echo e($c->n == 1 ? 'entry' : 'entries'); ?></div></td>
                    <td class="px-4 py-2 text-right font-semibold"><?php echo e($m($c->total)); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td class="p-6 text-center text-slate-400">Nothing recorded in this period.</td></tr> <?php endif; ?>
        </table>
    </section>

    <section class="rounded-xl bg-white overflow-hidden lg:col-span-2">
        <div class="<?php echo e($head); ?>">Entries (<?php echo e($entries->total()); ?>)</div>
        <form method="GET" class="grid grid-cols-2 gap-2 border-b border-slate-100 p-3 text-sm md:grid-cols-5">
            <?php $__currentLoopData = $keep; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><input type="hidden" name="<?php echo e($k); ?>" value="<?php echo e($v); ?>"><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <input name="q" value="<?php echo e(request('q')); ?>" placeholder="Search description, payee, reference…" class="<?php echo e($field); ?> col-span-2">
            <select name="type" class="<?php echo e($field); ?>"><option value="">All types</option><?php $__currentLoopData = CB::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(request('type') === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
            <select name="method" class="<?php echo e($field); ?>"><option value="">Any method</option><?php $__currentLoopData = CB::METHODS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(request('method') === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
            <button class="rounded-lg bg-[#1c3041] px-4 py-2 font-semibold text-white"><i class="fas fa-filter"></i> Filter</button>
        </form>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Date</th><th>Type</th><th>Details</th><th>Paid with</th><th class="text-right">Amount</th><th class="p-3"></th></tr></thead>
            <?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="border-t border-slate-100 align-top hover:bg-sky-50/50">
                    <td class="p-3 whitespace-nowrap text-slate-600"><?php echo e($e->entry_date->format('d M Y')); ?></td>
                    <td><span class="rounded-full px-2 py-0.5 text-xs font-medium <?php echo e($badge[$e->type] ?? ''); ?>"><?php echo e($e->type === 'purchase' ? 'Purchase' : 'Expense'); ?></span></td>
                    <td class="max-w-[280px]"><div class="font-medium"><?php echo e($e->description); ?></div>
                        <div class="text-xs text-slate-400"><?php echo e($e->category); ?><?php if($e->payee): ?> · <?php echo e($e->payee); ?><?php endif; ?> <?php if($e->reference): ?> · Ref <?php echo e($e->reference); ?><?php endif; ?></div></td>
                    <td class="text-xs text-slate-500"><?php echo e(CB::METHODS[$e->method] ?? $e->method); ?></td>
                    <td class="text-right font-semibold whitespace-nowrap"><?php echo e($m($e->amount)); ?></td>
                    <td class="p-3 whitespace-nowrap text-right text-xs">
                        <a class="text-[#1c3041] hover:underline" href="<?php echo e(route('manager.cashbook', array_merge(request()->except('page'), ['edit' => $e->id]))); ?>#entry-form">Edit</a> ·
                        <form method="POST" action="<?php echo e(route('manager.cashbook.destroy', $e)); ?>" class="inline" onsubmit="return confirm('Delete this entry?')"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="text-red-600 hover:underline">Delete</button></form>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="6" class="p-10 text-center text-slate-400"><i class="fas fa-book text-3xl mb-2 block"></i>No entries yet. Add your first purchase or expense above.</td></tr> <?php endif; ?>
            <?php if($entries->count()): ?><tr class="border-t bg-slate-50 font-semibold"><td colspan="4" class="p-3 text-right">Total for this filter</td><td class="text-right"><?php echo e($m($kpi['out'])); ?></td><td></td></tr><?php endif; ?>
        </table></div>
    </section>
</div>
<div class="mt-4"><?php echo e($entries->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/cashbook.blade.php ENDPATH**/ ?>