@extends('layouts.app')
@section('title', 'Completed · Drezwel Go')
@section('content')
<x-dashboard-hero class="mb-4 !p-5" eyebrow="History" title="Completed" subtitle="Your delivered and undelivered runs.">
    <x-slot:chips>
        <span><i class="fas fa-circle-check"></i> {{ $deliveries->where('status', 'delivered')->count() }} delivered</span>
        <span><i class="fas fa-triangle-exclamation"></i> {{ $deliveries->where('status', 'undelivered')->count() }} undelivered</span>
    </x-slot:chips>
</x-dashboard-hero>
<div class="space-y-3">
    @forelse($deliveries as $d) @include('rider._card', ['d' => $d]) @empty
        <div class="bg-white rounded-xl p-8 text-center text-slate-500"><i class="fas fa-truck text-3xl mb-2 block text-slate-300"></i>No completed deliveries yet.</div>
    @endforelse
</div>
@endsection
