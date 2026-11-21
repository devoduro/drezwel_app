@extends('layouts.app')
@section('title', 'Rider cash & items · Drezwel Go')
@php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $input = 'rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-sm';
    $held = $riders->sum(fn ($r) => max($r->c['cash_held'], 0));
    $items = $riders->sum(fn ($r) => $r->c['items_pending']);
    $outstanding = $riders->where('c.settled', false)->count();
    $feesOwed = $riders->sum(fn ($r) => $r->c['fees_owed']);
@endphp
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Reconciliation" title="Rider cash & items" subtitle="Riders collect goods + delivery fee from the customer and hand it all in. Receive it here, then pay the rider their delivery fees back.">
    <x-slot:chips>
        <span><i class="fas fa-wallet"></i> {{ $m($held) }} held</span>
        <span><i class="fas fa-box-open"></i> {{ $items }} items due back</span>
    </x-slot:chips>
</x-dashboard-hero>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <x-stat-tile :color="$held > 0 ? 'red' : 'green'" icon="fa-wallet" title="Cash with riders" :value="$m($held)" sub="Not yet handed in" />
    <x-stat-tile :color="$items > 0 ? 'orange' : 'green'" icon="fa-box-open" title="Items due back" :value="$items" sub="Pending check-in" />
    <x-stat-tile :color="$feesOwed > 0 ? 'purple' : 'green'" icon="fa-motorcycle" title="Fees owed to riders" :value="$m($feesOwed)" sub="Pay back after they hand in" />
    <x-stat-tile :color="$outstanding ? 'orange' : 'green'" icon="fa-clipboard-check" title="Riders outstanding" :value="$outstanding" :sub="$riders->count() - $outstanding . ' cleared'" />
</div>

