
<?php $__env->startSection('title', 'Checkout · ' . \App\Models\Setting::get('business_name')); ?>
<?php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $field = 'mt-1 h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-[15px] focus:border-jm-primary focus:outline-none focus:ring-1 focus:ring-jm-primary';
    $open = \App\Models\Setting::get('store_open') === '1';
    $tel = \App\Models\Setting::phoneLink();
    $count = $lines->sum('qty');
    $speso = \App\Services\SpesoClient::configured();
    $step = 'grid h-6 w-6 shrink-0 place-items-center rounded-full bg-jm-primary text-[13px] font-bold text-white';
?>
<?php $__env->startSection('content'); ?>
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 py-3 space-y-3">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">Checkout</h1>
        <a href="<?php echo e(route('shop.cart')); ?>" class="text-sm font-bold uppercase text-jm-primary hover:underline"><i class="fas fa-chevron-left text-[10px]"></i> Back to cart</a>
    </div>
    <?php if(! $open): ?><div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">Online ordering is paused. Please call <?php echo e(\App\Models\Setting::get('phone') ?: 'us'); ?> to place your order.</div><?php endif; ?>

    <form method="POST" action="<?php echo e(route('shop.checkout.place')); ?>" class="grid items-start gap-3 lg:grid-cols-[1fr_320px]"><?php echo csrf_field(); ?>
        <div class="space-y-3">
            
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3"><span class="<?php echo e($step); ?>">1</span><h2 class="text-[15px] font-bold uppercase tracking-wide">Customer address</h2></div>
                <div class="grid gap-4 p-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <div class="mb-2 text-sm font-medium">How would you like to receive your order?</div>
                        <div class="grid gap-2 sm:grid-cols-3">
                            <?php $__currentLoopData = [['door', 'fa-truck-fast', 'Regular delivery', '48 to 72 hours from the time of order placement'], ['express', 'fa-bolt', 'Express delivery', 'Same day in Accra, 24 hours outside Accra'], ['pickup', 'fa-store', 'Pick up', 'Collect from our shop']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$k, $ico, $ttl, $desc]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="svc-card flex cursor-pointer items-start gap-3 rounded-lg border-2 border-slate-200 p-3">
                                    <input type="radio" name="service" value="<?php echo e($k); ?>" <?php if(old('service', 'door') === $k): echo 'checked'; endif; ?> class="mt-1 accent-[#1c3041]">
                                    <div class="text-sm"><div class="font-bold"><i class="fas <?php echo e($ico); ?> text-jm-primary"></i> <?php echo e($ttl); ?></div><div class="text-jm-grey"><?php echo e($desc); ?></div></div>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <label class="block text-sm font-medium">Full name<input name="name" value="<?php echo e(old('name')); ?>" required autocomplete="name" class="<?php echo e($field); ?>"></label>
                    <label class="block text-sm font-medium">Phone number<input name="phone" value="<?php echo e(old('phone')); ?>" required inputmode="tel" autocomplete="tel" placeholder="0241234567" class="<?php echo e($field); ?>"></label>
                    <div id="addressFields" class="grid gap-4 sm:col-span-2 sm:grid-cols-2">
                    <label class="block text-sm font-medium">Region
                        <select name="region_id" id="regionSel" required class="<?php echo e($field); ?>"><option value="">Choose your region…</option>
                            <?php $__currentLoopData = $regions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($r['id']); ?>" <?php if(old('region_id') == $r['id']): echo 'selected'; endif; ?>><?php echo e($r['name']); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                    <label class="block text-sm font-medium">City / town
                        <select name="zone_id" id="zoneSel" disabled class="<?php echo e($field); ?>"><option value="">Choose a region first</option></select></label>
                    <?php $__errorArgs = ['zone_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-red-600 sm:col-span-2"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <label class="block text-sm font-medium sm:col-span-2">Street address / landmark<input name="address" value="<?php echo e(old('address')); ?>" required autocomplete="street-address" placeholder="Area, street, landmark, house number" class="<?php echo e($field); ?>"></label>
                    <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-red-600 sm:col-span-2"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <label class="block text-sm font-medium sm:col-span-2">Additional information (optional)<textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-[15px] focus:border-jm-primary focus:outline-none focus:ring-1 focus:ring-jm-primary" placeholder="Anything the rider should know?"><?php echo e(old('notes')); ?></textarea></label>
                    <p class="text-xs text-jm-grey sm:col-span-2"><i class="fas fa-circle-info"></i> We text your order updates to this number, and the rider will call it.</p>
                </div>
                <div style="position:absolute;left:-9999px" aria-hidden="true"><label>Company website<input name="company_website" tabindex="-1" autocomplete="off"></label></div>
            </section>

            
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3"><span class="<?php echo e($step); ?>">2</span><h2 class="text-[15px] font-bold uppercase tracking-wide">Delivery details</h2></div>
                <div class="p-4">
                    <div class="flex items-start gap-3 rounded-lg border-2 border-jm-primary bg-[#f3f6f9] p-3">
                        <i id="svcIcon" class="fas fa-truck-fast mt-0.5 text-xl text-jm-primary"></i>
                        <div class="text-sm"><div class="font-bold" id="svcTitle">Regular delivery</div>
                            <div class="text-[#535357]" id="svcLine1"></div>
                            <div class="text-jm-grey" id="svcLine2"></div></div>
                    </div>
                    <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900"><i class="fas fa-circle-info"></i> Payment on delivery unavailable for express delivery, pick up and orders outside Accra.</p>

                    <div class="mt-4 text-[13px] font-bold uppercase tracking-wide text-jm-grey">Shipment (<?php echo e($count); ?> <?php echo e($count === 1 ? 'item' : 'items'); ?>)</div>
                    <div class="mt-2 divide-y divide-slate-100 rounded-lg border border-slate-200">
                        <?php $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $p = $l->variant->product; ?>
                            <div class="flex items-center gap-3 p-3">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                    <?php if($p->primary_image_url): ?><img src="<?php echo e($p->primary_image_url); ?>" alt="" class="h-full w-full object-cover"><?php else: ?><span class="grid h-full place-items-center text-xl text-slate-300"><i class="fas fa-shirt"></i></span><?php endif; ?>
                                </div>
                                <div class="min-w-0 flex-1 text-sm"><div class="line-clamp-2 leading-snug"><?php echo e($p->name); ?></div><?php if (! ($l->variant->is_plain)): ?><div class="text-jm-grey"><?php echo e($l->variant->label); ?></div><?php endif; ?><div class="text-jm-grey">Qty: <?php echo e($l->qty); ?></div></div>
                                <div class="shrink-0 text-right text-sm font-medium"><?php echo e($m($l->qty * $l->price)); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <a href="<?php echo e(route('shop.cart')); ?>" class="mt-3 inline-block text-sm font-bold uppercase text-jm-primary hover:underline">Modify cart</a>
                </div>
            </section>

            
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3"><span class="<?php echo e($step); ?>">3</span><h2 class="text-[15px] font-bold uppercase tracking-wide">Payment method</h2></div>
                <?php $payNow = $speso && old('pay_method') === 'speso'; ?>
                <div class="space-y-3 p-4">
                    <?php $__errorArgs = ['pay_method'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <?php if($speso): ?>
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border-2 p-3 <?php echo e($payNow ? 'border-jm-primary bg-[#f3f6f9]' : 'border-slate-200'); ?>" data-pay-card>
                            <input type="radio" name="pay_method" value="speso" <?php if($payNow): echo 'checked'; endif; ?> class="mt-1 accent-[#1c3041]">
                            <div class="text-sm"><div class="font-bold">Pay now with Mobile Money <span class="ml-1 rounded bg-green-100 px-1.5 py-0.5 text-[11px] font-bold text-green-700">Secure</span></div>
                                <div class="text-[#535357]">We send an approval prompt to your phone. Approve it with your MoMo PIN and your order is paid.</div></div>
                        </label>
                        <div id="momo-fields" class="<?php echo e($payNow ? '' : 'hidden'); ?> grid gap-3 rounded-lg bg-jm-bg p-3 sm:grid-cols-2">
                            <label class="block text-sm font-medium">Mobile money number<input name="momo_number" value="<?php echo e(old('momo_number')); ?>" inputmode="tel" placeholder="Same as above if blank" class="<?php echo e($field); ?>"></label>
                            <label class="block text-sm font-medium">Network
                                <select name="network" class="<?php echo e($field); ?>"><?php $__currentLoopData = \App\Support\MomoApproval::NETWORKS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(old('network') === $k): echo 'selected'; endif; ?>><?php echo e($l); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                            <?php $__errorArgs = ['momo_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-red-600 sm:col-span-2"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    <?php endif; ?>
                    <div id="noPayWarn" class="hidden rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700"><i class="fas fa-triangle-exclamation"></i> This option has to be paid now with Mobile Money, and online payment is not available at the moment. Please call us to place this order.</div>
                    <div id="mustPayNote" class="hidden rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900"><i class="fas fa-circle-info"></i> <span id="mustPayText"></span></div>
                    <label id="codCard" class="flex cursor-pointer items-start gap-3 rounded-lg border-2 p-3 <?php echo e($payNow ? 'border-slate-200' : 'border-jm-primary bg-[#f3f6f9]'); ?>" data-pay-card>
                        <input type="radio" name="pay_method" value="cod" <?php if(! $payNow): echo 'checked'; endif; ?> class="mt-1 accent-[#1c3041]">
                        <div class="text-sm"><div class="font-bold">Pay on delivery <span class="ml-1 rounded bg-green-100 px-1.5 py-0.5 text-[11px] font-bold text-green-700">Available</span></div>
                            <div class="text-[#535357]">Pay the rider in cash or MoMo when your order arrives. Check your items first.</div></div>
                    </label>
                    <div class="rounded-lg bg-jm-bg p-3 text-xs text-[#535357]"><i class="fas fa-truck-fast text-jm-primary"></i> When your order reaches you, the rider marks it delivered and we text you to confirm. Not received? Call us straight away.</div>
                </div>
                <script>
                    (function () {
                        var regions = <?php echo json_encode($regions, 15, 512) ?>, sub = <?php echo e((float) $subtotal); ?>, stationFee = <?php echo e((float) $expressStation); ?>;
                        var speso = <?php echo json_encode($speso, 15, 512) ?>, storeOpen = <?php echo json_encode($open, 15, 512) ?>, shopAddress = <?php echo json_encode((string) \App\Models\Setting::get('address'), 15, 512) ?>;
                        var $ = function (id) { return document.getElementById(id); };
                        var rs = $('regionSel'), zs = $('zoneSel'), addr = document.querySelector('[name=address]');
                        var fmt = function (n) { return 'GH₵ ' + Number(n).toFixed(2); };
                        window.__fmt = fmt; window.__total = sub;

                        function service() { var c = document.querySelector('input[name=service]:checked'); return c ? c.value : 'door'; }
                        function region() { return regions.find(function (x) { return String(x.id) === rs.value; }); }
                        function zone() { var r = region(); return r ? r.zones.find(function (x) { return String(x.id) === zs.value; }) : null; }
                        function mustPayNow() { var r = region(); return service() !== 'door' || (!!r && !r.accra); }

                        function renderTowns(keep) {
                            var r = region();
                            zs.innerHTML = '';
                            if (!r) { zs.innerHTML = '<option value="">Choose a region first</option>'; return; }
                            zs.innerHTML = '<option value="">' + (r.zones.length ? 'Choose your city / town…' : 'No towns listed') + '</option>' +
                                r.zones.map(function (z) { return '<option value="' + z.id + '"' + (String(z.id) === String(keep) ? ' selected' : '') + '>' + z.name + '</option>'; }).join('') +
                                '<option value="other"' + (keep === 'other' ? ' selected' : '') + '>Another town in ' + r.name + '</option>';
                        }

                        // what the delivery costs, or null until a town is chosen
                        function quote() {
                            var s = service(), r = region();
                            if (s === 'pickup') return {amount: 0, text: 'Free (pick up)', extra: ''};
                            var z = zone(), base = z ? z.fee : (zs.value === 'other' && r ? r.fee : null);
                            if (base === null) return null;
                            var extra = (s === 'express' && r && !r.accra) ? stationFee : 0;
                            return {amount: base + extra, text: fmt(base + extra), extra: extra ? 'Includes ' + fmt(extra) + ' to transport your goods to the station' : ''};
                        }

                        function describe() {
                            var s = service(), r = region(), out = {door: {icon: 'fa-truck-fast', title: 'Regular delivery'}, express: {icon: 'fa-bolt', title: 'Express delivery'}, pickup: {icon: 'fa-store', title: 'Pick up'}}[s];
                            $('svcIcon').className = 'fas ' + out.icon + ' mt-0.5 text-xl text-jm-primary'; $('svcTitle').textContent = out.title;
                            var l1 = '', l2 = '';
                            if (s === 'pickup') { l1 = 'Collect your order from our shop' + (shopAddress ? ': ' + shopAddress : '') + '.'; l2 = 'There is no delivery fee. We call you when it is ready.'; }
                            else if (s === 'express') {
                                if (!r) { l1 = 'Delivered the same day in Accra and within 24 hours outside Accra.'; }
                                else if (r.accra) { l1 = 'Delivered the same day.'; }
                                else { l1 = 'Delivered within 24 hours.'; }
                                l1 += ' The delivery fee for your location is shown in your order summary.';
                                l2 = (r && !r.accra) ? 'Outside Accra, express orders go to the station, so a transport charge of ' + fmt(stationFee) + ' is added.' : 'Accra by rider. We call to confirm before dispatch.';
                            }
                            else { l1 = 'Delivered 48 to 72 hours from the time you place your order. The delivery fee for your location is shown in your order summary.'; l2 = 'Accra by rider. For other regions we send by station bus or parcel office. We call to confirm before dispatch.'; }
                            $('svcLine1').textContent = l1; $('svcLine2').textContent = l2;
                        }

                        function refresh() {
                            var s = service(), pick = s === 'pickup', r = region(), q = quote();

                            // pick up needs no address
                            $('addressFields').classList.toggle('hidden', pick);
                            rs.disabled = pick; rs.required = !pick;
                            addr.disabled = pick; addr.required = !pick;
                            zs.disabled = pick || !r; zs.required = !pick && !!r;
                            document.querySelectorAll('.svc-card').forEach(function (c) {
                                var on = c.querySelector('input').checked;
                                c.classList.toggle('border-jm-primary', on); c.classList.toggle('bg-[#f3f6f9]', on); c.classList.toggle('border-slate-200', !on);
                            });
                            describe();

                            // the fee, in the order summary only
                            $('feeSum').textContent = q ? q.text : 'Choose your town'; $('feeSum').classList.toggle('text-jm-grey', !q);
                            $('feeExtra').textContent = q ? q.extra : ''; $('feeExtra').classList.toggle('hidden', !q || !q.extra);
                            window.__total = sub + (q ? q.amount : 0);
                            $('totalSum').textContent = fmt(window.__total);

                            // who may pay on delivery: door delivery within Accra only; everything else is paid now with Mobile Money
                            var must = mustPayNow(), cod = document.querySelector('input[name=pay_method][value=cod]'), sp = document.querySelector('input[name=pay_method][value=speso]');
                            cod.disabled = must; $('codCard').classList.toggle('opacity-50', must); $('codCard').classList.toggle('cursor-not-allowed', must);
                            $('mustPayNote').classList.toggle('hidden', !must);
                            $('mustPayText').textContent = s === 'express' ? 'Express delivery is paid now with Mobile Money.' : (s === 'pickup' ? 'Pick up orders are paid now with Mobile Money.' : 'Orders outside Accra are paid now with Mobile Money.');
                            if (must) { cod.checked = false; if (sp) sp.checked = true; }
                            else if (!document.querySelector('input[name=pay_method]:checked')) { cod.checked = true; }
                            var blocked = must && !speso;
                            $('noPayWarn').classList.toggle('hidden', !blocked);

                            var now = (document.querySelector('input[name=pay_method]:checked') || {}).value === 'speso';
                            if ($('momo-fields')) $('momo-fields').classList.toggle('hidden', !now);
                            document.querySelectorAll('[data-pay-card]').forEach(function (c) {
                                var on = c.querySelector('input').checked;
                                c.classList.toggle('border-jm-primary', on); c.classList.toggle('bg-[#f3f6f9]', on); c.classList.toggle('border-slate-200', !on);
                            });
                            var btn = $('confirm-btn');   // further down the page than this script, so look it up each time
                            if (btn) { btn.disabled = !storeOpen || blocked; btn.textContent = now ? 'Pay ' + fmt(window.__total) + ' now' : 'Confirm order'; }
                        }

                        document.querySelectorAll('input[name=service]').forEach(function (r) { r.addEventListener('change', refresh); });
                        document.querySelectorAll('input[name=pay_method]').forEach(function (r) { r.addEventListener('change', refresh); });
                        rs.addEventListener('change', function () { renderTowns(null); refresh(); });
                        zs.addEventListener('change', refresh);
                        if (rs.value) renderTowns('<?php echo e(old('zone_id')); ?>');
                        // the order summary and the button come later in the page, so the first run waits for the page to finish loading
                        document.addEventListener('DOMContentLoaded', refresh);
                    })();
                </script>
            </section>
        </div>

        
        <aside class="space-y-3 lg:sticky lg:top-36">
            <div class="overflow-hidden rounded-xl bg-white">
                <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Order summary</div>
                <div class="space-y-2 p-4 text-[15px]">
                    <div class="flex justify-between"><span>Item's total (<?php echo e($count); ?>)</span><span><?php echo e($m($subtotal)); ?></span></div>
                    <div class="flex justify-between"><span>Delivery fees</span><span id="feeSum" class="text-jm-grey">Choose your town</span></div>
                    <div id="feeExtra" class="hidden text-right text-xs text-jm-grey"></div>
                    <div class="flex items-center justify-between border-t pt-3"><span class="font-bold">Total</span><span class="text-[20px] font-bold" id="totalSum"><?php echo e($m($subtotal)); ?></span></div>
                </div>
                <div class="border-t border-slate-200 p-3">
                    <button id="confirm-btn" <?php if(! $open): echo 'disabled'; endif; ?> class="h-12 w-full rounded-lg bg-jm-primary text-[15px] font-bold uppercase tracking-wide text-white shadow transition hover:bg-[#14212d] disabled:cursor-not-allowed disabled:opacity-40">Confirm order</button>
                    <a href="<?php echo e(route('shop.cart')); ?>" class="mt-2 block text-center text-sm font-bold uppercase text-jm-primary hover:underline">Modify cart</a>
                    <p class="mt-3 text-center text-[11px] leading-snug text-jm-grey">By placing your order you accept our <a class="text-jm-primary hover:underline" href="<?php echo e(route('site.page', 'terms')); ?>" target="_blank">Terms &amp; conditions</a> and <a class="text-jm-primary hover:underline" href="<?php echo e(route('site.page', 'return-policy')); ?>" target="_blank">Return policy</a>.</p>
                </div>
            </div>
            <?php if($tel): ?><div class="rounded-xl bg-white p-4 text-sm"><div class="font-bold"><i class="fas fa-headset text-jm-primary"></i> Need help?</div><div class="text-jm-grey">Call <a class="font-bold text-jm-primary" href="tel:<?php echo e($tel); ?>"><?php echo e(\App\Models\Setting::get('phone')); ?></a></div></div><?php endif; ?>
        </aside>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.shop', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/shop/checkout.blade.php ENDPATH**/ ?>