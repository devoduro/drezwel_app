<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($receipt ? 'Receipt' : 'Invoice'); ?> <?php echo e($delivery->reference); ?></title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('images/icon.png')); ?>">
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111; margin: 0; background: #e2e8f0; font-size: 13.5px; }
        .bar { max-width: 760px; margin: 16px auto 0; padding: 0 12px; display: flex; gap: 8px; flex-wrap: wrap; }
        .bar a, .bar button { border: 0; border-radius: 8px; padding: 9px 16px; font-weight: 600; font-size: 14px; cursor: pointer; text-decoration: none; background: #0f172a; color: #fff; }
        .bar a.alt { background: #fff; color: #0f172a; border: 1px solid #cbd5e1; }

        .sheet { max-width: 760px; margin: 14px auto 30px; background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.1); position: relative; border: 1px solid #222; }
        .title { background: #111; color: #fff; text-align: center; font-size: 26px; font-weight: 700; letter-spacing: .06em; padding: 7px 0; text-transform: uppercase; }
        .biz { background: #f1f1f1; text-align: center; font-family: "Times New Roman", serif; font-size: 12px; line-height: 1.5; padding: 7px 10px 8px; border-bottom: 1px solid #222; }
        .biz img { height: 38px; display: block; margin: 0 auto 4px; }

        .info { width: 100%; border-collapse: collapse; margin: 0; }
        .info td { border: 1px dotted #888; padding: 4px 8px; }
        .info td.k { background: #bdbdbd; font-weight: 700; text-transform: uppercase; width: 14%; white-space: nowrap; }
        .info td.v { background: #efefef; }

        table.items { width: 100%; border-collapse: collapse; }
        .items th { background: #444; color: #fff; padding: 6px 8px; font-size: 14px; text-align: center; }
        .items td { border: 1px dotted #999; padding: 5px 8px; height: 25px; }
        .items td.q { text-align: center; width: 9%; } .items td.p, .items td.a { text-align: right; width: 17%; }
        .sub { color: #666; font-size: 11.5px; }

        .sumrow { display: flex; border-top: 1px solid #222; }
        .terms { flex: 1; padding: 8px 10px; font-size: 11.5px; }
        .terms em { font-family: "Times New Roman", serif; font-size: 12.5px; }
        .terms strong { display: block; margin-top: 6px; letter-spacing: .02em; }
        .tot { width: 300px; border-left: 1px solid #222; }
        .tot div { display: flex; justify-content: space-between; padding: 4px 10px; }
        .tot .grand { background: #e6e6e6; font-weight: 800; font-size: 15px; padding: 9px 10px; }
        .tot .grand b { color: #d00; font-size: 18px; }
        .tot .small { font-size: 12px; color: #444; }
        .tot .due { background: #fde8e8; color: #b00; font-weight: 700; }
        .tot .ok { background: #e4f6e8; color: #157a35; font-weight: 700; }

        .sign { display: flex; justify-content: space-between; gap: 40px; padding: 38px 22px 10px; }
        .sign div { flex: 1; border-top: 1px solid #222; text-align: center; padding-top: 3px; font-size: 13px; }

        .stamp { position: absolute; right: 36px; top: 150px; border: 4px solid; border-radius: 10px; padding: 3px 16px; font-size: 26px; font-weight: 800; letter-spacing: .12em; transform: rotate(-12deg); opacity: .2; text-transform: uppercase; pointer-events: none; }
        .paid { color: #15803d; } .unpaid { color: #b91c1c; }
        .pay { padding: 6px 10px 0; font-size: 12px; color: #444; }

        /* Thermal receipt (80mm) */
        .receipt .sheet { max-width: 302px; border: 0; box-shadow: none; font-size: 12px; }
        .receipt .title { font-size: 16px; padding: 4px 0; }
        .receipt .biz { font-size: 10.5px; }
        .receipt .info td { padding: 2px 4px; font-size: 11.5px; } .receipt .info td.k { width: auto; }
        .receipt .info tr.opt { display: none; }
        .receipt .items th { font-size: 11px; padding: 3px; } .receipt .items td { padding: 3px; height: auto; }
        .receipt .items tr.blank { display: none; }
        .receipt .sumrow { display: block; } .receipt .tot { width: 100%; border-left: 0; }
        .receipt .terms { padding: 4px 6px; } .receipt .sign, .receipt .stamp { display: none; }

        @media print {
            body { background: #fff; } .bar { display: none; } .sheet { box-shadow: none; margin: 0; max-width: none; }
            .receipt .sheet { max-width: 80mm; }
            @page { margin: <?php echo e($receipt ? '3mm' : '10mm'); ?>; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<?php
    $m = fn ($v) => number_format((float) $v, 2);
    $biz = \App\Models\Setting::get('business_name');
    $paid = $delivery->balance <= 0 && $delivery->status !== 'cancelled';
    $walkIn = $delivery->customer->phone === \App\Services\SalesService::WALK_IN_PHONE;
    $rows = $delivery->items->count() ?: 1;
    $blank = max(0, 10 - $rows);
    $contact = collect([
        \App\Models\Setting::get('phone') ? 'Tel. ' . \App\Models\Setting::get('phone') : null,
        trim(collect([\App\Models\Setting::get('email') ? 'Email: ' . \App\Models\Setting::get('email') : null, \App\Models\Setting::get('website') ? 'Website: ' . preg_replace('#^https?://#', '', \App\Models\Setting::get('website')) : null])->filter()->implode(', ')),
    ])->filter();
?>
<body class="<?php echo e($receipt ? 'receipt' : ''); ?>">
    <div class="bar">
        <button onclick="window.print()">🖨 Print <?php echo e($receipt ? 'receipt' : 'invoice'); ?></button>
        <a class="alt" href="<?php echo e(request()->fullUrlWithQuery(['format' => $receipt ? null : 'receipt'])); ?>"><?php echo e($receipt ? 'Show full invoice' : 'Receipt (small printer)'); ?></a>
        <?php if(auth()->user()?->isManager()): ?><a class="alt" href="<?php echo e(route('manager.deliveries.show', $delivery)); ?>">← Back to order</a><?php endif; ?>
    </div>

    <div class="sheet">
        <?php if($delivery->status === 'cancelled'): ?><div class="stamp unpaid">Cancelled</div>
        <?php else: ?><div class="stamp <?php echo e($paid ? 'paid' : 'unpaid'); ?>"><?php echo e($paid ? 'Paid' : ($delivery->amount_paid > 0 ? 'Part paid' : 'Unpaid')); ?></div><?php endif; ?>

        <div class="title"><?php echo e($biz); ?> - <?php echo e($receipt ? 'Receipt' : 'Invoice'); ?></div>
        <div class="biz">
            <img src="<?php echo e(asset('images/logo.png')); ?>" alt="<?php echo e($biz); ?>">
            <?php if(\App\Models\Setting::get('tagline')): ?>[<?php echo e(\App\Models\Setting::get('tagline')); ?>]<br><?php endif; ?>
            <?php if(\App\Models\Setting::get('address')): ?><?php echo e(\App\Models\Setting::get('address')); ?><br><?php endif; ?>
            <?php $__currentLoopData = $contact; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo e($line); ?><?php if(! $loop->last): ?><br><?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <table class="info">
            <tr><td class="k">To:</td><td class="v"><?php echo e($walkIn ? 'Walk-in customer' : $delivery->customer->name); ?><?php if(! $walkIn && $delivery->customer->phone): ?> <span class="sub">· <?php echo e($delivery->customer->phone); ?></span><?php endif; ?></td>
                <td class="k">Ord. ID</td><td class="v"><?php echo e($delivery->reference); ?></td></tr>
            <tr><td class="k">Address:</td><td class="v"><?php echo e($delivery->source === 'pos' ? 'Collected in store' : $delivery->location); ?></td>
                <td class="k">Ord. Date:</td><td class="v"><?php echo e($delivery->created_at->format('d-M-Y')); ?></td></tr>
            <tr><td class="k">Del. Mode:</td><td class="v"><?php echo e(\App\Models\Delivery::MODES[$delivery->delivery_mode] ?? '—'); ?><?php if($delivery->rider): ?> <span class="sub">· <?php echo e($delivery->rider->name); ?><?php echo e($delivery->rider->phone ? ' ' . $delivery->rider->phone : ''); ?></span><?php endif; ?></td>
                <td class="k">Del. Date:</td><td class="v"><?php echo e(($delivery->delivered_at ?? $delivery->delivery_date)->format('d-M-Y')); ?><?php echo e($delivery->time_window ? ' · ' . $delivery->time_window : ''); ?></td></tr>
        </table>

        <table class="items">
            <thead><tr><th>Qty</th><th>Description</th><th>Unit Price</th><th>Amount</th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $delivery->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr><td class="q"><?php echo e($i->qty); ?></td>
                        <td><?php if($i->qty < 0): ?>Returned: <?php endif; ?><?php echo e($i->product->name); ?><?php if($i->variant && ! $i->variant->is_plain): ?> <span class="sub">(<?php echo e($i->variant->label); ?>)</span><?php endif; ?></td>
                        <td class="p"><?php echo e($m($i->unit_price)); ?></td><td class="a"><?php echo e($m($i->line_total)); ?></td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td class="q"><?php echo e($delivery->items_sent); ?></td><td><?php echo e($delivery->package_description); ?></td><td class="p">—</td><td class="a"><?php echo e($m($delivery->total_amount)); ?></td></tr>
                <?php endif; ?>
                <?php for($b = 0; $b < $blank; $b++): ?><tr class="blank"><td class="q"></td><td></td><td class="p"></td><td class="a"></td></tr><?php endfor; ?>
            </tbody>
        </table>

        <div class="sumrow">
            <div class="terms">
                <em><?php echo e(\App\Models\Setting::get('invoice_terms')); ?></em>
                <strong><?php echo e(mb_strtoupper(\App\Models\Setting::get('invoice_note'))); ?></strong>
                <?php if($delivery->balance > 0 && $delivery->status !== 'cancelled' && $delivery->source !== 'pos'): ?>
                    <div class="pay" style="padding-left:0">Payment is made on delivery in cash or MoMo. Please have GH₵ <?php echo e($m($delivery->balance)); ?> ready.</div>
                <?php endif; ?>
            </div>
            <div class="tot">
                <?php if($delivery->delivery_fee > 0): ?>
                    <div class="small"><span>Goods</span><span><?php echo e($m($delivery->total_amount)); ?></span></div>
                    <div class="small"><span>Delivery fee</span><span><?php echo e($m($delivery->delivery_fee)); ?></span></div>
                <?php endif; ?>
                <div class="grand"><span>TOTAL GHS</span><b><?php echo e($m($delivery->amount_due)); ?></b></div>
                <?php $__currentLoopData = $delivery->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="small"><span>Paid <?php echo e($p->paid_at->format('d M')); ?> (<?php echo e(\App\Models\Payment::METHODS[$p->method] ?? $p->method); ?>)</span><span>- <?php echo e($m($p->amount)); ?></span></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($delivery->status !== 'cancelled'): ?><div class="<?php echo e($paid ? 'ok' : 'due'); ?>"><span><?php echo e($paid ? 'Fully paid' : 'Balance due'); ?></span><span><?php echo e($m($delivery->balance)); ?></span></div><?php endif; ?>
            </div>
        </div>

        <div class="sign"><div>Customer's Signature</div><div>Manager's Signature</div></div>
    </div>

    <?php if($autoprint): ?><script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script><?php endif; ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\drezwel_app\resources\views/invoice.blade.php ENDPATH**/ ?>