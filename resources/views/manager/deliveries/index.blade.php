@extends('layouts.app')
@section('title', 'Deliveries · Drezwel Go')
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Manage orders" title="Deliveries" subtitle="Create orders, assign riders and follow every delivery from pending to delivered.">
    <x-slot:chips>
        <span><i class="fas fa-box"></i> {{ $counts->sum() - ($counts['cancelled'] ?? 0) }} orders</span>
        <span><i class="fas fa-filter"></i> {{ $deliveries->total() }} shown</span>
    </x-slot:chips>
    <a href="{{ route('manager.deliveries.create') }}" class="primary"><i class="fas fa-plus"></i> New delivery</a>
</x-dashboard-hero>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <x-stat-tile color="orange" icon="fa-hourglass-half" title="Pending" :value="$counts['pending'] ?? 0" sub="Waiting for a rider" :href="route('manager.deliveries.index', ['status' => 'pending'])" />
    <x-stat-tile color="blue" icon="fa-motorcycle" title="On the road" :value="($counts['assigned'] ?? 0) + ($counts['out_for_delivery'] ?? 0)" :sub="($counts['out_for_delivery'] ?? 0) . ' out · ' . ($counts['assigned'] ?? 0) . ' assigned'" :href="route('manager.deliveries.index', ['status' => 'out_for_delivery'])" />
    <x-stat-tile color="green" icon="fa-circle-check" title="Delivered" :value="$counts['delivered'] ?? 0" sub="Completed drop-offs" :href="route('manager.deliveries.index', ['status' => 'delivered'])" />
    <x-stat-tile color="red" icon="fa-triangle-exclamation" title="Undelivered" :value="$counts['undelivered'] ?? 0" sub="Needs follow-up" :href="route('manager.deliveries.index', ['status' => 'undelivered'])" />
</div>

<form method="GET" class="bg-white rounded-xl p-3 grid grid-cols-2 md:grid-cols-5 gap-2 mb-4 text-sm">
    <div class="relative col-span-2">
        <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input name="q" value="{{ request('q') }}" placeholder="Search name, phone, reference…" class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#42a5dc]">
    </div>
    <select name="status" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><option value="">All statuses</option>
        @foreach(\App\Models\Delivery::STATUSES as $k => $l)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $l }}</option>@endforeach</select>
    <select name="rider" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><option value="">All riders</option>
        @foreach($riders as $r)<option value="{{ $r->id }}" @selected(request('rider') == $r->id)>{{ $r->name }}</option>@endforeach</select>
    <div class="flex gap-2">
        <input type="date" name="date" value="{{ request('date') }}" class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-slate-50 px-2 py-2">
        <button class="rounded-lg bg-[#42a5dc] hover:bg-[#2c8fc6] text-white px-4 font-semibold transition"><i class="fas fa-filter"></i></button>
    </div>
</form>

<div class="bg-white rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#42a5dc] font-semibold text-sm text-slate-800">All deliveries</div>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Reference</th><th>Date</th><th>Customer</th><th>Location</th><th>Rider</th><th class="text-right">Total</th><th class="text-right">Balance</th><th class="p-3">Status</th></tr></thead>
        @forelse($deliveries as $d)
            <tr class="border-t border-slate-100 hover:bg-sky-50/50">
                <td class="p-3"><a class="text-[#2c8fc6] font-semibold hover:underline" href="{{ route('manager.deliveries.show', $d) }}">{{ $d->reference }}</a>
                    @if($d->source === 'online')<span class="ml-1 rounded bg-purple-100 px-1.5 py-0.5 text-[10px] font-semibold text-purple-700">WEB</span>@elseif($d->source === 'pos')<span class="ml-1 rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700">POS</span>@elseif($d->source === 'phone')<span class="ml-1 rounded bg-sky-100 px-1.5 py-0.5 text-[10px] font-semibold text-sky-700">PHONE</span>@endif</td>
                <td class="whitespace-nowrap text-slate-600">{{ $d->delivery_date->format('d M') }}</td>
                <td><div class="font-medium">{{ $d->customer->name }}</div><div class="text-xs text-slate-400">{{ $d->customer->phone }}</div></td>
                <td class="text-slate-600">{{ $d->location }}</td>
                <td>@if($d->rider)<span class="inline-flex items-center gap-1.5"><span class="grid h-6 w-6 place-items-center rounded-full bg-slate-200 text-[11px] font-semibold text-slate-600">{{ mb_strtoupper(mb_substr($d->rider->name, 0, 1)) }}</span>{{ $d->rider->name }}</span>@else<span class="text-slate-400">—</span>@endif</td>
                <td class="text-right">{{ number_format($d->amount_due, 2) }}</td>
                <td class="text-right {{ $d->balance > 0 ? 'text-red-600 font-medium' : 'text-green-600' }}">{{ number_format($d->balance, 2) }}</td>
                <td class="p-3"><x-badge :status="$d->status"/></td>
            </tr>
        @empty <tr><td colspan="8" class="p-10 text-center text-slate-400"><i class="fas fa-box-open text-3xl mb-2 block"></i>No deliveries found.</td></tr> @endforelse
    </table>
    </div>
</div>
<div class="mt-4">{{ $deliveries->links() }}</div>
@endsection
