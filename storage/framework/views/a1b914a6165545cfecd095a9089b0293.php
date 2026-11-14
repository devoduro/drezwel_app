<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'eyebrow' => null,     // small line above the title (usually the date)
    'subtitle' => null,
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'title',
    'eyebrow' => null,     // small line above the title (usually the date)
    'subtitle' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div <?php echo e($attributes->merge(['class' => 'relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#1c3041] to-[#14212d] p-7 text-white shadow-lg'])); ?>>
    <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-[#8fb8d8]/20 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 left-1/3 h-64 w-64 rounded-full bg-indigo-500/15 blur-3xl"></div>
    <div class="pointer-events-none absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 22px 22px;"></div>

    <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <?php if($eyebrow): ?><p class="text-sm font-medium text-[#7cc4ea]"><?php echo e($eyebrow); ?></p><?php endif; ?>
            <h2 class="mt-1 text-3xl font-bold tracking-tight"><?php echo e($title); ?></h2>
            <?php if($subtitle): ?><p class="mt-1 max-w-xl text-sm text-slate-300"><?php echo e($subtitle); ?></p><?php endif; ?>

            <?php if(isset($chips)): ?>
                <div class="mt-4 flex flex-wrap gap-2 text-xs font-medium [&>span]:inline-flex [&>span]:items-center [&>span]:gap-1.5 [&>span]:rounded-full [&>span]:bg-white/10 [&>span]:px-3 [&>span]:py-1 [&>span]:ring-1 [&>span]:ring-white/15">
                    <?php echo e($chips); ?>

                </div>
            <?php endif; ?>
        </div>

        <?php if(!$slot->isEmpty()): ?>
            <div class="flex flex-wrap gap-3 [&>a]:inline-flex [&>a]:items-center [&>a]:gap-2 [&>a]:rounded-xl [&>a]:px-4 [&>a]:py-2.5 [&>a]:text-sm [&>a]:font-semibold [&>a]:transition [&>a]:bg-white/10 [&>a]:text-white [&>a]:ring-1 [&>a]:ring-white/20 hover:[&>a]:bg-white/20 [&>a.primary]:bg-white [&>a.primary]:text-slate-800 [&>a.primary]:ring-0 hover:[&>a.primary]:bg-gray-100">
                <?php echo e($slot); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/components/dashboard-hero.blade.php ENDPATH**/ ?>