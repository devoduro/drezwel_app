@extends('layouts.app')
@section('title', 'Delivery fees · Drezwel Go')
@php
    $m = fn ($v) => 'GH₵ ' . number_format((float) $v, 2);
    $field = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]';
    $head = 'px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm text-slate-800';
@endphp
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Settings" title="Delivery fees" subtitle="Customers choose their region and town at checkout and are charged the fee you set here.">
    <x-slot:chips>
        <span><i class="fas fa-map"></i> {{ $summary['regions'] }} regions</span>
        <span><i class="fas fa-city"></i> {{ $summary['priced'] }} of {{ $summary['towns'] }} towns priced</span>
    </x-slot:chips>
</x-dashboard-hero>

<div class="grid gap-5 lg:grid-cols-[240px_1fr]">
    {{-- regions --}}
    <aside class="rounded-xl bg-white overflow-hidden self-start">
        <div class="{{ $head }}">Regions</div>
        <nav class="max-h-[70vh] overflow-y-auto text-sm">
            @foreach($regions as $r)
                <a href="{{ route('manager.fees', ['region' => $r->id]) }}" class="flex items-center justify-between gap-2 border-t border-slate-100 px-4 py-2.5 {{ $r->id === $region->id ? 'bg-[#e4eaf0] font-semibold text-[#1c3041]' : 'hover:bg-slate-50' }}">
                    <span class="truncate">{{ $r->name }}</span>
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] {{ $r->priced > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $r->priced }}/{{ $r->towns }}</span>
                </a>
            @endforeach
        </nav>
    </aside>

    <div class="space-y-5 min-w-0">
        {{-- add towns --}}
        <section class="rounded-xl bg-white overflow-hidden">
            <div class="{{ $head }}">Add towns to {{ $region->name }}</div>
            <form method="POST" action="{{ route('manager.fees.towns', $region) }}" class="grid gap-3 p-4 md:grid-cols-[1fr_auto]">@csrf
                <div>
                    <textarea name="names" rows="4" required placeholder="One town per line. Add a fee after a comma to price it too:&#10;Kasoa, 30&#10;Winneba, 35&#10;Mankessim" class="{{ $field }} w-full">{{ old('names') }}</textarea>
                    @error('names')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-2 md:w-52">
                    <label class="block text-xs font-medium text-slate-600">Fee for lines without one (GH₵)<input type="number" step="0.01" min="0" name="fee" placeholder="optional" class="{{ $field }} mt-1 w-full"></label>
                    <button class="w-full rounded-lg bg-[#1c3041] px-4 py-2 text-sm font-semibold text-white hover:bg-[#14212d]"><i class="fas fa-plus"></i> Add towns</button>
                </div>
            </form>
        </section>

        {{-- towns and fees --}}
        <form method="POST" action="{{ route('manager.fees.update', $region) }}" class="rounded-xl bg-white overflow-hidden" id="feesForm">@csrf @method('PUT')
            <button name="do" value="save" tabindex="-1" class="hidden" aria-hidden="true"></button>
            <div class="{{ $head }} flex flex-wrap items-center justify-between gap-2"><span>{{ $region->name }} · {{ $zones->count() }} towns</span>
                <span class="text-xs font-normal text-slate-500">A town with no fee is hidden from customers.</span></div>

            <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 bg-slate-50 p-3 text-sm">
                <label class="flex items-center gap-2"><input type="checkbox" id="pickAll" class="accent-[#1c3041]"> Select all</label>
                <label class="flex items-center gap-2 text-slate-600"><input type="checkbox" name="all" value="1" class="accent-[#1c3041]"> Apply to <strong>every</strong> town in {{ $region->name }}</label>
                <span class="mx-1 hidden h-5 border-l border-slate-300 sm:block"></span>
                <input type="number" step="0.01" min="0" name="bulk_fee" placeholder="Fee GH₵" class="{{ $field }} w-28">
                <button name="do" value="set_fee" class="rounded-lg bg-emerald-600 px-3 py-2 font-semibold text-white hover:bg-emerald-700">Set fee for selected</button>
                <button name="do" value="activate" class="rounded-lg bg-white px-3 py-2 ring-1 ring-slate-300">Switch on</button>
                <button name="do" value="deactivate" class="rounded-lg bg-white px-3 py-2 ring-1 ring-slate-300">Switch off</button>
                <button name="do" value="clear_fee" class="rounded-lg bg-white px-3 py-2 ring-1 ring-slate-300">Clear fee</button>
                <button name="do" value="delete" onclick="return confirm('Delete the selected towns?')" class="rounded-lg bg-white px-3 py-2 text-red-600 ring-1 ring-red-200">Delete</button>
            </div>

            <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 p-3 text-sm">
                <label class="flex items-center gap-2 font-medium">Other towns in {{ $region->name }} (GH₵)
                    <input type="number" step="0.01" min="0" name="default_fee" value="{{ old('default_fee', $region->default_fee) }}" placeholder="shop default" class="{{ $field }} w-32"></label>
                <span class="text-xs text-slate-500">Charged when a customer picks "Other town" instead of one of the listed towns.</span>
            </div>

            <div class="overflow-x-auto"><table class="w-full text-sm">
                <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3 w-10"></th><th>Town / area</th><th>Fee (GH₵)</th><th class="text-center">Shown to customers</th><th class="p-3"></th></tr></thead>
                @forelse($zones as $z)
                    <tr class="border-t border-slate-100 {{ $z->fee === null ? 'bg-amber-50/40' : '' }}">
                        <td class="p-3"><input type="checkbox" name="ids[]" value="{{ $z->id }}" class="pick accent-[#1c3041]"></td>
                        <td class="font-medium">{{ $z->name }}@if($z->fee === null)<span class="ml-2 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-800">NOT PRICED</span>@endif</td>
                        <td><input type="number" step="0.01" min="0" name="fees[{{ $z->id }}]" value="{{ $z->fee }}" placeholder="—" class="{{ $field }} w-28"></td>
                        <td class="text-center"><input type="checkbox" name="active[{{ $z->id }}]" value="1" @checked($z->is_active) class="accent-[#1c3041]"></td>
                        <td class="p-3 text-right text-xs"><button form="del{{ $z->id }}" class="text-red-600 hover:underline" onclick="return confirm('Delete {{ addslashes($z->name) }}?')">Delete</button></td>
                    </tr>
                @empty <tr><td colspan="5" class="p-8 text-center text-slate-400">No towns yet. Add some above.</td></tr> @endforelse
            </table></div>

            <div class="flex items-center justify-between gap-3 border-t border-slate-100 p-3">
                <span class="text-xs text-slate-500">Edit any fees above, then save them all at once.</span>
                <button name="do" value="save" class="rounded-lg bg-[#1c3041] px-5 py-2 text-sm font-semibold text-white hover:bg-[#14212d]"><i class="fas fa-floppy-disk"></i> Save all fees</button>
            </div>
        </form>
        @foreach($zones as $z)<form id="del{{ $z->id }}" method="POST" action="{{ route('manager.fees.destroy', $z) }}" class="hidden">@csrf @method('DELETE')</form>@endforeach
    </div>
</div>
<script>
    document.getElementById('pickAll').addEventListener('change', e => document.querySelectorAll('.pick').forEach(c => c.checked = e.target.checked));
</script>
@endsection
