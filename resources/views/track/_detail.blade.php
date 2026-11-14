{{-- One order's details, Jumia-style: header, progress steps, items, payment and delivery cards, updates. --}}
@php
    $steps = ['pending' => 'Order placed', 'assigned' => 'Rider assigned', 'out_for_delivery' => 'Out for delivery', 'delivered' => 'Delivered'];
    $keys = array_keys($steps);
    $current = array_search($delivery->status, $keys);
    $failed = in_array($delivery->status, ['undelivered', 'cancelled']);
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $invoice = \Illuminate\Support\Facades\URL::temporarySignedRoute('invoice.public', now()->addDays(30), ['reference' => $delivery->reference]);
@endphp
<div class="space-y-3" id="detail">
    <section class="overflow-hidden rounded-xl bg-white">
        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-200 p-4">
            <div>
                <h2 class="text-[17px] font-bold">Order {{ $delivery->reference }}</h2>
                <div class="mt-0.5 text-sm text-jm-grey">Placed on {{ $delivery->created_at->format('d-m-Y') }}@if($delivery->items->count()) · {{ $delivery->items->sum('qty') }} {{ $delivery->items->sum('qty') === 1 ? 'item' : 'items' }}@endif · Total: <strong class="text-jm-dark">{{ $m($delivery->amount_due) }}</strong></div>
            </div>
            <span class="rounded px-2.5 py-1 text-[12px] font-bold uppercase {{ $delivery->status === 'delivered' ? 'bg-green-100 text-green-700' : ($failed ? 'bg-red-100 text-jm-red' : 'bg-[#e4eaf0] text-jm-primary') }}">{{ \App\Models\Delivery::STATUSES[$delivery->status] ?? $delivery->status }}</span>
        </div>

        {{-- Progress --}}
        <div class="p-4 sm:p-6">
            @if($failed)
                <div class="rounded-lg bg-jm-bg px-4 py-3 text-sm">{{ $delivery->status === 'cancelled' ? 'This order was cancelled.' : 'We could not complete this delivery. We will contact you to reschedule.' }}</div>
            @else
                <ol class="grid grid-cols-4 text-center">
                    @foreach($steps as $key => $label)
                        @php $i = array_search($key, $keys); $done = $current !== false && $i <= $current; $now = $current === $i; @endphp
                        <li class="relative">
                            @if(! $loop->first)<span class="absolute right-1/2 top-3 h-0.5 w-full {{ $done ? 'bg-jm-primary' : 'bg-slate-200' }}"></span>@endif
                            <span class="relative z-10 mx-auto grid h-6 w-6 place-items-center rounded-full text-[11px] font-bold {{ $done ? 'bg-jm-primary text-white' : 'bg-slate-200 text-slate-500' }} {{ $now ? 'ring-4 ring-[#e4eaf0]' : '' }}">{!! $done ? '<i class="fas fa-check"></i>' : $i + 1 !!}</span>
                            <div class="mt-2 px-1 text-[12px] leading-tight sm:text-[13px] {{ $done ? 'font-bold text-jm-dark' : 'text-jm-grey' }}">{{ $label }}</div>
                        </li>
                    @endforeach
                </ol>
            @endif

            @if($delivery->status === 'out_for_delivery')
                <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm"><i class="fas fa-shield-halved text-jm-primary"></i> We texted you a <strong>4-digit code</strong>. Give it to the rider <strong>only when you have received your order</strong>.</div>
            @endif
        </div>
    </section>

    {{-- Items --}}
    <section class="overflow-hidden rounded-xl bg-white">
        <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Items in your order</div>
        <div class="divide-y divide-slate-100">
            @forelse($delivery->items as $i)
                <div class="flex items-center gap-3 p-4">
                    <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-slate-100">@if($i->product->primary_image_url)<img src="{{ $i->product->primary_image_url }}" alt="" class="h-full w-full object-cover">@else<span class="grid h-full place-items-center text-xl text-slate-300"><i class="fas fa-shirt"></i></span>@endif</div>
                    <div class="min-w-0 flex-1 text-sm"><div class="line-clamp-2 leading-snug">{{ $i->product->name }}</div>@if($i->variant && ! $i->variant->is_plain)<div class="text-jm-grey">{{ $i->variant->label }}</div>@endif<div class="text-jm-grey">Qty: {{ $i->qty }}</div></div>
                    <div class="shrink-0 text-sm font-medium">{{ $m($i->line_total) }}</div>
                </div>
            @empty
                <div class="p-4 text-sm"><div>{{ $delivery->package_description }}</div><div class="text-jm-grey">Qty: {{ $delivery->items_sent }}</div></div>
            @endforelse
        </div>
    </section>

    <div class="grid gap-3 md:grid-cols-2">
        <section class="overflow-hidden rounded-xl bg-white">
            <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Payment information</div>
            <div class="space-y-1.5 p-4 text-sm">
                <div class="font-medium">Pay on delivery</div>
                <div class="flex justify-between text-jm-grey"><span>Items</span><span>{{ $m($delivery->total_amount) }}</span></div>
                @if($delivery->delivery_fee > 0)<div class="flex justify-between text-jm-grey"><span>Delivery fee</span><span>{{ $m($delivery->delivery_fee) }}</span></div>@endif
                <div class="flex justify-between border-t pt-2 font-bold"><span>Total</span><span>{{ $m($delivery->amount_due) }}</span></div>
                @if($delivery->amount_paid > 0)<div class="flex justify-between text-green-700"><span>Paid</span><span>{{ $m($delivery->amount_paid) }}</span></div>@endif
                @if($delivery->status !== 'cancelled')<div class="flex justify-between font-bold {{ $delivery->balance > 0 ? 'text-jm-red' : 'text-green-700' }}"><span>{{ $delivery->balance > 0 ? 'To pay on delivery' : 'Fully paid' }}</span><span>{{ $m($delivery->balance) }}</span></div>@endif
            </div>
        </section>
        <section class="overflow-hidden rounded-xl bg-white">
            <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Delivery information</div>
            <div class="space-y-1.5 p-4 text-sm">
                <div class="font-medium">{{ \App\Models\Delivery::MODES[$delivery->delivery_mode] ?? 'Door delivery' }}</div>
                <div class="text-jm-grey"><i class="fas fa-location-dot w-5"></i>{{ $delivery->location }}</div>
                @if($delivery->rider && in_array($delivery->status, ['assigned', 'out_for_delivery']))
                    <div class="mt-2 rounded-lg bg-jm-bg p-3"><div class="text-xs font-bold uppercase text-jm-grey">Your rider</div><div class="font-medium">{{ $delivery->rider->name }}</div>
                        @if($delivery->rider->phone)<a class="font-bold text-jm-primary" href="tel:{{ $delivery->rider->phone }}"><i class="fas fa-phone"></i> {{ $delivery->rider->phone }}</a>@endif</div>
                @endif
            </div>
        </section>
    </div>

    {{-- Updates --}}
    <section class="overflow-hidden rounded-xl bg-white">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 px-4 py-3"><span class="text-[15px] font-bold uppercase tracking-wide">Order updates</span>
            <a target="_blank" href="{{ $invoice }}" class="inline-flex items-center gap-2 rounded-lg bg-jm-primary px-4 py-2 text-[13px] font-bold uppercase text-white shadow transition hover:bg-[#14212d]"><i class="fas fa-print"></i> Print invoice</a></div>
        <ul class="divide-y divide-slate-100 text-sm">
            @foreach($delivery->events->whereIn('type', ['created', 'assigned', 'started', 'delivered', 'undelivered', 'reopened', 'cancelled'])->reverse() as $e)
                <li class="flex justify-between gap-3 px-4 py-3">
                    <span>{{ match($e->type) {
                        'created' => 'Order placed', 'assigned' => 'Rider assigned', 'started' => 'Rider is on the way',
                        'delivered' => 'Delivered', 'undelivered' => 'Delivery attempt failed', 'reopened' => 'Rescheduled', 'cancelled' => 'Cancelled', default => $e->type } }}</span>
                    <span class="whitespace-nowrap text-jm-grey">{{ $e->created_at->format('d M, g:ia') }}</span>
                </li>
            @endforeach
        </ul>
    </section>
</div>
