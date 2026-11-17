@extends('layouts.app')
@section('title', 'Website pages · Drezwel Go')
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Website" title="Website pages" subtitle="Edit the policies, FAQs and other text customers read on your website.">
    <x-slot:chips><span><i class="fas fa-file-lines"></i> {{ $pages->count() }} pages</span></x-slot:chips>
</x-dashboard-hero>

<div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
    <i class="fas fa-circle-info"></i> Pages marked <strong>Suggested draft</strong> use wording we prepared for Drezwel. Please read each one and adjust it to match how you really work, then save it. Saving makes it your own.
</div>

<div class="grid gap-3 md:grid-cols-2">
    @foreach($pages as $p)
        <div class="rounded-xl bg-white p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="font-semibold text-slate-800">{{ $p['title'] }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $p['help'] }}</p>
                </div>
                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium {{ $p['draft'] ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-700' }}">{{ $p['draft'] ? 'Suggested draft' : 'Edited by you' }}</span>
            </div>
            <div class="mt-3 flex gap-2 text-sm font-semibold">
                <a href="{{ route('manager.pages.edit', $p['slug']) }}" class="rounded-lg bg-[#1c3041] px-4 py-2 text-white hover:bg-[#14212d]"><i class="fas fa-pen"></i> Edit</a>
                <a href="{{ $p['url'] }}" target="_blank" class="rounded-lg bg-slate-100 px-4 py-2 text-slate-700 hover:bg-slate-200"><i class="fas fa-arrow-up-right-from-square"></i> View page</a>
            </div>
        </div>
    @endforeach
</div>
@endsection