<div class="grid lg:grid-cols-2 gap-4">
    @foreach($riders as $r)
        @php $c = $r->c; @endphp
        <section class="bg-white rounded-xl p-4 border-l-4 {{ $c['settled'] ? 'border-l-[#8cc152]' : 'border-l-[#1c3041]' }} {{ $r->rider->is_active ? '' : 'opacity-60' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-[#1c3041] to-[#1c3041] font-bold text-white">{{ mb_strtoupper(mb_substr($r->rider->name, 0, 1)) }}</span>
                    <div class="min-w-0">
                        <div class="font-semibold truncate"><a href="{{ route('manager.riders.show', $r->rider) }}" class="text-[#1c3041] hover:underline" title="See {{ $r->rider->name }}'s orders and deliveries">{{ $r->rider->name }}</a>
                            <span class="ml-1 text-xs rounded-full px-2 py-0.5 {{ $c['settled'] ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">{{ $c['settled'] ? 'Cleared' : ($c['cleared'] ? 'Has open orders' : 'Outstanding') }}</span></div>
                        <div class="text-xs text-slate-500">{{ $c['open_runs'] }} open run(s)</div>
                    </div>
                </div>
                @unless($c['cleared'])
                    <form method="POST" action="{{ route('manager.remittances.clear', $r->rider) }}">@csrf
                        <button class="rounded-lg bg-[#8cc152] hover:bg-[#74a83b] text-white px-3 py-2 text-sm font-semibold transition whitespace-nowrap" onclick="return confirm('Receive {{ $m(max($c['cash_held'],0)) }} and {{ $c['items_pending'] }} item(s) from {{ $r->rider->name }} and clear them?')"><i class="fas fa-check"></i> Clear rider</button>
                    </form>
                @endunless
            </div>
            <div class="mt-3 grid grid-cols-3 gap-2 text-center text-xs">
                <div class="rounded-lg bg-slate-50 py-2"><div class="text-base font-bold">{{ $m($c['cash_collected']) }}</div><div class="uppercase tracking-wide text-slate-500">Collected</div></div>
                <div class="rounded-lg bg-slate-50 py-2"><div class="text-base font-bold text-green-600">{{ $m($c['cash_remitted']) }}</div><div class="uppercase tracking-wide text-slate-500">Remitted</div></div>
                <div class="rounded-lg bg-slate-50 py-2"><div class="text-base font-bold {{ $c['cash_held'] > 0 ? 'text-red-600' : '' }}">{{ $m($c['cash_held']) }}</div><div class="uppercase tracking-wide text-slate-500">Holding</div></div>
            </div>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-lg {{ $c['fees_owed'] > 0 ? 'bg-purple-50 border border-purple-200' : 'bg-slate-50' }} px-3 py-2 text-sm">
                <span><i class="fas fa-motorcycle text-[#7d62c4]"></i> Delivery fees owed to {{ $r->rider->name }}: <strong>{{ $m($c['fees_owed']) }}</strong> <span class="text-xs text-slate-500">(paid so far {{ $m($c['fees_paid']) }})</span></span>
                @if($c['fees_owed'] > 0)
                    <form method="POST" action="{{ route('manager.remittances.pay-fees', $r->rider) }}">@csrf
                        <button class="rounded-lg bg-[#967adc] hover:bg-[#7d62c4] text-white px-3 py-1.5 font-semibold transition" onclick="return confirm('Pay {{ $r->rider->name }} {{ $m($c['fees_owed']) }} in delivery fees?')"><i class="fas fa-hand-holding-dollar"></i> Pay fees back</button>
                    </form>
                @endif
            </div>
            {{-- the deliveries this rider still has: where they are going, and a way to clear each one --}}
            <details class="mt-3 rounded-lg border border-slate-200 group" @if($r->open->count() && request('rider') == $r->rider->id) open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-3 py-2 text-sm font-semibold text-slate-700">
                    <span><i class="fas fa-route text-[#1c3041]"></i> Assigned deliveries <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs {{ $r->open->count() ? 'bg-amber-100 text-amber-800' : 'text-slate-500' }}">{{ $r->open->count() }}</span></span>
                    <i class="fas fa-chevron-down text-xs text-slate-400 transition group-open:rotate-180"></i>
                </summary>
                <div class="divide-y divide-slate-100 border-t border-slate-200">
                    @forelse($r->open as $d)
                        <div class="px-3 py-2.5 text-sm">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <a href="{{ route('manager.deliveries.show', $d) }}" class="font-semibold text-[#1c3041] hover:underline">{{ $d->reference }}</a>
                                    <span class="ml-1 rounded-full px-2 py-0.5 text-[11px] {{ $d->status === 'out_for_delivery' ? 'bg-indigo-100 text-indigo-800' : 'bg-blue-100 text-blue-800' }}">{{ $d->status === 'out_for_delivery' ? 'On the road' : 'Assigned' }}</span>
                                    <div class="text-slate-700"><i class="fas fa-location-dot w-4 text-slate-400"></i> {{ $d->location }}</div>
                                    <div class="text-xs text-slate-500">{{ $d->customer->name }} · {{ $d->customer->phone }} · {{ $d->delivery_date->format('d M') }}</div>
                                </div>
                                <div class="text-right text-xs text-slate-500">To collect<div class="text-sm font-semibold {{ $d->balance > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $m($d->balance) }}</div></div>
                            </div>
                            <form method="POST" action="{{ route('manager.deliveries.confirm', $d) }}" class="mt-2 flex flex-wrap items-center gap-2" onsubmit="return confirm('Mark {{ $d->reference }} as delivered{{ $d->balance > 0 ? ' with the cash collected' : '' }}? The customer will be texted.')">@csrf
                                @if($d->balance > 0)<input type="number" step="0.01" min="0" max="{{ $d->balance }}" name="cash" value="{{ $d->balance }}" class="{{ $input }} w-28" title="Cash the rider collected"><span class="text-xs text-slate-500">cash collected</span>@endif
                                <button class="ml-auto rounded-lg bg-[#8cc152] hover:bg-[#74a83b] text-white px-3 py-1.5 text-sm font-semibold transition"><i class="fas fa-circle-check"></i> Clear as delivered</button>
                            </form>
                        </div>
                    @empty
                        <div class="px-3 py-3 text-sm text-slate-400">No assigned deliveries. Everything is cleared.</div>
                    @endforelse
                </div>
            </details>
            <div class="mt-2 text-xs text-slate-500">Items due back {{ $c['items_due'] }} · checked in {{ $c['items_remitted'] }} · <strong class="text-slate-700">{{ $c['items_pending'] }} pending</strong></div>
            <form method="POST" action="{{ route('manager.remittances.store') }}" class="mt-3 flex flex-wrap gap-2 items-center">@csrf
                <input type="hidden" name="rider_id" value="{{ $r->rider->id }}">
                <input type="number" step="0.01" min="0" name="cash" value="0" class="{{ $input }} w-24" title="Cash received">
                <input type="number" min="0" name="items" value="0" class="{{ $input }} w-16" title="Items checked in">
                <select name="method" class="{{ $input }}">@foreach(\App\Models\Remittance::METHODS as $k => $l)<option value="{{ $k }}">{{ \Illuminate\Support\Str::before($l, ' /') }}</option>@endforeach</select>
                <input name="notes" placeholder="Notes" class="{{ $input }} flex-1 min-w-20">
                <button class="rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-3 py-1.5 text-sm font-semibold transition">Record partial</button>
            </form>
        </section>
    @endforeach
</div>

<div class="bg-white rounded-xl overflow-hidden mt-8">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm">Recent remittances</div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">When</th><th>Rider</th><th class="text-right">Cash</th><th class="text-right">Items</th><th>Method</th><th>Received by</th><th class="p-3">Notes</th></tr></thead>
        @forelse($history as $h)
            <tr class="border-t border-slate-100 hover:bg-sky-50/50"><td class="p-3 whitespace-nowrap">{{ $h->remitted_at->format('d M, g:ia') }}</td><td class="font-medium"><a href="{{ route('manager.riders.show', $h->rider) }}" class="text-[#1c3041] hover:underline">{{ $h->rider->name }}</a></td><td class="text-right">{{ $m($h->cash) }}</td><td class="text-right">{{ $h->items }}</td><td>{{ \App\Models\Remittance::METHODS[$h->method] ?? $h->method }}</td><td>{{ $h->recorder?->name }}</td><td class="p-3 text-slate-500">{{ $h->notes }}</td></tr>
        @empty <tr><td colspan="7" class="p-8 text-center text-slate-400">Nothing recorded yet.</td></tr> @endforelse
    </table></div>
</div>
@endsection
