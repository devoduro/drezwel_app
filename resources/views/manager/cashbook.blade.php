@extends('layouts.app')
@section('title', 'Cash book · Drezwel Go')
@php
    use App\Models\CashbookEntry as CB;
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $field = 'w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]';
    $head = 'px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm text-slate-800';
    $range = ($from || $to) ? ($from?->format('d M Y') ?? '…') . ' – ' . ($to?->format('d M Y') ?? '…') : 'All records';
    $keep = request()->only(['period', 'from', 'to']);
    $type = old('type', $edit?->type ?? 'expense');
    $badge = ['purchase' => 'bg-sky-100 text-sky-700', 'expense' => 'bg-amber-100 text-amber-700'];
@endphp
@section('content')
<x-dashboard-hero class="mb-6" :eyebrow="$range" title="Cash book" subtitle="Everything you bought and spent, set against what customers have paid.">
    <x-slot:chips>
        <span><i class="fas fa-calendar-days"></i> {{ $periods[$period] ?? 'Last 30 days' }}</span>
        <span><i class="fas fa-arrow-trend-down"></i> {{ $m($kpi['out']) }} spent</span>
    </x-slot:chips>
    <form method="GET" class="flex flex-wrap items-center gap-2 text-sm">
        @foreach(request()->only('type', 'category', 'method', 'q') as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
        <select name="period" onchange="this.form.submit()" class="rounded-xl border-0 bg-white/10 text-white ring-1 ring-white/20 px-3 py-2.5 focus:outline-none">
            @foreach($periods as $k => $l)<option class="text-slate-800" value="{{ $k }}" @selected($period === $k)>{{ $l }}</option>@endforeach
        </select>
        @if($period === 'custom')
            <input type="date" name="from" value="{{ request('from') }}" class="rounded-xl bg-white text-slate-800 px-2 py-2">
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-xl bg-white text-slate-800 px-2 py-2">
            <button class="rounded-xl bg-white text-slate-800 px-3 py-2 font-semibold">Apply</button>
        @endif
        <a href="{{ route('manager.cashbook.export', $keep + request()->only('type')) }}" class="rounded-xl bg-white text-slate-800 px-4 py-2.5 font-semibold hover:bg-gray-100 transition"><i class="fas fa-file-csv"></i> Download CSV</a>
    </form>
</x-dashboard-hero>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <x-stat-tile color="red" icon="fa-arrow-trend-down" title="Money out" :value="$m($kpi['out'])" sub="Purchases and expenses" />
    <x-stat-tile color="blue" icon="fa-cart-flatbed" title="Purchases" :value="$m($kpi['purchases'])" sub="Stock and goods bought" />
    <x-stat-tile color="orange" icon="fa-file-invoice-dollar" title="Expenses" :value="$m($kpi['expenses'])" sub="Running costs" />
    <x-stat-tile :color="$kpi['net'] >= 0 ? 'green' : 'red'" icon="fa-scale-balanced" title="Cash position" :value="$m($kpi['net'])" :sub="$m($kpi['in']) . ' received from customers − spent'" />
</div>

{{-- add / edit --}}
<section class="mb-6 rounded-xl bg-white" id="entry-form">
    <div class="{{ $head }}">{{ $edit ? 'Edit entry' : 'Add an entry' }}</div>
    <form method="POST" action="{{ $edit ? route('manager.cashbook.update', $edit) : route('manager.cashbook.store') }}" class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">@csrf @if($edit)@method('PUT')@endif
        @foreach($keep as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
        @if($errors->any())<div class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 sm:col-span-2 lg:col-span-4">{{ $errors->first() }}</div>@endif
        <label class="text-sm font-medium">Date<input type="date" name="entry_date" value="{{ old('entry_date', ($edit?->entry_date ?? today())->format('Y-m-d')) }}" max="{{ today()->format('Y-m-d') }}" required class="{{ $field }} mt-1"></label>
        <label class="text-sm font-medium">Type
            <select name="type" id="cbType" class="{{ $field }} mt-1">@foreach(CB::TYPES as $k => $l)<option value="{{ $k }}" @selected($type === $k)>{{ $l }}</option>@endforeach</select></label>
        <label class="text-sm font-medium">Category
            <select name="category" id="cbCategory" class="{{ $field }} mt-1" data-current="{{ old('category', $edit?->category) }}"></select></label>
        <label class="text-sm font-medium">Amount (GH₵)<input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $edit?->amount) }}" required class="{{ $field }} mt-1"></label>
        <label class="text-sm font-medium sm:col-span-2">What was it for?<input name="description" value="{{ old('description', $edit?->description) }}" required maxlength="200" placeholder="e.g. 20 pairs of jeans from Kantamanto" class="{{ $field }} mt-1"></label>
        <label class="text-sm font-medium">Paid to / supplier<input name="payee" value="{{ old('payee', $edit?->payee) }}" maxlength="120" class="{{ $field }} mt-1"></label>
        <label class="text-sm font-medium">Receipt / reference<input name="reference" value="{{ old('reference', $edit?->reference) }}" maxlength="60" class="{{ $field }} mt-1"></label>
        <label class="text-sm font-medium">Paid with
            <select name="method" class="{{ $field }} mt-1">@foreach(CB::METHODS as $k => $l)<option value="{{ $k }}" @selected(old('method', $edit?->method ?? 'cash') === $k)>{{ $l }}</option>@endforeach</select></label>
        <div class="flex items-end gap-2 sm:col-span-2">
            <button class="rounded-lg bg-[#1c3041] px-5 py-2 text-sm font-semibold text-white hover:bg-[#14212d]"><i class="fas fa-plus"></i> {{ $edit ? 'Save changes' : 'Add to cash book' }}</button>
            @if($edit)<a href="{{ route('manager.cashbook', $keep) }}" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Cancel</a>@endif
        </div>
    </form>
