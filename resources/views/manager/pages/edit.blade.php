@extends('layouts.app')
@section('title', 'Edit ' . $title . ' · Drezwel Go')
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Website pages" :title="$title" :subtitle="$help">
    <a href="{{ route('manager.pages') }}"><i class="fas fa-arrow-left"></i> All pages</a>
    <a href="{{ $url }}" target="_blank"><i class="fas fa-arrow-up-right-from-square"></i> View page</a>
</x-dashboard-hero>

@if($usingDraft)
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900"><i class="fas fa-circle-info"></i> This is our <strong>suggested draft</strong>. Read it, change anything that does not match how you work, then save.</div>
@endif

<div class="grid gap-5 lg:grid-cols-[1fr_300px]">
    <form method="POST" action="{{ route('manager.pages.update', $slug) }}" class="rounded-xl bg-white p-4">@csrf @method('PUT')
        @error('body')<div class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</div>@enderror
        <textarea name="body" id="body" rows="26" class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 font-mono text-[13px] leading-relaxed focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]">{{ old('body', $body) }}</textarea>
        <div class="mt-3 flex flex-wrap items-center gap-3">
            <button class="rounded-lg bg-[#1c3041] px-5 py-2 font-semibold text-white hover:bg-[#14212d]"><i class="fas fa-floppy-disk"></i> Save page</button>
            <button type="button" id="loadDraft" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200"><i class="fas fa-rotate-left"></i> Load the suggested draft</button>
            <span class="text-xs text-slate-500">Leave it empty and save to go back to the suggested draft.</span>
        </div>
    </form>

    <aside class="space-y-4 self-start text-sm">
        <div class="rounded-xl bg-white p-4">
            <div class="mb-2 font-semibold text-slate-800">Formatting</div>
            <ul class="space-y-1.5 text-slate-600">
                <li><code class="rounded bg-slate-100 px-1"># Heading</code>, <code class="rounded bg-slate-100 px-1">## Smaller heading</code></li>
                <li><code class="rounded bg-slate-100 px-1">**bold text**</code> for <strong>bold</strong></li>
                <li><code class="rounded bg-slate-100 px-1">- item</code> for a bullet list</li>
                <li><code class="rounded bg-slate-100 px-1">1. step</code> for a numbered list</li>
                <li><code class="rounded bg-slate-100 px-1">[words](https://…)</code> for a link</li>
                <li>Leave a blank line between paragraphs.</li>
            </ul>
            @if($slug === 'faqs')<p class="mt-3 rounded-lg bg-sky-50 p-2 text-sky-900">Write each question as <code>### Your question</code> and put the answer on the lines below it.</p>@endif
        </div>
        <div class="rounded-xl bg-white p-4">
            <div class="mb-2 font-semibold text-slate-800">Filled in for you</div>
            <p class="mb-2 text-slate-500">Type these and they are replaced with your details from Settings:</p>
            <div class="flex flex-wrap gap-1.5 font-mono text-xs">
                @foreach(['{business}', '{phone}', '{email}', '{address}', '{hours}', '{website}', '{shop}', '{contact}', '{track}', '{delivery}', '{returns}'] as $t)<code class="rounded bg-slate-100 px-1.5 py-0.5">{{ $t }}</code>@endforeach
            </div>
        </div>
    </aside>
</div>
<script type="application/json" id="draftText">@json($draft)</script>
<script>
    document.getElementById('loadDraft').addEventListener('click', function () {
        if (confirm('Replace what is in the box with the suggested draft? Nothing is saved until you click Save page.')) {
            document.getElementById('body').value = JSON.parse(document.getElementById('draftText').textContent);
        }
    });
</script>
@endsection
