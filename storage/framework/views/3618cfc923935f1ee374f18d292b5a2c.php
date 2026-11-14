<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status']));

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

foreach (array_filter((['status']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>
<?php
    $map = [
        'pending' => 'bg-[#e4eaf0] text-[#1c3041]',
        'assigned' => 'bg-blue-100 text-blue-800',
        'out_for_delivery' => 'bg-indigo-100 text-indigo-800',
        'delivered' => 'bg-green-100 text-green-800',
        'undelivered' => 'bg-red-100 text-red-800',
        'cancelled' => 'bg-slate-200 text-slate-600',
    ];
?>
<span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap <?php echo e($map[$status] ?? 'bg-slate-100'); ?>"><?php echo e(\App\Models\Delivery::STATUSES[$status] ?? $status); ?></span>
<?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/components/badge.blade.php ENDPATH**/ ?>