</section>
<script>
    (function () {
        const cats = @json(CB::CATEGORIES), type = document.getElementById('cbType'), sel = document.getElementById('cbCategory');
        function fill() {
            const cur = sel.dataset.current;
            sel.innerHTML = (cats[type.value] || []).map(c => '<option' + (c === cur ? ' selected' : '') + '>' + c.replace(/&/g, '&amp;') + '</option>').join('');
            sel.dataset.current = '';
        }
        type.addEventListener('change', fill); fill();
    })();
</script>

<div class="mb-6 grid gap-5 lg:grid-cols-3">
    <section class="rounded-xl bg-white overflow-hidden lg:col-span-1">
        <div class="{{ $head }}">Where the money went</div>
        <table class="w-full text-sm">
            @forelse($byCategory as $c)
                <tr class="border-t border-slate-100">
                    <td class="px-4 py-2"><a class="hover:underline" href="{{ route('manager.cashbook', array_merge(request()->except('page', 'edit', 'category'), ['category' => $c->category])) }}">{{ $c->category }}</a><div class="text-xs text-slate-400">{{ $c->n }} {{ $c->n == 1 ? 'entry' : 'entries' }}</div></td>
                    <td class="px-4 py-2 text-right font-semibold">{{ $m($c->total) }}</td>
                </tr>
            @empty <tr><td class="p-6 text-center text-slate-400">Nothing recorded in this period.</td></tr> @endforelse
        </table>
    </section>

    <section class="rounded-xl bg-white overflow-hidden lg:col-span-2">
        <div class="{{ $head }}">Entries ({{ $entries->total() }})</div>
        <form method="GET" class="grid grid-cols-2 gap-2 border-b border-slate-100 p-3 text-sm md:grid-cols-5">
            @foreach($keep as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
            <input name="q" value="{{ request('q') }}" placeholder="Search description, payee, reference…" class="{{ $field }} col-span-2">
            <select name="type" class="{{ $field }}"><option value="">All types</option>@foreach(CB::TYPES as $k => $l)<option value="{{ $k }}" @selected(request('type') === $k)>{{ $l }}</option>@endforeach</select>
            <select name="method" class="{{ $field }}"><option value="">Any method</option>@foreach(CB::METHODS as $k => $l)<option value="{{ $k }}" @selected(request('method') === $k)>{{ $l }}</option>@endforeach</select>
            <button class="rounded-lg bg-[#1c3041] px-4 py-2 font-semibold text-white"><i class="fas fa-filter"></i> Filter</button>
        </form>
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Date</th><th>Type</th><th>Details</th><th>Paid with</th><th class="text-right">Amount</th><th class="p-3"></th></tr></thead>
            @forelse($entries as $e)
                <tr class="border-t border-slate-100 align-top hover:bg-sky-50/50">
                    <td class="p-3 whitespace-nowrap text-slate-600">{{ $e->entry_date->format('d M Y') }}</td>
                    <td><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $badge[$e->type] ?? '' }}">{{ $e->type === 'purchase' ? 'Purchase' : 'Expense' }}</span></td>
                    <td class="max-w-[280px]"><div class="font-medium">{{ $e->description }}</div>
                        <div class="text-xs text-slate-400">{{ $e->category }}@if($e->payee) · {{ $e->payee }}@endif @if($e->reference) · Ref {{ $e->reference }}@endif</div></td>
                    <td class="text-xs text-slate-500">{{ CB::METHODS[$e->method] ?? $e->method }}</td>
                    <td class="text-right font-semibold whitespace-nowrap">{{ $m($e->amount) }}</td>
                    <td class="p-3 whitespace-nowrap text-right text-xs">
                        <a class="text-[#1c3041] hover:underline" href="{{ route('manager.cashbook', array_merge(request()->except('page'), ['edit' => $e->id])) }}#entry-form">Edit</a> ·
                        <form method="POST" action="{{ route('manager.cashbook.destroy', $e) }}" class="inline" onsubmit="return confirm('Delete this entry?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Delete</button></form>
                    </td>
                </tr>
            @empty <tr><td colspan="6" class="p-10 text-center text-slate-400"><i class="fas fa-book text-3xl mb-2 block"></i>No entries yet. Add your first purchase or expense above.</td></tr> @endforelse
            @if($entries->count())<tr class="border-t bg-slate-50 font-semibold"><td colspan="4" class="p-3 text-right">Total for this filter</td><td class="text-right">{{ $m($kpi['out']) }}</td><td></td></tr>@endif
        </table></div>
    </section>
</div>
<div class="mt-4">{{ $entries->links() }}</div>
@endsection
