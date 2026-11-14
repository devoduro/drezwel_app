<a href="{{ route('rider.show', $d) }}" class="block bg-white rounded-xl p-4 border-l-4 transition hover:shadow-md {{ $d->status === 'out_for_delivery' ? 'border-l-[#967adc]' : ($d->status === 'delivered' ? 'border-l-[#8cc152]' : ($d->status === 'undelivered' ? 'border-l-[#da4453]' : 'border-l-[#1c3041]')) }}">
    <div class="flex justify-between gap-2">
        <div class="font-semibold">{{ $d->customer->name }}</div>
        <x-badge :status="$d->status"/>
    </div>
    <div class="text-xs text-slate-500 mt-0.5">{{ $d->reference }} · {{ $d->delivery_date->format('d M') }} {{ $d->time_window }}</div>
    <div class="text-sm mt-2"><i class="fas fa-location-dot text-slate-400"></i> {{ $d->location }}</div>
    <div class="text-sm text-slate-500"><i class="fas fa-box text-slate-400"></i> {{ $d->package_description }}</div>
    <div class="text-sm mt-2 font-semibold {{ $d->balance > 0 ? 'text-[#c12f3e]' : 'text-green-600' }}">{{ $d->balance > 0 ? 'Collect GH₵ ' . number_format($d->balance, 2) : 'Fully paid' }}</div>
</a>
