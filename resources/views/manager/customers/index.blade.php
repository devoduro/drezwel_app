@extends('layouts.app')
@section('title', 'Customers · Drezwel Go')
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Your buyers" title="Customers" subtitle="Customers are created automatically when you add a delivery for a new phone number.">
    <x-slot:chips>
        <span><i class="fas fa-users"></i> {{ $totals['customers'] }} customers</span>
        <span><i class="fas fa-triangle-exclamation"></i> {{ $totals['debtors'] }} owing</span>
    </x-slot:chips>
    <a href="{{ route('manager.deliveries.create') }}" class="primary"><i class="fas fa-plus"></i> New delivery</a>
</x-dashboard-hero>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <x-stat-tile color="blue" icon="fa-users" title="Customers" :value="$totals['customers']" sub="Registered accounts" />
    <x-stat-tile color="green" icon="fa-sack-dollar" title="Lifetime spend" :value="'GH₵ ' . number_format($totals['spend'], 2)" sub="All orders" />
    <x-stat-tile color="red" icon="fa-hand-holding-dollar" title="Owed" :value="'GH₵ ' . number_format($totals['owed'], 2)" sub="Uncollected balances" :href="route('manager.debtors')" />
    <x-stat-tile color="orange" icon="fa-user-clock" title="Active debtors" :value="$totals['debtors']" sub="With unpaid arrears" :href="route('manager.debtors')" />
</div>

<form method="GET" class="bg-white rounded-xl p-3 mb-4 text-sm">
    <div class="relative">
        <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input name="q" value="{{ request('q') }}" placeholder="Search name or phone…" class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#42a5dc]">
    </div>
</form>

<div class="bg-white rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#42a5dc] font-semibold text-sm text-slate-800">All customers</div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Name</th><th>Phone</th><th>Default location</th><th class="text-right">Orders</th><th class="text-right">Lifetime spend</th><th class="text-right p-3">Balance due</th></tr></thead>
        @forelse($customers as $c)
            @php $bal = max(($c->spend ?? 0) - ($c->paid ?? 0), 0); @endphp
            <tr class="border-t border-slate-100 hover:bg-sky-50/50">
                <td class="p-3"><a class="inline-flex items-center gap-2 font-semibold text-[#2c8fc6] hover:underline" href="{{ route('manager.customers.show', $c) }}">
                    <span class="grid h-7 w-7 place-items-center rounded-full bg-slate-200 text-xs text-slate-600">{{ mb_strtoupper(mb_substr($c->name, 0, 1)) }}</span>{{ $c->name }}</a></td>
                <td class="text-slate-600">{{ $c->phone }}</td><td class="text-slate-600">{{ $c->location }}</td>
                <td class="text-right">{{ $c->orders }}</td><td class="text-right">{{ number_format($c->spend ?? 0, 2) }}</td>
                <td class="text-right p-3 {{ $bal > 0 ? 'text-red-600 font-medium' : 'text-green-600' }}">{{ number_format($bal, 2) }}</td></tr>
        @empty <tr><td colspan="6" class="p-10 text-center text-slate-400"><i class="fas fa-users text-3xl mb-2 block"></i>No customers yet.</td></tr> @endforelse
    </table></div>
</div>
<div class="mt-4">{{ $customers->links() }}</div>
@endsection
