@extends('layouts.app')
@section('title', $delivery->reference . ' · Drezwel Go')
@php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $input = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm';
    $btn = 'rounded-lg px-3 py-2 text-sm font-semibold';
@endphp
@section('content')
<x-dashboard-hero class="mb-6 !p-5" :eyebrow="$delivery->customer->name . ' · ' . $delivery->location" :title="$delivery->reference" :subtitle="$delivery->package_description">
    <x-slot:chips>
        <span><i class="fas fa-flag"></i> {{ $delivery->status_label }}</span>
        <span><i class="fas fa-coins"></i> {{ $m($delivery->amount_due) }}</span>
        <span><i class="fas fa-user"></i> {{ $delivery->rider?->name ?? 'No rider yet' }}</span>
    </x-slot:chips>
    <a href="{{ route('manager.deliveries.index') }}"><i class="fas fa-arrow-left"></i> Deliveries</a>
    @if(! in_array($delivery->status, ['delivered', 'cancelled']))
        <a href="{{ route('manager.deliveries.edit', $delivery) }}" class="primary"><i class="fas fa-pen"></i> Edit</a>
    @endif
</x-dashboard-hero>

<div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">
        <section class="bg-white rounded-xl p-5 grid sm:grid-cols-2 gap-3 text-sm">
            <div><div class="text-xs text-slate-500">Customer</div><a class="font-medium text-brand-accent" href="{{ route('manager.customers.show', $delivery->customer) }}">{{ $delivery->customer->name }}</a> · {{ $delivery->customer->phone }}</div>
            <div><div class="text-xs text-slate-500">Location</div>{{ $delivery->location }}</div>
            <div><div class="text-xs text-slate-500">Package</div>{{ $delivery->package_description }} ({{ $delivery->items_sent }} item{{ $delivery->items_sent > 1 ? 's' : '' }})</div>
            <div><div class="text-xs text-slate-500">Date / window</div>{{ $delivery->delivery_date->format('D d M Y') }} {{ $delivery->time_window }}</div>
            <div><div class="text-xs text-slate-500">Mode · Type</div>{{ \App\Models\Delivery::MODES[$delivery->delivery_mode] }} · {{ \App\Models\Delivery::TYPES[$delivery->order_type] }}</div>
            <div>
                <div class="text-xs text-slate-500">Rider</div>
                @if($delivery->rider)
                    <span class="font-medium">{{ $delivery->rider->name }}</span>
                    <div class="mt-1 space-y-0.5">
                        @if($delivery->rider->phone)
                            <a class="block text-[#2c8fc6] hover:underline" href="tel:{{ $delivery->rider->phone }}"><i class="fas fa-phone w-4"></i> {{ $delivery->rider->phone }}</a>
                        @else
                            <span class="block text-amber-600 text-xs"><i class="fas fa-triangle-exclamation w-4"></i> No phone on file - add one on the Riders page</span>
                        @endif
                        @if($delivery->rider->email)
                            <a class="block text-slate-500 hover:underline" href="mailto:{{ $delivery->rider->email }}"><i class="fas fa-envelope w-4"></i> {{ $delivery->rider->email }}</a>
                        @endif
                    </div>
                @else
                    Not assigned
                @endif
            </div>
            <div class="sm:col-span-2 rounded-lg bg-slate-50 p-3">
                <div class="text-xs text-slate-500 mb-1">Money - the customer pays goods + delivery fee to the rider</div>
                <div class="flex flex-wrap gap-x-6 gap-y-1">
                    <span>Goods {{ $m($delivery->total_amount) }}</span>
                    <span>+ Delivery fee {{ $m($delivery->delivery_fee) }}</span>
                    <span class="font-semibold">= Customer pays {{ $m($delivery->amount_due) }}</span>
                    <span>Paid {{ $m($delivery->amount_paid) }}</span>
                    <span class="{{ $delivery->balance > 0 ? 'text-red-600 font-semibold' : 'text-green-600' }}">Balance {{ $m($delivery->balance) }}</span>
                </div>
                @if($delivery->delivery_fee > 0)
                    <div class="mt-1 text-xs {{ $delivery->rider_fee_status === 'paid' ? 'text-green-700' : 'text-slate-500' }}">
                        <i class="fas fa-motorcycle"></i> Rider's fee {{ $delivery->rider_fee_status === 'paid' ? 'paid back ' . $delivery->rider_fee_paid_at?->format('d M, g:ia') : ($delivery->status === 'delivered' ? 'still to be paid back to the rider' : 'is paid back after delivery') }}
                    </div>
                @endif
            </div>
            @if($delivery->notes)<div class="sm:col-span-2"><div class="text-xs text-slate-500">Notes</div>{{ $delivery->notes }}</div>@endif
            @if($delivery->undelivered_reason)<div class="sm:col-span-2 text-red-700"><div class="text-xs">Undelivered reason</div>{{ $delivery->undelivered_reason }}</div>@endif
            @if($delivery->confirmed_by_manager)<div class="sm:col-span-2 text-amber-700 text-xs">Receipt was confirmed by the manager without the customer's code.</div>@endif
        </section>

        @if($delivery->items->count())
            <section class="bg-white rounded-xl p-5">
                <h2 class="text-slate-800 font-semibold mb-3"><i class="fas fa-shirt text-[#42a5dc]"></i> Products</h2>
                <table class="w-full text-sm">
                    <thead class="text-left text-slate-500 text-xs"><tr><th>Product</th><th class="text-right">Qty</th><th class="text-right">Price</th><th class="text-right">Total</th></tr></thead>
                    @foreach($delivery->items as $i)
                        <tr class="border-t border-slate-100"><td class="py-1.5"><a class="text-[#2c8fc6] hover:underline" href="{{ route('manager.products.show', $i->product) }}">{{ $i->product->name }}</a></td><td class="text-right">{{ $i->qty }}</td><td class="text-right">{{ number_format($i->unit_price, 2) }}</td><td class="text-right font-medium">{{ number_format($i->line_total, 2) }}</td></tr>
                    @endforeach
                </table>
            </section>
        @endif

        <section class="bg-white rounded-xl p-5">
            <h2 class="text-slate-800 font-semibold mb-3">Payments</h2>
            @forelse($delivery->payments as $p)
                <div class="flex justify-between text-sm border-b py-1.5"><span>{{ $p->paid_at->format('d M, g:ia') }} · {{ \App\Models\Payment::METHODS[$p->method] ?? $p->method }}{{ $p->rider ? ' · via ' . $p->rider->name : '' }}</span><strong>{{ $m($p->amount) }}</strong></div>
            @empty <p class="text-sm text-slate-400">No payments yet.</p> @endforelse
            @if($delivery->balance > 0 && $delivery->status !== 'cancelled')
                <form method="POST" action="{{ route('manager.deliveries.payment', $delivery) }}" class="mt-3 flex flex-wrap gap-2">@csrf
                    <input type="number" step="0.01" name="amount" value="{{ $delivery->balance }}" max="{{ $delivery->balance }}" class="{{ $input }} w-28">
                    <select name="method" class="{{ $input }} bg-white">@foreach(\App\Models\Payment::METHODS as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select>
                    <input name="notes" placeholder="Note" class="{{ $input }} flex-1 min-w-24">
                    <button class="{{ $btn }} bg-green-600 text-white">Record payment</button>
                </form>
            @endif
        </section>

        <section class="bg-white rounded-xl p-5">
            <h2 class="text-slate-800 font-semibold mb-3">Timeline</h2>
            <ol class="space-y-2 text-sm">
                @foreach($delivery->events->reverse() as $e)
                    <li class="flex justify-between gap-3 border-b border-slate-100 pb-1.5"><span><span class="font-medium capitalize">{{ str_replace('_', ' ', $e->type) }}</span>@if($e->note) · {{ $e->note }}@endif @if($e->user)<span class="text-slate-400">({{ $e->user->name }})</span>@endif</span><span class="text-slate-400 whitespace-nowrap">{{ $e->created_at->format('d M, g:ia') }}</span></li>
                @endforeach
            </ol>
        </section>
    </div>

    <div class="space-y-5">
        @if(in_array($delivery->status, ['pending', 'assigned']))
            <section class="bg-white rounded-xl p-5">
                <h2 class="text-slate-800 font-semibold mb-2">{{ $delivery->status === 'pending' ? 'Assign a rider' : 'Reassign rider' }}</h2>
                <form method="POST" action="{{ route('manager.deliveries.assign', $delivery) }}" class="flex gap-2">@csrf
                    <select name="rider_id" required class="{{ $input }} bg-white flex-1"><option value="">Choose rider</option>@foreach($riders as $r)<option value="{{ $r->id }}" @selected($delivery->rider_id === $r->id)>{{ $r->name }}</option>@endforeach</select>
                    <button class="{{ $btn }} bg-brand text-white">Assign</button>
                </form>
                @if($delivery->status === 'pending')<p class="text-xs text-slate-500 mt-2">Or leave it: riders can pick it from their "Pick Up" tab.</p>@endif
            </section>
        @endif

        @if($delivery->status === 'out_for_delivery')
            <section class="bg-white rounded-xl p-5 space-y-3">
                <h2 class="text-slate-800 font-semibold">Delivery code</h2>
                <p class="text-sm text-slate-500">Sent {{ $delivery->code_sent_at?->diffForHumans() ?? 'never' }} · wrong attempts: {{ $delivery->code_attempts }}/{{ \App\Models\Delivery::MAX_CODE_ATTEMPTS }}</p>
                <form method="POST" action="{{ route('manager.deliveries.resend', $delivery) }}">@csrf<button class="{{ $btn }} bg-brand text-white w-full">Send a new code to customer</button></form>
            </section>
        @endif

        @if(in_array($delivery->status, ['assigned', 'out_for_delivery', 'undelivered']))
            <section class="bg-white rounded-xl p-5">
                <h2 class="text-slate-800 font-semibold mb-1">Confirm without code</h2>
                <p class="text-xs text-slate-500 mb-2">Only if the customer confirmed another way. This is recorded.</p>
                <form method="POST" action="{{ route('manager.deliveries.confirm', $delivery) }}" class="space-y-2">@csrf
                    <input name="reason" required placeholder="Reason" class="{{ $input }} w-full">
                    <div class="flex gap-2"><input type="number" name="items_kept" min="0" max="{{ $delivery->items_sent }}" placeholder="Items kept" class="{{ $input }} w-1/2"><input type="number" step="0.01" name="cash" placeholder="Cash collected" class="{{ $input }} w-1/2"></div>
                    <button class="{{ $btn }} bg-amber-600 text-white w-full" onclick="return confirm('Confirm this delivery without the customer code?')">Confirm delivery</button>
                </form>
            </section>
        @endif

        <section class="bg-white rounded-xl p-5 space-y-3">
            <h2 class="text-slate-800 font-semibold">Other actions</h2>
            @if(in_array($delivery->status, ['assigned', 'out_for_delivery']))
                <form method="POST" action="{{ route('manager.deliveries.undeliver', $delivery) }}" class="flex gap-2">@csrf
                    <input name="reason" required placeholder="Why undelivered?" class="{{ $input }} flex-1"><button class="{{ $btn }} bg-red-600 text-white">Undelivered</button></form>
            @endif
            @if(in_array($delivery->status, ['undelivered', 'cancelled']))
                <form method="POST" action="{{ route('manager.deliveries.reopen', $delivery) }}" class="flex gap-2">@csrf
                    <input type="date" name="delivery_date" value="{{ today()->toDateString() }}" class="{{ $input }}"><button class="{{ $btn }} bg-brand text-white">Reopen</button></form>
            @endif
            @if(! in_array($delivery->status, ['delivered', 'cancelled']))
                <form method="POST" action="{{ route('manager.deliveries.cancel', $delivery) }}">@csrf<button class="{{ $btn }} border border-red-300 text-red-700 w-full" onclick="return confirm('Cancel this delivery?')">Cancel delivery</button></form>
            @endif
        </section>
    </div>
</div>
@endsection
