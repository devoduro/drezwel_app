
<?php
    $wholesale = $type === 'wholesale';
    $S = fn ($k) => \App\Models\Setting::get($k);
    $field = 'mt-1 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-[15px] focus:border-jm-orange focus:outline-none focus:ring-1 focus:ring-jm-orange';
    $wa = preg_replace('/\D/', '', (string) $S('whatsapp'));
    $phones = \App\Models\Setting::phones();
    $faq = [
        ['How do I order?', 'Add items to your cart on this website and check out, or call us' . ($phones ? ' on ' . $phones[0][0] : '') . ' and we will place the order for you. We always confirm by phone before dispatch.'],
        ['How do I pay?', 'You pay on delivery, in cash or MoMo, to the rider when your order arrives. Please check your items first.'],
        ['How long does delivery take?', 'In Accra a rider brings your order to your door. For other regions we send by station bus or parcel office, and you can also pick up from us. We will tell you the timing when we call to confirm.'],
        ['How do I track my order?', 'Open Track your order and enter your order number and phone number, or sign in with your phone number to see all your orders. When the rider sets off you also get a 4-digit code by SMS.'],
        ['Can I return an item?', 'Please read our return policy. Check your order in front of the rider and tell us the same day if something is wrong.'],
    ];
?>
<?php $__env->startSection('title', ($wholesale ? 'Wholesale orders' : 'Contact us') . ' | ' . \App\Models\Setting::get('business_name')); ?>
<?php $__env->startSection('description', $wholesale ? 'Buy Drezwel fashion at wholesale prices. Tell us what you need and we will call you back.' : 'Call, WhatsApp or message Drezwel. We are in Accra, Ghana.'); ?>
<?php $__env->startSection('content'); ?>
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 py-3 space-y-3">
    <nav class="text-[13px] text-jm-grey">
        <a class="hover:text-jm-orange" href="<?php echo e(route('home')); ?>">Home</a> <i class="fas fa-chevron-right mx-1 text-[8px]"></i>
        <span>Help center</span> <i class="fas fa-chevron-right mx-1 text-[8px]"></i> <span class="text-jm-dark"><?php echo e($wholesale ? 'Wholesale orders' : 'Contact us'); ?></span>
    </nav>

    <div class="grid items-start gap-3 lg:grid-cols-[250px_1fr]">
        <?php echo $__env->make('site._helpmenu', ['active' => $wholesale ? 'wholesale' : 'contact'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="min-w-0 space-y-3">
            
            <section class="rounded-xl bg-white p-5">
                <h1 class="text-[22px] font-bold"><?php echo e($wholesale ? 'Buy at wholesale price' : 'Contact us'); ?></h1>
                <p class="mt-1 max-w-2xl text-[#535357]"><?php echo e($wholesale ? 'Stocking a shop or buying in bulk? Tell us what you are looking for and how many, and we will call you with wholesale prices.' : 'Questions about an order or a product? Call us, chat on WhatsApp, or send a message and we will call you back.'); ?></p>
            </section>

            
            <div class="grid gap-3 sm:grid-cols-3">
                <?php $__currentLoopData = $phones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => [$disp, $digits]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($i < 1): ?>
                        <a href="tel:<?php echo e($digits); ?>" class="flex items-center gap-3 rounded-xl bg-white p-4 transition hover:shadow-[0_4px_16px_rgba(0,0,0,.12)]"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-[#fdecd5] text-xl text-jm-orange"><i class="fas fa-phone-volume"></i></span><div class="min-w-0"><div class="text-[13px] text-jm-grey">Call us</div><div class="truncate font-bold"><?php echo e($disp); ?></div></div></a>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($wa): ?>
                    <a href="https://wa.me/<?php echo e($wa); ?>" target="_blank" rel="noopener" class="flex items-center gap-3 rounded-xl bg-white p-4 transition hover:shadow-[0_4px_16px_rgba(0,0,0,.12)]"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-green-100 text-xl text-green-600"><i class="fab fa-whatsapp"></i></span><div class="min-w-0"><div class="text-[13px] text-jm-grey">WhatsApp</div><div class="truncate font-bold">Chat with us</div></div></a>
                <?php endif; ?>
                <?php if($S('email')): ?>
                    <a href="mailto:<?php echo e($S('email')); ?>" class="flex items-center gap-3 rounded-xl bg-white p-4 transition hover:shadow-[0_4px_16px_rgba(0,0,0,.12)]"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-sky-100 text-xl text-sky-600"><i class="fas fa-envelope"></i></span><div class="min-w-0"><div class="text-[13px] text-jm-grey">Email</div><div class="truncate font-bold"><?php echo e($S('email')); ?></div></div></a>
                <?php endif; ?>
            </div>

            
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide"><?php echo e($wholesale ? 'Send a wholesale enquiry' : 'Send us a message'); ?></div>
                <form method="POST" action="<?php echo e(route('site.send')); ?>" class="relative space-y-4 p-4 sm:p-5"><?php echo csrf_field(); ?>
                    <input type="hidden" name="type" value="<?php echo e($type); ?>">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block text-sm font-medium">Your name<input name="name" value="<?php echo e(old('name')); ?>" required autocomplete="name" class="<?php echo e($field); ?>"></label>
                        <label class="block text-sm font-medium">Phone number<input name="phone" value="<?php echo e(old('phone')); ?>" required inputmode="tel" autocomplete="tel" placeholder="0241234567" class="<?php echo e($field); ?>"></label>
                        <label class="block text-sm font-medium">Email (optional)<input type="email" name="email" value="<?php echo e(old('email')); ?>" autocomplete="email" class="<?php echo e($field); ?>"></label>
                        <label class="block text-sm font-medium"><?php echo e($wholesale ? 'What are you looking for?' : 'Subject (optional)'); ?><input name="subject" value="<?php echo e(old('subject')); ?>" class="<?php echo e($field); ?>" placeholder="<?php echo e($wholesale ? 'e.g. 50 pieces of baggy jeans' : ''); ?>"></label>
                    </div>
                    <label class="block text-sm font-medium"><?php echo e($wholesale ? 'Tell us more: items, sizes, quantities, where you sell' : 'Your message'); ?>

                        <textarea name="message" rows="5" required class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-[15px] focus:border-jm-orange focus:outline-none focus:ring-1 focus:ring-jm-orange"><?php echo e(old('message')); ?></textarea></label>
                    <div style="position:absolute;left:-9999px" aria-hidden="true"><label>Company website<input name="company_website" tabindex="-1" autocomplete="off"></label></div>
                    <?php if($errors->any()): ?><div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($e); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><?php endif; ?>
                    <div class="flex flex-wrap items-center gap-3">
                        <button class="h-11 rounded-lg bg-jm-orange px-8 text-[14px] font-bold uppercase tracking-wide text-white shadow transition hover:bg-[#e07b10]"><?php echo e($wholesale ? 'Send enquiry' : 'Send message'); ?></button>
                        <span class="text-xs text-jm-grey">We usually reply by phone, so please check your number.</span>
                    </div>
                </form>
            </section>

            <?php if (! ($wholesale)): ?>
                
                <section class="overflow-hidden rounded-xl bg-white">
                    <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Common questions</div>
                    <div class="divide-y divide-slate-100">
                        <?php $__currentLoopData = $faq; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$q, $a]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <details class="group px-4 py-3">
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-[15px] font-medium"><?php echo e($q); ?><i class="fas fa-chevron-down text-xs text-jm-grey transition group-open:rotate-180"></i></summary>
                                <p class="mt-2 text-sm leading-relaxed text-[#535357]"><?php echo e($a); ?></p>
                            </details>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </section>
            <?php endif; ?>

            
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Visit us</div>
                <div class="grid gap-3 p-4 text-sm sm:grid-cols-2">
                    <div class="space-y-1.5">
                        <?php if($S('address')): ?><div class="flex gap-3"><i class="fas fa-location-dot mt-0.5 w-5 text-jm-orange"></i><span><?php echo e($S('address')); ?></span></div><?php endif; ?>
                        <?php if($S('hours')): ?><div class="flex gap-3"><i class="fas fa-clock mt-0.5 w-5 text-jm-orange"></i><span><?php echo e($S('hours')); ?></span></div><?php endif; ?>
                        <?php $__currentLoopData = $phones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$disp, $digits]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a class="flex gap-3 text-jm-orange hover:underline" href="tel:<?php echo e($digits); ?>"><i class="fas fa-phone mt-0.5 w-5"></i><?php echo e($disp); ?></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php if($S('address')): ?>
                        <a target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?php echo e(urlencode($S('address'))); ?>" class="flex items-center justify-center gap-2 rounded-lg border border-jm-orange px-4 py-3 font-bold uppercase text-jm-orange transition hover:bg-[#fdecd5]/60"><i class="fas fa-map-location-dot"></i> Open in Google Maps</a>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.shop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/site/contact.blade.php ENDPATH**/ ?>