@extends('layouts.app')
@section('title', 'Messages · Drezwel Go')
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Website" title="Messages" subtitle="Questions and wholesale enquiries sent from your website. Call the customer back, then mark it handled.">
    <x-slot:chips>
        <span><i class="fas fa-envelope"></i> {{ $counts['new'] }} new</span>
        <span><i class="fas fa-inbox"></i> {{ $counts['all'] }} total</span>
    </x-slot:chips>
</x-dashboard-hero>

<form method="GET" class="bg-white rounded-xl p-3 flex flex-wrap gap-2 mb-4 text-sm">
    <select name="status" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
        @foreach(['new' => 'New', 'handled' => 'Handled', 'all' => 'All'] as $k => $l)<option value="{{ $k }}" @selected(request('status', 'new') === $k)>{{ $l }}</option>@endforeach
    </select>
    <select name="type" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
        <option value="">All types</option><option value="contact" @selected(request('type') === 'contact')>Contact</option><option value="wholesale" @selected(request('type') === 'wholesale')>Wholesale</option>
    </select>
    <button class="rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-4 py-2 font-semibold transition"><i class="fas fa-filter"></i> Filter</button>
</form>

<div class="space-y-3">
    @forelse($messages as $m)
        <section class="bg-white rounded-xl p-4 border-l-4 {{ $m->status === 'new' ? 'border-l-[#1c3041]' : 'border-l-[#8cc152] opacity-80' }}">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="font-semibold">{{ $m->name }}
                        <span class="ml-1 rounded-full px-2 py-0.5 text-xs {{ $m->type === 'wholesale' ? 'bg-purple-100 text-purple-700' : 'bg-sky-100 text-sky-700' }}">{{ $m->type === 'wholesale' ? 'Wholesale' : 'Contact' }}</span></div>
                    <div class="text-sm text-slate-500">
                        <a class="text-[#1c3041] hover:underline" href="tel:{{ $m->phone }}"><i class="fas fa-phone"></i> {{ $m->phone }}</a>
                        @if($m->email) · <a class="hover:underline" href="mailto:{{ $m->email }}">{{ $m->email }}</a>@endif
                        · {{ $m->created_at->format('d M Y, g:ia') }}
                    </div>
                </div>
                <form method="POST" action="{{ route('manager.messages.toggle', $m) }}">@csrf
                    <button class="rounded-lg border px-3 py-1.5 text-sm font-semibold hover:border-[#1c3041]">{{ $m->status === 'new' ? 'Mark handled' : 'Reopen' }}</button>
                </form>
            </div>
            @if($m->subject)<div class="mt-2 font-medium">{{ $m->subject }}</div>@endif
            <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $m->message }}</p>
        </section>
    @empty
        <div class="bg-white rounded-xl p-10 text-center text-slate-400"><i class="fas fa-inbox text-3xl mb-2 block"></i>No messages here.</div>
    @endforelse
</div>
<div class="mt-4">{{ $messages->links() }}</div>
@endsection
