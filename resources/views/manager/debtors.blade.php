@extends('layouts.app')
@section('title', 'Debtors · Drezwel Go')
@php $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2); @endphp
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Collections" title="Debtors" subtitle="Customers with unpaid balances, oldest debt first. Record a payment right here.">
    <x-slot:chips>
        <span><i class="fas fa-user-clock"></i> {{ $debtors->count() }} owing</span>
        <span><i class="fas fa-coins"></i> {{ $m($total) }}</span>
    </x-slot:chips>
</x-dashboard-hero>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <x-stat-tile color="red" icon="fa-hand-holding-dollar" title="Total outstanding" :value="$m($total)" sub="Arrears balance" />
    <x-stat-tile color="blue" icon="fa-users" title="Customers owing" :value="$debtors->count()" sub="Active debtor accounts" />
    <x-stat-tile color="green" icon="fa-clock" title="1–7 days" :value="$m($aging['recent'])" sub="Recent" />
    <x-stat-tile color="orange" icon="fa-clock-rotate-left" title="8+ days" :value="$m($aging['old'])" sub="Urgent collection" />
</div>

<div class="space-y-4">
    @forelse($debtors as $d)
        <section class="bg-white rounded-xl overflow-hidden border-l-4 {{ $d->age > 7 ? 'border-l-red-500' : 'border-l-[#f5922f]' }}">
            <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 bg-slate-50 border-b border-slate-100">
                <a class="inline-flex items-center gap-2 font-semibold text-[#2c8fc6]" href="{{ route('manager.customers.show', $d->customer) }}">
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-slate-200 text-xs text-slate-600">{{ mb_strtoupper(mb_substr($d->customer->name, 0, 1)) }}</span>
                    {{ $d->customer->name }} <span class="text-slate-400 text-sm font-normal">{{ $d->customer->phone }}</span></a>
                <div class="text-right"><strong class="text-red-600">{{ $m($d->balance) }}</strong> <span class="text-xs rounded-full px-2 py-0.5 {{ $d->age > 7 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800' }}">{{ $d->age }} day(s)</span></div>
            </div>
            <div class="px-4 text-sm divide-y divide-slate-100">
                @foreach($d->deliveries as $x)
                    <div class="py-2.5 flex flex-wrap items-center justify-between gap-2">
                        <a class="text-slate-600 hover:text-slate-900" href="{{ route('manager.deliveries.show', $x) }}">{{ $x->reference }} · {{ $x->package_description }} <x-badge :status="$x->status"/></a>
                        <form method="POST" action="{{ route('manager.deliveries.payment', $x) }}" class="flex gap-1">@csrf
                            <input type="number" step="0.01" name="amount" value="{{ $x->balance }}" max="{{ $x->balance }}" class="w-24 rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5">
                            <select name="method" class="rounded-lg border border-slate-200 bg-slate-50 px-1 py-1.5">@foreach(\App\Models\Payment::METHODS as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select>
                            <button class="rounded-lg bg-[#8cc152] hover:bg-[#74a83b] text-white px-3 py-1.5 font-semibold transition">Pay</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>
    @empty
        <div class="bg-white rounded-xl p-10 text-center text-slate-400"><i class="fas fa-champagne-glasses text-3xl mb-2 block text-[#8cc152]"></i>Nobody owes anything.</div>
    @endforelse
</div>
@endsection
