<?php $__env->startSection('title', ($product->exists ? 'Edit' : 'New') . ' product · Drezwel Go'); ?>
<?php
    $v = fn ($k, $fallback = null) => old($k, $fallback);
    $input = 'mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#42a5dc]';
?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6 !p-5','eyebrow' => 'Inventory','title' => $product->exists ? 'Edit ' . $product->name : 'Add product','subtitle' => 'Set the cost and selling price so reports can show your profit.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6 !p-5','eyebrow' => 'Inventory','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($product->exists ? 'Edit ' . $product->name : 'Add product'),'subtitle' => 'Set the cost and selling price so reports can show your profit.']); ?>
    <a href="<?php echo e($product->exists ? route('manager.products.show', $product) : route('manager.products.index')); ?>"><i class="fas fa-arrow-left"></i> Back</a>
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

<form method="POST" action="<?php echo e($product->exists ? route('manager.products.update', $product) : route('manager.products.store')); ?>" class="bg-white rounded-xl p-5 grid md:grid-cols-2 gap-4 max-w-3xl">
    <?php echo csrf_field(); ?> <?php if($product->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
    <label class="text-sm font-medium md:col-span-2">Product name<input name="name" required value="<?php echo e($v('name', $product->name)); ?>" class="<?php echo e($input); ?>" placeholder="e.g. Baggy jeans - blue, size 32"></label>
    <label class="text-sm font-medium">SKU / code (optional)<input name="sku" value="<?php echo e($v('sku', $product->sku)); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">Category<input name="category" list="cats" value="<?php echo e($v('category', $product->category)); ?>" class="<?php echo e($input); ?>" placeholder="Jeans, Sneakers, …"><datalist id="cats"><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c); ?>"><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></datalist></label>
    <label class="text-sm font-medium">Cost price (GH₵) - what you pay<input type="number" step="0.01" min="0" name="cost_price" required value="<?php echo e($v('cost_price', $product->cost_price ?? 0)); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">Selling price (GH₵)<input type="number" step="0.01" min="0" name="selling_price" required value="<?php echo e($v('selling_price', $product->selling_price ?? 0)); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">Alert me when stock falls to<input type="number" min="0" name="reorder_level" required value="<?php echo e($v('reorder_level', $product->reorder_level)); ?>" class="<?php echo e($input); ?>"></label>
    <?php if (! ($product->exists)): ?>
        <label class="text-sm font-medium">Opening stock (units on hand now)<input type="number" min="0" name="opening_stock" value="<?php echo e($v('opening_stock', 0)); ?>" class="<?php echo e($input); ?>"></label>
    <?php endif; ?>
    <label class="text-sm font-medium md:col-span-2">Notes<textarea name="notes" rows="2" class="<?php echo e($input); ?>"><?php echo e($v('notes', $product->notes)); ?></textarea></label>
    <div class="md:col-span-2 flex gap-3">
        <button class="rounded-lg bg-[#42a5dc] hover:bg-[#2c8fc6] text-white px-5 py-2 font-semibold transition"><?php echo e($product->exists ? 'Save changes' : 'Add product'); ?></button>
        <a href="<?php echo e(route('manager.products.index')); ?>" class="px-4 py-2 text-slate-500">Cancel</a>
    </div>
    <?php if($product->exists): ?><p class="md:col-span-2 text-xs text-slate-500">To change how many are in stock, use "Update stock" on the product page so the change is recorded.</p><?php endif; ?>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/products/form.blade.php ENDPATH**/ ?>