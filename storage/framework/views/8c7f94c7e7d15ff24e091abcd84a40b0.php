<?php $__env->startSection('title', 'Riders · Drezwel Go'); ?>
<?php $input = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm w-full focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#42a5dc]'; ?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => 'Fleet','title' => 'Riders & drivers','subtitle' => 'Add riders, edit their details and see who is out on the road.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => 'Fleet','title' => 'Riders & drivers','subtitle' => 'Add riders, edit their details and see who is out on the road.']); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-user-check"></i> <?php echo e($riders->where('is_active', true)->count()); ?> active</span>
        <span><i class="fas fa-user-slash"></i> <?php echo e($riders->where('is_active', false)->count()); ?> inactive</span>
     <?php $__env->endSlot(); ?>
    <a href="<?php echo e(route('manager.remittances')); ?>"><i class="fas fa-cash-register"></i> Cash &amp; items</a>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'blue','icon' => 'fa-users','title' => 'Riders','value' => $riders->count(),'sub' => $riders->where('is_active', true)->count() . ' active']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'blue','icon' => 'fa-users','title' => 'Riders','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($riders->count()),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($riders->where('is_active', true)->count() . ' active')]); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'purple','icon' => 'fa-road','title' => 'On a run','value' => $riders->sum('open_deliveries'),'sub' => 'Assigned or out for delivery']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'purple','icon' => 'fa-road','title' => 'On a run','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($riders->sum('open_deliveries')),'sub' => 'Assigned or out for delivery']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'green','icon' => 'fa-circle-check','title' => 'Delivered','value' => $riders->sum('total_deliveries'),'sub' => 'All riders, all time']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'green','icon' => 'fa-circle-check','title' => 'Delivered','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($riders->sum('total_deliveries')),'sub' => 'All riders, all time']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.stat-tile','data' => ['color' => 'orange','icon' => 'fa-wallet','title' => 'Cash with riders','value' => 'GH₵ ' . number_format($cashHeld, 2),'sub' => 'Not yet handed in','href' => route('manager.remittances')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('stat-tile'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'orange','icon' => 'fa-wallet','title' => 'Cash with riders','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('GH₵ ' . number_format($cashHeld, 2)),'sub' => 'Not yet handed in','href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('manager.remittances'))]); ?>
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

<details class="bg-white rounded-xl mb-5 overflow-hidden group" <?php echo e($errors->any() ? 'open' : ''); ?>>
    <summary class="cursor-pointer px-4 py-3 font-semibold text-sm border-l-4 border-l-[#8cc152] flex items-center gap-2"><i class="fas fa-user-plus text-[#74a83b]"></i> Add a rider</summary>
    <form method="POST" action="<?php echo e(route('manager.riders.store')); ?>" class="grid sm:grid-cols-2 lg:grid-cols-5 gap-2 p-4 border-t border-gray-100"><?php echo csrf_field(); ?>
        <input name="name" placeholder="Name" required class="<?php echo e($input); ?>">
        <input name="phone" placeholder="Phone (login)" required class="<?php echo e($input); ?>">
        <input name="email" type="email" placeholder="Email (optional)" class="<?php echo e($input); ?>">
        <input name="password" type="text" placeholder="Password (min 6)" required class="<?php echo e($input); ?>">
        <button class="rounded-lg bg-[#8cc152] hover:bg-[#74a83b] text-white px-4 py-2 text-sm font-semibold transition">Add rider</button>
    </form>
</details>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
    <?php $__currentLoopData = $riders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="min-w-0 bg-white rounded-xl p-4 transition hover:shadow-md <?php echo e($r->is_active ? '' : 'opacity-60'); ?>">
            <div class="flex items-center gap-3">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-gradient-to-br from-[#42a5dc] to-[#2c8fc6] text-lg font-bold text-white shadow"><?php echo e(mb_strtoupper(mb_substr($r->name, 0, 1))); ?></span>
                <div class="min-w-0 flex-1">
                    <div class="font-semibold truncate"><?php echo e($r->name); ?></div>
                    <div class="text-xs text-slate-500 truncate"><i class="fas fa-phone"></i> <?php echo e($r->phone ?: 'No phone'); ?></div>
                    <div class="text-xs text-slate-400 truncate"><i class="fas fa-envelope"></i> <?php echo e($r->email ?: '—'); ?></div>
                </div>
                <span class="rounded-full px-2 py-0.5 text-xs font-medium <?php echo e($r->is_active ? 'bg-green-100 text-green-800' : 'bg-slate-200 text-slate-600'); ?>"><?php echo e($r->is_active ? 'Active' : 'Inactive'); ?></span>
            </div>
            <?php if (! ($r->phone)): ?>
                <form method="POST" action="<?php echo e(route('manager.riders.update', $r)); ?>" class="mt-3 flex gap-2 rounded-lg bg-amber-50 border border-amber-200 p-2"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                    <input type="hidden" name="name" value="<?php echo e($r->name); ?>">
                    <input type="hidden" name="email" value="<?php echo e($r->email); ?>">
                    <input name="phone" inputmode="tel" required placeholder="Add phone e.g. 0241234567" class="<?php echo e($input); ?> min-w-0 flex-1">
                    <button class="rounded-lg bg-[#f5922f] hover:bg-[#de7a14] text-white px-3 text-sm font-semibold transition whitespace-nowrap">Save</button>
                </form>
            <?php endif; ?>
            <div class="mt-4 grid grid-cols-2 gap-2 text-center">
                <div class="rounded-lg bg-slate-50 py-2"><div class="text-lg font-bold text-green-600"><?php echo e($r->total_deliveries); ?></div><div class="text-[11px] uppercase tracking-wide text-slate-500">Delivered</div></div>
                <div class="rounded-lg bg-slate-50 py-2"><div class="text-lg font-bold text-[#2c8fc6]"><?php echo e($r->open_deliveries); ?></div><div class="text-[11px] uppercase tracking-wide text-slate-500">Open runs</div></div>
            </div>
            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-sm">
                <details class="relative">
                    <summary class="cursor-pointer font-medium text-[#2c8fc6]"><i class="fas fa-pen"></i> Edit</summary>
                    <form method="POST" action="<?php echo e(route('manager.riders.update', $r)); ?>" class="absolute left-0 z-10 mt-2 w-60 space-y-2 rounded-lg border bg-white p-3 shadow-lg"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                        <input name="name" value="<?php echo e($r->name); ?>" required class="<?php echo e($input); ?>">
                        <input name="phone" value="<?php echo e($r->phone); ?>" placeholder="Phone" required class="<?php echo e($input); ?>">
                        <input name="email" value="<?php echo e($r->email); ?>" placeholder="Email" class="<?php echo e($input); ?>">
                        <input name="password" placeholder="New password (optional)" class="<?php echo e($input); ?>">
                        <button class="w-full rounded bg-[#42a5dc] text-white py-1.5 font-semibold">Save</button>
                    </form>
                </details>
                <a href="<?php echo e(route('manager.riders.show', $r)); ?>" class="font-medium text-slate-600 hover:text-slate-900"><i class="fas fa-clock-rotate-left"></i> History</a>
                <form method="POST" action="<?php echo e(route('manager.riders.toggle', $r)); ?>"><?php echo csrf_field(); ?>
                    <button class="text-slate-500 hover:text-slate-800"><i class="fas <?php echo e($r->is_active ? 'fa-user-slash' : 'fa-user-check'); ?>"></i> <?php echo e($r->is_active ? 'Deactivate' : 'Activate'); ?></button>
                </form>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/riders/index.blade.php ENDPATH**/ ?>