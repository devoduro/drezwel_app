
<?php $__env->startSection('title', 'Delivery fees · Drezwel Go'); ?>
<?php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $field = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]';
    $head = 'px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm text-slate-800';
?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => 'Settings','title' => 'Delivery fees','subtitle' => 'Customers choose their region and town at checkout and are charged the fee you set here.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => 'Settings','title' => 'Delivery fees','subtitle' => 'Customers choose their region and town at checkout and are charged the fee you set here.']); ?>
     <?php $__env->slot('chips', null, []); ?> 
        <span><i class="fas fa-map"></i> <?php echo e($summary['regions']); ?> regions</span>
        <span><i class="fas fa-city"></i> <?php echo e($summary['priced']); ?> of <?php echo e($summary['towns']); ?> towns priced</span>
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

<div class="grid gap-5 lg:grid-cols-[240px_1fr]">
    
    <aside class="rounded-xl bg-white overflow-hidden self-start">
        <div class="<?php echo e($head); ?>">Regions</div>
        <nav class="max-h-[70vh] overflow-y-auto text-sm">
            <?php $__currentLoopData = $regions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('manager.fees', ['region' => $r->id])); ?>" class="flex items-center justify-between gap-2 border-t border-slate-100 px-4 py-2.5 <?php echo e($r->id === $region->id ? 'bg-[#e4eaf0] font-semibold text-[#1c3041]' : 'hover:bg-slate-50'); ?>">
                    <span class="truncate"><?php echo e($r->name); ?></span>
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] <?php echo e($r->priced > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'); ?>"><?php echo e($r->priced); ?>/<?php echo e($r->towns); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </nav>
    </aside>

    <div class="space-y-5 min-w-0">
        
        <section class="rounded-xl bg-white overflow-hidden">
            <div class="<?php echo e($head); ?>">Add towns to <?php echo e($region->name); ?></div>
            <form method="POST" action="<?php echo e(route('manager.fees.towns', $region)); ?>" class="grid gap-3 p-4 md:grid-cols-[1fr_auto]"><?php echo csrf_field(); ?>
                <div>
                    <textarea name="names" rows="4" required placeholder="One town per line. Add a fee after a comma to price it too:&#10;Kasoa, 30&#10;Winneba, 35&#10;Mankessim" class="<?php echo e($field); ?> w-full"><?php echo e(old('names')); ?></textarea>
                    <?php $__errorArgs = ['names'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="space-y-2 md:w-52">
                    <label class="block text-xs font-medium text-slate-600">Fee for lines without one (GH₵)<input type="number" step="0.01" min="0" name="fee" placeholder="optional" class="<?php echo e($field); ?> mt-1 w-full"></label>
                    <button class="w-full rounded-lg bg-[#1c3041] px-4 py-2 text-sm font-semibold text-white hover:bg-[#14212d]"><i class="fas fa-plus"></i> Add towns</button>
                </div>
            </form>
        </section>

        
        <form method="POST" action="<?php echo e(route('manager.fees.update', $region)); ?>" class="rounded-xl bg-white overflow-hidden" id="feesForm"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <button name="do" value="save" tabindex="-1" class="hidden" aria-hidden="true"></button>
            <div class="<?php echo e($head); ?> flex flex-wrap items-center justify-between gap-2"><span><?php echo e($region->name); ?> · <?php echo e($zones->count()); ?> towns</span>
                <span class="text-xs font-normal text-slate-500">A town with no fee of its own is charged the region fee (set in the box below).</span></div>

            <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 bg-slate-50 p-3 text-sm">
                <label class="flex items-center gap-2"><input type="checkbox" id="pickAll" class="accent-[#1c3041]"> Select all</label>
                <label class="flex items-center gap-2 text-slate-600"><input type="checkbox" name="all" value="1" class="accent-[#1c3041]"> Apply to <strong>every</strong> town in <?php echo e($region->name); ?></label>
                <span class="mx-1 hidden h-5 border-l border-slate-300 sm:block"></span>
                <input type="number" step="0.01" min="0" name="bulk_fee" placeholder="Fee GH₵" class="<?php echo e($field); ?> w-28">
                <button name="do" value="set_fee" class="rounded-lg bg-emerald-600 px-3 py-2 font-semibold text-white hover:bg-emerald-700">Set fee for selected</button>
                <button name="do" value="activate" class="rounded-lg bg-white px-3 py-2 ring-1 ring-slate-300">Switch on</button>
                <button name="do" value="deactivate" class="rounded-lg bg-white px-3 py-2 ring-1 ring-slate-300">Switch off</button>
                <button name="do" value="clear_fee" class="rounded-lg bg-white px-3 py-2 ring-1 ring-slate-300">Clear fee</button>
                <button name="do" value="delete" onclick="return confirm('Delete the selected towns?')" class="rounded-lg bg-white px-3 py-2 text-red-600 ring-1 ring-red-200">Delete</button>
            </div>

            <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 p-3 text-sm">
                <label class="flex items-center gap-2 font-medium">Other towns in <?php echo e($region->name); ?> (GH₵)
                    <input type="number" step="0.01" min="0" name="default_fee" value="<?php echo e(old('default_fee', $region->default_fee)); ?>" placeholder="shop default" class="<?php echo e($field); ?> w-32"></label>
                <span class="text-xs text-slate-500">Charged for any town below with no fee of its own, and when a customer picks "Another town". Leave it empty to use the shop-wide fee from Settings.</span>
            </div>

            <div class="overflow-x-auto"><table class="w-full text-sm">
                <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3 w-10"></th><th>Town / area</th><th>Fee (GH₵)</th><th class="text-center">Shown to customers</th><th class="p-3"></th></tr></thead>
                <?php $__empty_1 = true; $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $z): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-t border-slate-100 <?php echo e($z->fee === null ? 'bg-amber-50/40' : ''); ?>">
                        <td class="p-3"><input type="checkbox" name="ids[]" value="<?php echo e($z->id); ?>" class="pick accent-[#1c3041]"></td>
                        <td class="font-medium"><?php echo e($z->name); ?><?php if($z->fee === null): ?><span class="ml-2 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-800">REGION FEE</span><?php endif; ?></td>
                        <td><input type="number" step="0.01" min="0" name="fees[<?php echo e($z->id); ?>]" value="<?php echo e($z->fee); ?>" placeholder="—" class="<?php echo e($field); ?> w-28"></td>
                        <td class="text-center"><input type="checkbox" name="active[<?php echo e($z->id); ?>]" value="1" <?php if($z->is_active): echo 'checked'; endif; ?> class="accent-[#1c3041]"></td>
                        <td class="p-3 text-right text-xs"><button form="del<?php echo e($z->id); ?>" class="text-red-600 hover:underline" onclick="return confirm('Delete <?php echo e(addslashes($z->name)); ?>?')">Delete</button></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?> <tr><td colspan="5" class="p-8 text-center text-slate-400">No towns yet. Add some above.</td></tr> <?php endif; ?>
            </table></div>

            <div class="flex items-center justify-between gap-3 border-t border-slate-100 p-3">
                <span class="text-xs text-slate-500">Edit any fees above, then save them all at once.</span>
                <button name="do" value="save" class="rounded-lg bg-[#1c3041] px-5 py-2 text-sm font-semibold text-white hover:bg-[#14212d]"><i class="fas fa-floppy-disk"></i> Save all fees</button>
            </div>
        </form>
        <?php $__currentLoopData = $zones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $z): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><form id="del<?php echo e($z->id); ?>" method="POST" action="<?php echo e(route('manager.fees.destroy', $z)); ?>" class="hidden"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?></form><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<script>
    document.getElementById('pickAll').addEventListener('change', e => document.querySelectorAll('.pick').forEach(c => c.checked = e.target.checked));
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/fees.blade.php ENDPATH**/ ?>