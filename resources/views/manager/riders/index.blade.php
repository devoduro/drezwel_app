@extends('layouts.app')
@section('title', 'Riders · Drezwel Go')
@php $input = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm w-full focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]'; @endphp
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Fleet" title="Riders & drivers" subtitle="Add riders, edit their details and see who is out on the road.">
    <x-slot:chips>
        <span><i class="fas fa-user-check"></i> {{ $riders->where('is_active', true)->count() }} active</span>
        <span><i class="fas fa-user-slash"></i> {{ $riders->where('is_active', false)->count() }} inactive</span>
    </x-slot:chips>
    <a href="{{ route('manager.remittances') }}"><i class="fas fa-cash-register"></i> Cash &amp; items</a>
</x-dashboard-hero>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <x-stat-tile color="blue" icon="fa-users" title="Riders" :value="$riders->count()" :sub="$riders->where('is_active', true)->count() . ' active'" />
    <x-stat-tile color="purple" icon="fa-road" title="On a run" :value="$riders->sum('open_deliveries')" sub="Assigned or out for delivery" />
    <x-stat-tile color="green" icon="fa-circle-check" title="Delivered" :value="$riders->sum('total_deliveries')" sub="All riders, all time" />
    <x-stat-tile color="orange" icon="fa-wallet" title="Cash with riders" :value="'GH₵ ' . number_format($cashHeld, 2)" sub="Not yet handed in" :href="route('manager.remittances')" />
</div>

<details class="bg-white rounded-xl mb-5 overflow-hidden group" {{ $errors->any() ? 'open' : '' }}>
    <summary class="cursor-pointer px-4 py-3 font-semibold text-sm border-l-4 border-l-[#8cc152] flex items-center gap-2"><i class="fas fa-user-plus text-[#74a83b]"></i> Add a rider</summary>
    <form method="POST" action="{{ route('manager.riders.store') }}" class="grid sm:grid-cols-2 lg:grid-cols-5 gap-2 p-4 border-t border-gray-100">@csrf
        <input name="name" placeholder="Name" required class="{{ $input }}">
        <input name="phone" placeholder="Phone (login)" required class="{{ $input }}">
        <input name="email" type="email" placeholder="Email (optional)" class="{{ $input }}">
        <input name="password" type="text" placeholder="Password (min 6)" required class="{{ $input }}">
        <button class="rounded-lg bg-[#8cc152] hover:bg-[#74a83b] text-white px-4 py-2 text-sm font-semibold transition">Add rider</button>
    </form>
</details>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
    @foreach($riders as $r)
        <div class="min-w-0 bg-white rounded-xl p-4 transition hover:shadow-md {{ $r->is_active ? '' : 'opacity-60' }}">
            <div class="flex items-center gap-3">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-gradient-to-br from-[#1c3041] to-[#1c3041] text-lg font-bold text-white shadow">{{ mb_strtoupper(mb_substr($r->name, 0, 1)) }}</span>
                <div class="min-w-0 flex-1">
                    <div class="font-semibold truncate">{{ $r->name }}</div>
                    <div class="text-xs text-slate-500 truncate"><i class="fas fa-phone"></i> {{ $r->phone ?: 'No phone' }}</div>
                    <div class="text-xs text-slate-400 truncate"><i class="fas fa-envelope"></i> {{ $r->email ?: '—' }}</div>
                </div>
                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $r->is_active ? 'bg-green-100 text-green-800' : 'bg-slate-200 text-slate-600' }}">{{ $r->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            @unless($r->phone)
                <form method="POST" action="{{ route('manager.riders.update', $r) }}" class="mt-3 flex gap-2 rounded-lg bg-amber-50 border border-amber-200 p-2">@csrf @method('PUT')
                    <input type="hidden" name="name" value="{{ $r->name }}">
                    <input type="hidden" name="email" value="{{ $r->email }}">
                    <input name="phone" inputmode="tel" required placeholder="Add phone e.g. 0241234567" class="{{ $input }} min-w-0 flex-1">
                    <button class="rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-3 text-sm font-semibold transition whitespace-nowrap">Save</button>
                </form>
            @endunless
            <div class="mt-4 grid grid-cols-2 gap-2 text-center">
                <div class="rounded-lg bg-slate-50 py-2"><div class="text-lg font-bold text-green-600">{{ $r->total_deliveries }}</div><div class="text-[11px] uppercase tracking-wide text-slate-500">Delivered</div></div>
                <div class="rounded-lg bg-slate-50 py-2"><div class="text-lg font-bold text-[#1c3041]">{{ $r->open_deliveries }}</div><div class="text-[11px] uppercase tracking-wide text-slate-500">Open runs</div></div>
            </div>
            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-sm">
                <details class="relative">
                    <summary class="cursor-pointer font-medium text-[#1c3041]"><i class="fas fa-pen"></i> Edit</summary>
                    <form method="POST" action="{{ route('manager.riders.update', $r) }}" class="absolute left-0 z-10 mt-2 w-60 space-y-2 rounded-lg border bg-white p-3 shadow-lg">@csrf @method('PUT')
                        <input name="name" value="{{ $r->name }}" required class="{{ $input }}">
                        <input name="phone" value="{{ $r->phone }}" placeholder="Phone" required class="{{ $input }}">
                        <input name="email" value="{{ $r->email }}" placeholder="Email" class="{{ $input }}">
                        <input name="password" placeholder="New password (optional)" class="{{ $input }}">
                        <button class="w-full rounded bg-[#1c3041] text-white py-1.5 font-semibold">Save</button>
                    </form>
                </details>
                <a href="{{ route('manager.riders.show', $r) }}" class="font-medium text-slate-600 hover:text-slate-900"><i class="fas fa-clock-rotate-left"></i> History</a>
                <form method="POST" action="{{ route('manager.riders.toggle', $r) }}">@csrf
                    <button class="text-slate-500 hover:text-slate-800"><i class="fas {{ $r->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i> {{ $r->is_active ? 'Deactivate' : 'Activate' }}</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
@endsection
