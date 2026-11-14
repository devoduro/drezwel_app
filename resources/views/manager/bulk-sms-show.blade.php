@extends('layouts.app')
@section('title', 'Campaign results · Drezwel Go')
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Bulk SMS" :title="$campaign->audience_label" :subtitle="$campaign->created_at->format('d M Y, g:ia') . ' · sent by ' . ($campaign->user?->name ?? 'unknown')">
    <x-slot:chips>
        <span><i class="fas fa-users"></i> {{ $campaign->recipients }} recipients</span>
        <span><i class="fas fa-comment-sms"></i> {{ $parts }} SMS part{{ $parts === 1 ? '' : 's' }} each</span>
    </x-slot:chips>
    <a href="{{ route('manager.bulk') }}"><i class="fas fa-arrow-left"></i> Bulk SMS</a>
</x-dashboard-hero>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 mb-6">
    <x-stat-tile color="blue" icon="fa-users" title="Recipients" :value="$campaign->recipients" sub="Phone numbers" />
    <x-stat-tile color="green" icon="fa-paper-plane" title="Accepted" :value="$campaign->sent" sub="Handed to Speso" />
    <x-stat-tile :color="$campaign->failed ? 'red' : 'slate'" icon="fa-circle-exclamation" title="Failed" :value="$campaign->failed" sub="Rejected by Speso" />
    <x-stat-tile :color="$campaign->not_sent ? 'orange' : 'slate'" icon="fa-hourglass-half" title="Not attempted" :value="$campaign->not_sent" :sub="$campaign->status === 'aborted' ? 'Sending stopped early' : 'None'" />
</div>

@if($campaign->note)<div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"><i class="fas fa-triangle-exclamation"></i> {{ $campaign->note }}</div>@endif

<section class="bg-white rounded-xl p-4 mb-5 border-l-4 border-l-[#1c3041]">
    <div class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-1">Message</div>
    <p class="whitespace-pre-line text-sm">{{ $campaign->message }}</p>
</section>

<div class="bg-white rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm">Who it went to</div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">Phone</th><th>Message sent</th><th class="p-3">Result</th></tr></thead>
        @forelse($logs as $l)
            <tr class="border-t border-slate-100 align-top hover:bg-sky-50/50"><td class="p-3 whitespace-nowrap">{{ $l->phone }}</td><td class="max-w-md text-slate-600">{{ $l->message }}</td>
                <td class="p-3"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $l->success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $l->success ? 'Accepted' : 'Failed' }}</span><div class="mt-1 text-xs text-slate-400">{{ $l->response }}</div></td></tr>
        @empty <tr><td colspan="3" class="p-8 text-center text-slate-400">No messages were attempted.</td></tr> @endforelse
    </table></div>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
