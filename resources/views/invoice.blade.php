<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $receipt ? 'Receipt' : 'Invoice' }} {{ $delivery->reference }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
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
            @page { margin: {{ $receipt ? '3mm' : '10mm' }}; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
@php
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
@endphp
<body class="{{ $receipt ? 'receipt' : '' }}">
    <div class="bar">
        <button onclick="window.print()">🖨 Print {{ $receipt ? 'receipt' : 'invoice' }}</button>
        <a class="alt" href="{{ request()->fullUrlWithQuery(['format' => $receipt ? null : 'receipt']) }}">{{ $receipt ? 'Show full invoice' : 'Receipt (small printer)' }}</a>
        @if(auth()->user()?->isManager())<a class="alt" href="{{ route('manager.deliveries.show', $delivery) }}">← Back to order</a>@endif
    </div>

    <div class="sheet">
        @if($delivery->status === 'cancelled')<div class="stamp unpaid">Cancelled</div>
        @else<div class="stamp {{ $paid ? 'paid' : 'unpaid' }}">{{ $paid ? 'Paid' : ($delivery->amount_paid > 0 ? 'Part paid' : 'Unpaid') }}</div>@endif

        <div class="title">{{ $biz }} - {{ $receipt ? 'Receipt' : 'Invoice' }}</div>
        <div class="biz">
            <img src="{{ asset('images/logo.png') }}" alt="{{ $biz }}">
            @if(\App\Models\Setting::get('tagline'))[{{ \App\Models\Setting::get('tagline') }}]<br>@endif
            @if(\App\Models\Setting::get('address')){{ \App\Models\Setting::get('address') }}<br>@endif
            @foreach($contact as $line){{ $line }}@if(! $loop->last)<br>@endif @endforeach
        </div>

        <table class="info">
            <tr><td class="k">To:</td><td class="v">{{ $walkIn ? 'Walk-in customer' : $delivery->customer->name }}@if(! $walkIn && $delivery->customer->phone) <span class="sub">· {{ $delivery->customer->phone }}</span>@endif</td>
                <td class="k">Ord. ID</td><td class="v">{{ $delivery->reference }}</td></tr>
            <tr><td class="k">Address:</td><td class="v">{{ $delivery->source === 'pos' ? 'Collected in store' : $delivery->location }}</td>
                <td class="k">Ord. Date:</td><td class="v">{{ $delivery->created_at->format('d-M-Y') }}</td></tr>
            <tr><td class="k">Del. Mode:</td><td class="v">{{ \App\Models\Delivery::MODES[$delivery->delivery_mode] ?? '—' }}@if($delivery->rider) <span class="sub">· {{ $delivery->rider->name }}{{ $delivery->rider->phone ? ' ' . $delivery->rider->phone : '' }}</span>@endif</td>
                <td class="k">Del. Date:</td><td class="v">{{ ($delivery->delivered_at ?? $delivery->delivery_date)->format('d-M-Y') }}{{ $delivery->time_window ? ' · ' . $delivery->time_window : '' }}</td></tr>
        </table>

        <table class="items">
            <thead><tr><th>Qty</th><th>Description</th><th>Unit Price</th><th>Amount</th></tr></thead>
            <tbody>
                @forelse($delivery->items as $i)
                    <tr><td class="q">{{ $i->qty }}</td>
                        <td>{{ $i->product->name }}@if($i->variant && ! $i->variant->is_plain) <span class="sub">({{ $i->variant->label }})</span>@endif</td>
                        <td class="p">{{ $m($i->unit_price) }}</td><td class="a">{{ $m($i->line_total) }}</td></tr>
                @empty
                    <tr><td class="q">{{ $delivery->items_sent }}</td><td>{{ $delivery->package_description }}</td><td class="p">—</td><td class="a">{{ $m($delivery->total_amount) }}</td></tr>
                @endforelse
                @for($b = 0; $b < $blank; $b++)<tr class="blank"><td class="q"></td><td></td><td class="p"></td><td class="a"></td></tr>@endfor
            </tbody>
        </table>

        <div class="sumrow">
            <div class="terms">
                <em>{{ \App\Models\Setting::get('invoice_terms') }}</em>
                <strong>{{ mb_strtoupper(\App\Models\Setting::get('invoice_note')) }}</strong>
                @if($delivery->balance > 0 && $delivery->status !== 'cancelled' && $delivery->source !== 'pos')
                    <div class="pay" style="padding-left:0">Payment is made on delivery in cash or MoMo. Please have GH₵ {{ $m($delivery->balance) }} ready.</div>
                @endif
            </div>
            <div class="tot">
                @if($delivery->delivery_fee > 0)
                    <div class="small"><span>Goods</span><span>{{ $m($delivery->total_amount) }}</span></div>
                    <div class="small"><span>Delivery fee</span><span>{{ $m($delivery->delivery_fee) }}</span></div>
                @endif
                <div class="grand"><span>TOTAL GHS</span><b>{{ $m($delivery->amount_due) }}</b></div>
                @foreach($delivery->payments as $p)
                    <div class="small"><span>Paid {{ $p->paid_at->format('d M') }} ({{ \App\Models\Payment::METHODS[$p->method] ?? $p->method }})</span><span>- {{ $m($p->amount) }}</span></div>
                @endforeach
                @if($delivery->status !== 'cancelled')<div class="{{ $paid ? 'ok' : 'due' }}"><span>{{ $paid ? 'Fully paid' : 'Balance due' }}</span><span>{{ $m($delivery->balance) }}</span></div>@endif
            </div>
        </div>

        <div class="sign"><div>Customer's Signature</div><div>Manager's Signature</div></div>
    </div>

    @if($autoprint)<script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>@endif
</body>
</html>
