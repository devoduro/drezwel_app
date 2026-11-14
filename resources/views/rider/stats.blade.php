@extends('layouts.app')
@section('title', 'Statistics · Drezwel Go')
@section('content')
<x-dashboard-hero class="mb-4 !p-5" eyebrow="{{ now()->format('l, d M') }}" title="Hi, {{ explode(' ', auth()->user()->name)[0] }}" subtitle="Here is how your deliveries are going.">
    <x-slot:chips>
        <span><i class="fas fa-circle-check"></i> {{ $today }} delivered today</span>
        <span><i class="fas fa-road"></i> {{ $open }} open</span>
    </x-slot:chips>
</x-dashboard-hero>
<div class="space-y-3">
    <x-stat-tile color="green" icon="fa-circle-check" title="Total delivered" :value="$delivered" :sub="'GH₵ ' . number_format($value, 2) . ' value'" />
    <x-stat-tile color="blue" icon="fa-route" title="Open runs" :value="$open" sub="Assigned or on the road" />
    <x-stat-tile color="red" icon="fa-triangle-exclamation" title="Undelivered" :value="$undelivered" sub="Return items to the manager" />
    <x-stat-tile color="purple" icon="fa-motorcycle" title="My delivery fees" :value="'GH₵ ' . number_format($custody['fees_owed'], 2)" :sub="'Owed to me · paid so far GH₵ ' . number_format($custody['fees_paid'], 2)" />
    <x-stat-tile color="orange" icon="fa-wallet" title="Cash I am holding" :value="'GH₵ ' . number_format($custody['cash_held'], 2)" :sub="$custody['items_pending'] . ' item(s) to hand in'" :badge="$custody['cleared'] ? 'Cleared' : 'Outstanding'" />
</div>
@endsection
