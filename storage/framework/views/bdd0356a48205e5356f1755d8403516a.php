
<?php $__env->startSection('title', 'Settings · Drezwel Go'); ?>
<?php
    $input = 'mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#42a5dc]';
    $v = fn ($k) => old($k, $s[$k] ?? '');
?>
<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginalb08eb6e359b0cda244088e0d0db6136c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb08eb6e359b0cda244088e0d0db6136c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard-hero','data' => ['class' => 'mb-6 !p-5','eyebrow' => 'Business','title' => 'Settings','subtitle' => 'These details appear on your online store and on every invoice.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard-hero'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-6 !p-5','eyebrow' => 'Business','title' => 'Settings','subtitle' => 'These details appear on your online store and on every invoice.']); ?>
    <a href="<?php echo e(route('shop.index')); ?>" target="_blank"><i class="fas fa-up-right-from-square"></i> View online store</a>
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

<form method="POST" enctype="multipart/form-data" action="<?php echo e(route('manager.settings.update')); ?>" class="bg-white rounded-xl p-5 grid md:grid-cols-2 gap-4 max-w-4xl"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
    <label class="text-sm font-medium">Business name<input name="business_name" required value="<?php echo e($v('business_name')); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">Tagline<input name="tagline" value="<?php echo e($v('tagline')); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">Phone(s) shown to customers - separate several with /<input name="phone" value="<?php echo e($v('phone')); ?>" inputmode="tel" class="<?php echo e($input); ?>" placeholder="0200680701/0203264474"></label>
    <label class="text-sm font-medium">WhatsApp number<input name="whatsapp" value="<?php echo e($v('whatsapp')); ?>" inputmode="tel" class="<?php echo e($input); ?>" placeholder="233241234567"></label>
    <label class="text-sm font-medium">Email<input type="email" name="email" value="<?php echo e($v('email')); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">Main website<input name="website" value="<?php echo e($v('website')); ?>" class="<?php echo e($input); ?>" placeholder="https://drezwell.com"></label>
    <label class="text-sm font-medium md:col-span-2">Address (shown on invoices)<input name="address" value="<?php echo e($v('address')); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">Delivery fee on website orders (GH₵)<input type="number" step="0.01" min="0" name="default_delivery_fee" required value="<?php echo e($v('default_delivery_fee')); ?>" class="<?php echo e($input); ?>"><span class="text-xs font-normal text-slate-500">Added to every online order. Leave 0 to confirm the fee by phone.</span></label>
    <label class="text-sm font-medium">Invoice thank-you line<input name="invoice_note" value="<?php echo e($v('invoice_note')); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium md:col-span-2">Invoice terms (small print, e.g. returns policy)<input name="invoice_terms" value="<?php echo e($v('invoice_terms')); ?>" class="<?php echo e($input); ?>"></label>
    <div class="md:col-span-2 border-t pt-4 font-semibold"><i class="fas fa-image text-[#42a5dc]"></i> Home page picture</div>
    <div class="md:col-span-2 text-sm">
        <?php if(! empty($s['hero_image'])): ?>
            <div class="relative mb-3 h-40 overflow-hidden rounded-xl bg-slate-900">
                <img src="<?php echo e(asset('uploads/' . $s['hero_image'])); ?>" alt="" class="h-full w-full object-cover opacity-60">
                <div class="absolute inset-0 grid place-items-center text-white font-bold text-xl">A step above class.</div>
            </div>
            <label class="mb-2 flex items-center gap-2 text-red-600"><input type="checkbox" name="remove_hero_image" value="1"> Remove the current picture</label>
        <?php endif; ?>
        <label class="block font-medium">Upload a picture (wide photo, at least 1600 x 800 px works best)
            <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-[#42a5dc] file:px-4 file:py-2 file:text-white file:font-semibold"></label>
        <p class="mt-1 text-xs text-slate-500">It is shown behind the big headline on the home page, under a dark overlay so the words stay easy to read. Without a picture, a dark blue background is used.</p>
    </div>

    <div class="md:col-span-2 border-t pt-4 font-semibold"><i class="fas fa-share-nodes text-[#42a5dc]"></i> Website: contact &amp; social links</div>
    <label class="text-sm font-medium">Second phone / helpline<input name="helpline" value="<?php echo e($v('helpline')); ?>" class="<?php echo e($input); ?>" placeholder="+233 24 695 6958"></label>
    <label class="text-sm font-medium">Opening hours<input name="hours" value="<?php echo e($v('hours')); ?>" class="<?php echo e($input); ?>" placeholder="Mon-Sat, 8am - 7pm"></label>
    <label class="text-sm font-medium">WhatsApp channel link<input name="whatsapp_channel" value="<?php echo e($v('whatsapp_channel')); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">Facebook<input name="facebook" value="<?php echo e($v('facebook')); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">Instagram<input name="instagram" value="<?php echo e($v('instagram')); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">X (Twitter)<input name="x" value="<?php echo e($v('x')); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">TikTok<input name="tiktok" value="<?php echo e($v('tiktok')); ?>" class="<?php echo e($input); ?>"></label>
    <label class="text-sm font-medium">YouTube<input name="youtube" value="<?php echo e($v('youtube')); ?>" class="<?php echo e($input); ?>"></label>

    <div class="md:col-span-2 border-t pt-4 font-semibold"><i class="fas fa-file-lines text-[#42a5dc]"></i> Website pages <span class="font-normal text-xs text-slate-500">(leave a blank line between paragraphs)</span></div>
    <label class="text-sm font-medium md:col-span-2">About us<textarea name="about_text" rows="6" class="<?php echo e($input); ?>"><?php echo e($v('about_text')); ?></textarea></label>
    <label class="text-sm font-medium md:col-span-2">Delivery &amp; payment<textarea name="delivery_text" rows="8" class="<?php echo e($input); ?>"><?php echo e($v('delivery_text')); ?></textarea></label>
    <label class="text-sm font-medium md:col-span-2">Return policy<textarea name="policy_returns" rows="6" class="<?php echo e($input); ?>"><?php echo e($v('policy_returns')); ?></textarea></label>
    <label class="text-sm font-medium md:col-span-2">Terms &amp; conditions<textarea name="policy_terms" rows="8" class="<?php echo e($input); ?>"><?php echo e($v('policy_terms')); ?></textarea></label>
    <label class="text-sm font-medium md:col-span-2">Privacy policy<textarea name="policy_privacy" rows="8" class="<?php echo e($input); ?>"><?php echo e($v('policy_privacy')); ?></textarea><span class="text-xs font-normal text-slate-500">The starting text for the policies is a draft. Please read it and change it to match how you really work.</span></label>

    <label class="flex items-center gap-2 text-sm font-medium md:col-span-2"><input type="checkbox" name="store_open" value="1" <?php if(old('store_open', $s['store_open'] ?? '1') == '1'): echo 'checked'; endif; ?>> Accept orders on the online store (untick to pause - customers will be asked to call)</label>
    <div class="md:col-span-2"><button class="rounded-lg bg-[#42a5dc] hover:bg-[#2c8fc6] text-white px-6 py-2.5 font-semibold transition">Save settings</button></div>
</form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/manager/settings.blade.php ENDPATH**/ ?>