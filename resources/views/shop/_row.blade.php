{{-- A titled section: coloured header band + a sideways-scrolling row of cards (Jumia-style). $deal = crimson "Flash Sales" band. --}}
@php $deal = $deal ?? false; @endphp
<section class="overflow-hidden rounded-xl {{ $deal ? 'bg-jm-red' : 'bg-white' }}">
    <div class="flex items-center justify-between gap-3 px-4 py-3 {{ $deal ? 'text-white' : 'border-b border-slate-200' }}">
        <div class="flex items-center gap-3">
            <h2 class="text-[17px] sm:text-xl font-bold leading-tight {{ $deal ? '' : 'text-jm-dark' }}">@if($deal)<i class="fas fa-bolt mr-1"></i>@endif{{ $title }}</h2>
            @if($deal)<span class="hidden sm:inline-flex items-center gap-1.5 rounded-lg bg-white px-2.5 py-1 text-[13px] font-bold text-jm-red"><i class="far fa-clock"></i> Limited time offers</span>@endif
        </div>
        @isset($url)<a href="{{ $url }}" class="shrink-0 text-sm font-bold uppercase {{ $deal ? 'text-white hover:underline' : 'text-jm-orange hover:underline' }}">See all <i class="fas fa-chevron-right text-[10px]"></i></a>@endisset
    </div>
    <div class="no-scrollbar flex snap-x gap-2.5 overflow-x-auto p-3 {{ $deal ? '' : 'bg-white' }}">
        @foreach($products as $p) @include('shop._card', ['p' => $p, 'row' => true]) @endforeach
    </div>
</section>
