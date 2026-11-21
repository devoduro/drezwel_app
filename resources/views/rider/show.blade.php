@extends('layouts.app')
@section('title', $delivery->reference . ' · Drezwel Go')
@php $input = 'w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]'; $mine = $delivery->rider_id === auth()->id(); @endphp
@section('content')
<a href="{{ url()->previous() }}" class="text-sm text-slate-500">← Back</a>
<div class="bg-white rounded-xl p-4 mt-2">
    <div class="flex justify-between items-start"><div><div class="font-bold text-lg">{{ $delivery->customer->name }}</div><div class="text-xs text-slate-500">{{ $delivery->reference }}</div></div><x-badge :status="$delivery->status"/></div>
    <dl class="mt-3 text-sm space-y-1.5">
        <div><dt class="inline text-slate-500">Location: </dt><dd class="inline">{{ $delivery->location }}</dd></div>
        <div><dt class="inline text-slate-500">Package: </dt><dd class="inline">{{ $delivery->package_description }} ({{ $delivery->items_sent }} item{{ $delivery->items_sent > 1 ? 's' : '' }}) · {{ \App\Models\Delivery::TYPES[$delivery->order_type] }}</dd></div>
        <div><dt class="inline text-slate-500">When: </dt><dd class="inline">{{ $delivery->delivery_date->format('D d M') }} {{ $delivery->time_window }}</dd></div>
        <div><dt class="inline text-slate-500">To collect: </dt><dd class="inline font-semibold">{{ $delivery->balance > 0 ? 'GH₵ ' . number_format($delivery->balance, 2) : 'Nothing - fully paid' }}</dd>
            <div class="text-xs text-slate-500 mt-0.5">Goods GH₵ {{ number_format($delivery->total_amount, 2) }} + delivery fee GH₵ {{ number_format($delivery->delivery_fee, 2) }}. Hand it all to the business; your delivery fee is paid back to you.</div></div>
        @if($delivery->notes)<div><dt class="inline text-slate-500">Notes: </dt><dd class="inline">{{ $delivery->notes }}</dd></div>@endif
    </dl>
    @if($mine)
        <a href="tel:{{ $delivery->customer->phone }}" class="mt-4 block text-center rounded-lg border border-brand-accent text-brand-accent py-2.5 font-semibold">📞 Call {{ $delivery->customer->phone }}</a>
    @endif
</div>

@if(! $mine && $delivery->status === 'pending')
    <form method="POST" action="{{ route('rider.claim', $delivery) }}" class="mt-4">@csrf<button class="w-full rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white py-3 font-semibold transition shadow">Pick this delivery</button></form>
@endif

@if($mine && $delivery->status === 'assigned')
    <form method="POST" action="{{ route('rider.start', $delivery) }}" class="mt-4">@csrf
        <button class="w-full rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white py-3 font-semibold transition shadow">Start delivery</button>
        <p class="text-xs text-slate-500 mt-2 text-center">Tap this when you leave with the order.</p>
    </form>
    <form method="POST" action="{{ route('rider.release', $delivery) }}" class="mt-3">@csrf<button class="w-full text-sm text-slate-500 py-2" onclick="return confirm('Hand this delivery back?')">Release this delivery</button></form>
@endif

@if($mine && $delivery->status === 'out_for_delivery')
    <form method="POST" action="{{ route('rider.deliver', $delivery) }}" class="mt-4 bg-white rounded-xl p-4 space-y-3">@csrf
        <h2 class="font-semibold">Delivered?</h2>
        <p class="text-sm text-slate-500">Tap <strong>Delivered</strong> once the customer has received the order. They get a text message to say so.</p>
        @if($delivery->items_sent > 1 || $delivery->order_type !== 'standard')
            <label class="block text-sm font-medium">Items the customer kept (of {{ $delivery->items_sent }})
                <input type="number" name="items_kept" min="0" max="{{ $delivery->items_sent }}" value="{{ $delivery->items_sent }}" class="{{ $input }} mt-1"></label>
        @endif
        @if($delivery->balance > 0)
            <label class="block text-sm font-medium">Cash collected (GH₵) - owed {{ number_format($delivery->balance, 2) }}
                <input type="number" step="0.01" min="0" max="{{ $delivery->balance }}" name="cash" value="{{ $delivery->balance }}" class="{{ $input }} mt-1"></label>
        @endif
        <button class="w-full rounded-lg bg-[#8cc152] hover:bg-[#74a83b] text-white py-3 text-lg font-semibold transition shadow" onclick="return confirm('Has the customer received the order? They will be sent a text message.')"><i class="fas fa-circle-check"></i> Delivered</button>
    </form>
    <form method="POST" action="{{ route('rider.undeliver', $delivery) }}" class="mt-2 bg-white rounded-xl p-4 space-y-2">@csrf
        <h2 class="font-semibold text-sm">Could not deliver?</h2>
        <input name="reason" required placeholder="Reason (e.g. customer unreachable)" class="{{ $input }}">
        <button class="w-full rounded-lg border border-red-300 text-red-700 py-2.5 font-semibold" onclick="return confirm('Mark this delivery as undelivered?')">Mark undelivered</button>
    </form>
@endif

@if($mine && $delivery->status === 'assigned')
    <form method="POST" action="{{ route('rider.undeliver', $delivery) }}" class="mt-2 bg-white rounded-xl p-4 space-y-2">@csrf
        <input name="reason" required placeholder="Reason, if you cannot deliver" class="{{ $input }}">
        <button class="w-full rounded-lg border border-red-300 text-red-700 py-2.5 font-semibold">Mark undelivered</button>
    </form>
@endif
@endsection
