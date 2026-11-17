@extends('layouts.app')
@section('title', 'Customers · Drezwel Go')
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Your buyers" title="Customers" subtitle="Created automatically from orders, or add one yourself. Open a customer to edit, see their history or delete them.">
    <x-slot:chips>
        <span><i class="fas fa-users"></i> {{ $totals['customers'] }} customers</span>
        <span><i class="fas fa-triangle-exclamation"></i> {{ $totals['debtors'] }} owing</span>
    </x-slot:chips>
    <a href="#add-customer" onclick="document.getElementById('addCustomer').open = true" class="primary"><i class="fas fa-user-plus"></i> Add customer</a>
    <a href="{{ route('manager.deliveries.create') }}"><i class="fas fa-plus"></i> New delivery</a>
</x-dashboard-hero>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <x-stat-tile color="blue" icon="fa-users" title="Customers" :value="$totals['customers']" sub="Registered accounts" />
    <x-stat-tile color="green" icon="fa-sack-dollar" title="Lifetime spend" :value="'GH₵ ' . number_format($totals['spend'], 2)" sub="All orders" />
    <x-stat-tile color="red" icon="fa-hand-holding-dollar" title="Owed" :value="'GH₵ ' . number_format($totals['owed'], 2)" sub="Uncollected balances" :href="route('manager.debtors')" />
    <x-stat-tile color="orange" icon="fa-user-clock" title="Active debtors" :value="$totals['debtors']" sub="With unpaid arrears" :href="route('manager.debtors')" />
</div>

@php $inp = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm w-full focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]'; @endphp
<details id="addCustomer" class="mb-4 rounded-xl bg-white" @if($errors->any() && old('_form') === 'add') open @endif>
    <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-slate-800 border-l-4 border-l-[#1c3041] rounded-xl" id="add-customer"><i class="fas fa-user-plus"></i> Add a customer</summary>
    <form method="POST" action="{{ route('manager.customers.store') }}" class="grid gap-2 p-4 pt-1 md:grid-cols-5">@csrf
        <input type="hidden" name="_form" value="add">
        @if($errors->any() && old('_form') === 'add')<div class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 md:col-span-5">{{ $errors->first() }}</div>@endif
        <input name="name" value="{{ old('name') }}" required placeholder="Full name" class="{{ $inp }}">
        <input name="phone" value="{{ old('phone') }}" required inputmode="tel" placeholder="Phone, e.g. 0241234567" class="{{ $inp }}">
        <input name="location" value="{{ old('location') }}" placeholder="Default location" class="{{ $inp }}">
        <input name="notes" value="{{ old('notes') }}" placeholder="Notes" class="{{ $inp }}">
        <button class="rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-4 py-2 text-sm font-semibold transition">Save customer</button>
    </form>
</details>

<form method="GET" class="bg-white rounded-xl p-3 mb-4 text-sm">
    <div class="relative">
        <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input name="q" value="{{ request('q') }}" placeholder="Search name or phone…" class="w-full rounded-lg border border-slate-200 bg-slate-50 pl-9 pr-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]">
    </div>
</form>

<div class="bg-white rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm text-slate-800">All customers</div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Name</th><th>Phone</th><th>Default location</th><th class="text-right">Orders</th><th class="text-right">Lifetime spend</th><th class="text-right">Balance due</th><th class="p-3"></th></tr></thead>
        @forelse($customers as $c)
            @php $bal = max(($c->spend ?? 0) - ($c->paid ?? 0), 0); @endphp
            <tr class="border-t border-slate-100 hover:bg-sky-50/50">
                <td class="p-3"><a class="inline-flex items-center gap-2 font-semibold text-[#1c3041] hover:underline" href="{{ route('manager.customers.show', $c) }}">
                    <span class="grid h-7 w-7 place-items-center rounded-full bg-slate-200 text-xs text-slate-600">{{ mb_strtoupper(mb_substr($c->name, 0, 1)) }}</span>{{ $c->name }}</a></td>
                <td class="text-slate-600">{{ $c->phone }}</td><td class="text-slate-600">{{ $c->location }}</td>
                <td class="text-right">{{ $c->orders }}</td><td class="text-right">{{ number_format($c->spend ?? 0, 2) }}</td>
                <td class="text-right p-3 {{ $bal > 0 ? 'text-red-600 font-medium' : 'text-green-600' }}">{{ number_format($bal, 2) }}</td>
                <td class="p-3 whitespace-nowrap text-right text-xs">
                    <a class="text-[#1c3041] hover:underline" href="{{ route('manager.customers.show', $c) }}">Edit</a>
                    @if($c->phone !== \App\Services\SalesService::WALK_IN_PHONE)
                        · <form method="POST" action="{{ route('manager.customers.destroy', $c) }}" class="inline" onsubmit="return confirm('Delete {{ addslashes($c->name) }}?{{ $c->orders ? ' They have orders on record, so this is refused unless you delete them from their page.' : '' }}')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Delete</button></form>
                    @endif
                </td></tr>
        @empty <tr><td colspan="7" class="p-10 text-center text-slate-400"><i class="fas fa-users text-3xl mb-2 block"></i>No customers yet.</td></tr> @endforelse
    </table></div>
</div>
<div class="mt-4">{{ $customers->links() }}</div>
@endsection
