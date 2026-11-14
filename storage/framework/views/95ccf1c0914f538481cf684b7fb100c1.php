<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'color' => 'blue',     // blue | green | orange | red | slate | purple
    'icon' => 'fa-chart-simple',
    'title',
    'value',
    'sub' => null,         // small caption under the title (e.g. "This year")
    'badge' => null,       // small pill on the right (e.g. "86% of billed")
    'href' => null,
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
    'color' => 'blue',     // blue | green | orange | red | slate | purple
    'icon' => 'fa-chart-simple',
    'title',
    'value',
    'sub' => null,         // small caption under the title (e.g. "This year")
    'badge' => null,       // small pill on the right (e.g. "86% of billed")
    'href' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<?php
    // Flat, two-tone tiles: a darker icon block beside a solid colour body.
    $palette = [
        'blue'   => ['body' => 'bg-[#2c4a63]', 'icon' => 'bg-[#1c3041]'],
        'green'  => ['body' => 'bg-[#8cc152]', 'icon' => 'bg-[#74a83b]'],
        'orange' => ['body' => 'bg-[#1c3041]', 'icon' => 'bg-[#14212d]'],
        'red'    => ['body' => 'bg-[#da4453]', 'icon' => 'bg-[#c12f3e]'],
        'slate'  => ['body' => 'bg-[#5b6f8f]', 'icon' => 'bg-[#4a5c7a]'],
        'purple' => ['body' => 'bg-[#967adc]', 'icon' => 'bg-[#7d62c4]'],
    ][$color] ?? ['body' => 'bg-[#2c4a63]', 'icon' => 'bg-[#1c3041]'];
    $tag = $href ? 'a' : 'div';
    // Long values (e.g. "GH₵ 108,360.00") get a smaller size so they never clip in a 4-across row.
    $valueLength = mb_strlen(trim(strip_tags((string) $value)));
    $valueSize = $valueLength > 12 ? 'text-base xl:text-lg' : ($valueLength > 9 ? 'text-lg xl:text-xl' : 'text-2xl');
?>
<<?php echo e($tag); ?> <?php if($href): ?> href="<?php echo e($href); ?>" <?php endif; ?>
    <?php echo e($attributes->merge(['class' => 'group flex overflow-hidden rounded-md text-white shadow-sm transition ' . ($href ? 'hover:shadow-md hover:-translate-y-0.5' : '')])); ?>>
    <span class="flex w-16 shrink-0 items-center justify-center <?php echo e($palette['icon']); ?>">
        <i class="fas <?php echo e($icon); ?> text-2xl"></i>
    </span>
    <span class="flex min-w-0 flex-1 flex-wrap items-center justify-between gap-x-3 gap-y-1 px-4 py-3 <?php echo e($palette['body']); ?>">
        <span class="min-w-0 flex-1 basis-28">
            <span class="block text-base font-semibold leading-tight"><?php echo e($title); ?></span>
            <?php if($sub): ?><span class="block truncate text-xs opacity-90"><?php echo e($sub); ?></span><?php endif; ?>
        </span>
        <span class="text-right">
            <span class="block whitespace-nowrap <?php echo e($valueSize); ?> font-bold leading-tight tracking-tight"><?php echo e($value); ?></span>
            <?php if($badge): ?><span class="mt-0.5 inline-block whitespace-nowrap rounded-full bg-white/90 px-2 py-0.5 text-[11px] font-semibold text-gray-700"><?php echo e($badge); ?></span><?php endif; ?>
        </span>
    </span>
</<?php echo e($tag); ?>>
<?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/components/stat-tile.blade.php ENDPATH**/ ?>