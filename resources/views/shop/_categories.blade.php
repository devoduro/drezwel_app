{{-- Row of category tiles: rounded grey squares with an icon and a label, like Hubtel's quick links. --}}
@php
    $icons = ['jean' => 'fa-shirt', 'sneaker' => 'fa-shoe-prints', 'shoe' => 'fa-shoe-prints', 'jacket' => 'fa-vest', 'coat' => 'fa-vest', 'hood' => 'fa-vest-patches', 'shirt' => 'fa-shirt',
        'vest' => 'fa-shirt', 'accessor' => 'fa-glasses', 'belt' => 'fa-glasses', 'active' => 'fa-person-running', 'street' => 'fa-hat-cowboy', 'dress' => 'fa-person-dress', 'trouser' => 'fa-shirt', 'cap' => 'fa-hat-cowboy'];
    $iconFor = function ($name) use ($icons) { foreach ($icons as $k => $i) { if (stripos($name, $k) !== false) return $i; } return 'fa-tag'; };
    $current = request('category');
@endphp
<div class="no-scrollbar -mx-4 flex gap-3 overflow-x-auto px-4 pb-1">
    <a href="{{ route('shop.index') }}" class="flex w-[84px] shrink-0 flex-col items-center gap-2 rounded-2xl border p-3 text-center text-[13px] transition {{ $current ? 'bg-slate-50 hover:bg-slate-100' : 'border-teal-600 bg-teal-50 text-teal-800' }}">
        <span class="grid h-11 w-11 place-items-center rounded-full bg-white text-lg text-teal-700 shadow-sm"><i class="fas fa-border-all"></i></span>All</a>
    @foreach($categories as $c)
        <a href="{{ route('shop.index', ['category' => $c]) }}" class="flex w-[84px] shrink-0 flex-col items-center gap-2 rounded-2xl border p-3 text-center text-[13px] leading-tight transition {{ $current === $c ? 'border-teal-600 bg-teal-50 text-teal-800' : 'bg-slate-50 hover:bg-slate-100' }}">
            <span class="grid h-11 w-11 place-items-center rounded-full bg-white text-lg text-teal-700 shadow-sm"><i class="fas {{ $iconFor($c) }}"></i></span>
            <span class="line-clamp-2">{{ $c }}</span>
        </a>
    @endforeach
</div>
