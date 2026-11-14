@props(['status'])
@php
    $map = [
        'pending' => 'bg-[#e4eaf0] text-[#1c3041]',
        'assigned' => 'bg-blue-100 text-blue-800',
        'out_for_delivery' => 'bg-indigo-100 text-indigo-800',
        'delivered' => 'bg-green-100 text-green-800',
        'undelivered' => 'bg-red-100 text-red-800',
        'cancelled' => 'bg-slate-200 text-slate-600',
    ];
@endphp
<span class="inline-block rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap {{ $map[$status] ?? 'bg-slate-100' }}">{{ \App\Models\Delivery::STATUSES[$status] ?? $status }}</span>
