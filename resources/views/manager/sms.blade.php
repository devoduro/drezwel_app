@extends('layouts.app')
@section('title', 'SMS log · Drezwel Go')
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Speso" title="SMS log" subtitle="Every text sent to customers. Delivery codes are never stored here.">
    <x-slot:chips>
        <span><i class="fas fa-comment-sms"></i> {{ $stats['total'] }} messages</span>
    </x-slot:chips>
</x-dashboard-hero>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
    <x-stat-tile color="blue" icon="fa-comment-sms" title="Total" :value="$stats['total']" sub="All attempts" />
    <x-stat-tile color="green" icon="fa-paper-plane" title="Sent" :value="$stats['sent']" sub="Accepted by Speso" />
    <x-stat-tile :color="$stats['failed'] ? 'red' : 'slate'" icon="fa-circle-exclamation" title="Failed" :value="$stats['failed']" sub="Check SPESO_API_KEY and balance" />
</div>

<div class="bg-white rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#42a5dc] font-semibold text-sm">Messages</div>
    <div class="overflow-x-auto"><table class="w-full text-sm">
        <thead class="text-left text-slate-500 text-xs"><tr><th class="p-3">When</th><th>To</th><th>Purpose</th><th>Message</th><th class="p-3">Result</th></tr></thead>
        @forelse($logs as $l)
            <tr class="border-t border-slate-100 align-top hover:bg-sky-50/50"><td class="p-3 whitespace-nowrap">{{ $l->created_at->format('d M, g:ia') }}</td><td>{{ $l->phone }}</td>
                <td><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ str_replace('_', ' ', $l->purpose) }}</span></td><td class="max-w-md text-slate-600">{{ $l->message }}</td>
                <td class="p-3"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $l->success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $l->success ? 'Sent' : 'Failed' }}</span><div class="text-xs text-slate-400 mt-1">{{ $l->response }}</div></td></tr>
        @empty <tr><td colspan="5" class="p-10 text-center text-slate-400"><i class="fas fa-comment-slash text-3xl mb-2 block"></i>No messages yet.</td></tr> @endforelse
    </table></div>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
