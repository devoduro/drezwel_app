
<?php $__env->startSection('title', 'Edit ' . $title . ' · Drezwel Go'); ?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6','eyebrow' => 'Website pages','title' => $title,'subtitle' => $help]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6','eyebrow' => 'Website pages','title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'subtitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($help)]); ?>
    <a href="<?php echo e(route('manager.pages')); ?>"><i class="fas fa-arrow-left"></i> All pages</a>
    <a href="<?php echo e($url); ?>" target="_blank"><i class="fas fa-arrow-up-right-from-square"></i> View page</a>
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

<?php if($usingDraft): ?>
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"><i class="fas fa-circle-info"></i> This is our <strong>suggested draft</strong>. Read it, change anything that does not match how you work, then save.</div>
<?php endif; ?>

<div class="grid gap-5 lg:grid-cols-[1fr_300px]">
    <form method="POST" action="<?php echo e(route('manager.pages.update', $slug)); ?>" class="rounded-xl bg-white p-4"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
        <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <textarea name="body" id="body" rows="26" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-[13px] leading-relaxed focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]"><?php echo e(old('body', $body)); ?></textarea>
        <div class="mt-3 flex flex-wrap items-center gap-3">
            <button class="rounded-lg bg-[#1c3041] px-5 py-2 font-semibold text-white hover:bg-[#14212d]"><i class="fas fa-floppy-disk"></i> Save page</button>
            <button type="button" id="loadDraft" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200"><i class="fas fa-rotate-left"></i> Load the suggested draft</button>
            <span class="text-xs text-slate-500">Leave it empty and save to go back to the suggested draft.</span>
        </div>
    </form>

    <aside class="space-y-4 self-start text-sm">
        <div class="rounded-xl bg-white p-4">
            <div class="mb-2 font-semibold text-slate-800">Formatting</div>
            <ul class="space-y-1.5 text-slate-600">
                <li><code class="rounded bg-slate-100 px-1"># Heading</code>, <code class="rounded bg-slate-100 px-1">## Smaller heading</code></li>
                <li><code class="rounded bg-slate-100 px-1">**bold text**</code> for <strong>bold</strong></li>
                <li><code class="rounded bg-slate-100 px-1">- item</code> for a bullet list</li>
                <li><code class="rounded bg-slate-100 px-1">1. step</code> for a numbered list</li>
                <li><code class="rounded bg-slate-100 px-1">[words](https://…)</code> for a link</li>
                <li>Leave a blank line between paragraphs.</li>
            </ul>
            <?php if($slug === 'faqs'): ?><p class="mt-3 rounded-lg bg-sky-50 p-2 text-sky-900">Write each question as <code>### Your question</code> and put the answer on the lines below it.</p><?php endif; ?>
        </div>
        <div class="rounded-xl bg-white p-4">
            <div class="mb-2 font-semibold text-slate-800">Filled in for you</div>
            <p class="mb-2 text-slate-500">Type these and they are replaced with your details from Settings:</p>
            <div class="flex flex-wrap gap-1.5 font-mono text-xs">
                <?php $__currentLoopData = ['{business}', '{phone}', '{email}', '{address}', '{hours}', '{website}', '{shop}', '{contact}', '{track}', '{delivery}', '{returns}']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><code class="rounded bg-slate-100 px-1.5 py-0.5"><?php echo e($t); ?></code><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </aside>
</div>
<script type="application/json" id="draftText"><?php echo json_encode($draft, 15, 512) ?></script>
<script>
    document.getElementById('loadDraft').addEventListener('click', function () {
        if (confirm('Replace what is in the box with the suggested draft? Nothing is saved until you click Save page.')) {
            document.getElementById('body').value = JSON.parse(document.getElementById('draftText').textContent);
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/pages/edit.blade.php ENDPATH**/ ?>