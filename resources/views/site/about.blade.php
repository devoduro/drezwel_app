@extends('layouts.shop')
@section('title', 'About us | ' . \App\Models\Setting::get('business_name'))
@section('description', \Illuminate\Support\Str::limit(strtok((string) \App\Models\Setting::get('about_text'), "\n"), 155))
@php
    $S = fn ($k) => \App\Models\Setting::get($k);
    $phones = \App\Models\Setting::phones();
    $wa = preg_replace('/\D/', '', (string) $S('whatsapp'));
    $paras = preg_split("/\n\s*\n/", trim((string) $S('about_text')));
    $cats = \App\Models\Product::inStore()->whereNotNull('category')->where('category', '!=', '')->distinct()->orderBy('category')->pluck('category');
@endphp
@section('content')
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 py-3 space-y-3">
    <nav class="text-[13px] text-jm-grey">
        <a class="hover:text-jm-primary" href="{{ route('home') }}">Home</a> <i class="fas fa-chevron-right mx-1 text-[8px]"></i>
        <span>Help center</span> <i class="fas fa-chevron-right mx-1 text-[8px]"></i> <span class="text-jm-dark">About us</span>
    </nav>

    <div class="grid items-start gap-3 lg:grid-cols-[250px_1fr]">
        @include('site._helpmenu', ['active' => 'about'])

        <div class="min-w-0 space-y-3">
            {{-- Banner --}}
            <section class="relative isolate overflow-hidden rounded-xl bg-jm-dark p-6 text-white sm:p-8">
                @if($S('hero_image'))
                    <img src="{{ asset('uploads/' . $S('hero_image')) }}" alt="" class="absolute inset-0 -z-20 h-full w-full object-cover">
                    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950/90 via-slate-950/70 to-slate-950/30"></div>
                @else
                    <div class="absolute inset-0 -z-20 bg-gradient-to-br from-[#1c3041] to-[#111d28]"></div>
                    <div class="pointer-events-none absolute -right-16 -top-20 -z-10 h-64 w-64 rounded-full bg-jm-light/25 blur-3xl"></div>
                @endif
                <span class="inline-block rounded bg-jm-light px-2.5 py-1 text-xs font-bold uppercase tracking-wider text-jm-dark">About us</span>
                <h1 class="mt-3 text-3xl font-bold leading-tight sm:text-4xl">{{ $S('business_name') }}: a step above <span class="text-jm-light">class</span>.</h1>
                <p class="mt-2 max-w-xl text-slate-200">{{ $S('tagline') }}</p>
            </section>

            {{-- Our story --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Who we are</div>
                <div class="space-y-3 p-4 leading-relaxed text-[#535357] sm:p-5">
                    @foreach($paras as $para)<p>{{ $para }}</p>@endforeach
                </div>
            </section>

            {{-- Why shop with us --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Why shop with {{ $S('business_name') }}</div>
                <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-5">
                    @foreach([
                        ['fa-gem', 'Quality first', 'Clothing chosen for how it looks and how it lasts.'],
                        ['fa-truck-fast', 'Delivered to you', 'By rider to your door in Accra, and by station bus or parcel office to other regions.'],
                        ['fa-hand-holding-dollar', 'Pay on delivery', 'See your order first, then pay the rider in cash or MoMo.'],
                        ['fa-location-crosshairs', 'Track every step', 'SMS updates and a delivery code, and you can follow your order online.'],
                    ] as [$ic, $t, $d])
                        <div class="flex gap-3"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-[#e4eaf0] text-xl text-jm-primary"><i class="fas {{ $ic }}"></i></span><div><div class="font-bold">{{ $t }}</div><div class="text-sm text-jm-grey">{{ $d }}</div></div></div>
                    @endforeach
                </div>
            </section>

            {{-- What we sell --}}
            @if($cats->count())
                <section class="overflow-hidden rounded-xl bg-white">
                    <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">What we sell</div>
                    <div class="flex flex-wrap gap-2 p-4 sm:p-5">
                        @foreach($cats as $c)<a href="{{ route('shop.index', ['category' => $c]) }}" class="rounded-full border border-slate-300 px-4 py-1.5 text-sm transition hover:border-jm-primary hover:text-jm-primary">{{ $c }}</a>@endforeach
                        <a href="{{ route('site.wholesale') }}" class="rounded-full border border-slate-300 px-4 py-1.5 text-sm transition hover:border-jm-primary hover:text-jm-primary">Wholesale</a>
                    </div>
                </section>
            @endif

            {{-- Find us + call to action --}}
            <div class="grid gap-3 md:grid-cols-2">
                <section class="overflow-hidden rounded-xl bg-white">
                    <div class="border-b border-slate-200 px-4 py-3 text-[15px] font-bold uppercase tracking-wide">Find us</div>
                    <div class="space-y-2 p-4 text-sm">
                        @if($S('address'))<div class="flex gap-3"><i class="fas fa-location-dot mt-0.5 w-5 text-jm-primary"></i><span>{{ $S('address') }}</span></div>@endif
                        @if($S('hours'))<div class="flex gap-3"><i class="fas fa-clock mt-0.5 w-5 text-jm-primary"></i><span>{{ $S('hours') }}</span></div>@endif
                        @foreach($phones as [$disp, $digits])<a class="flex gap-3 text-jm-primary hover:underline" href="tel:{{ $digits }}"><i class="fas fa-phone mt-0.5 w-5"></i>{{ $disp }}</a>@endforeach
                        @if($S('email'))<a class="flex gap-3 text-jm-primary hover:underline" href="mailto:{{ $S('email') }}"><i class="fas fa-envelope mt-0.5 w-5"></i>{{ $S('email') }}</a>@endif
                        @if($S('address'))<a target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query={{ urlencode($S('address')) }}" class="mt-2 flex items-center justify-center gap-2 rounded-lg border border-jm-primary px-4 py-2.5 font-bold uppercase text-jm-primary transition hover:bg-[#e4eaf0]/60"><i class="fas fa-map-location-dot"></i> Open in Google Maps</a>@endif
                    </div>
                </section>
                <section class="flex flex-col justify-center rounded-xl bg-jm-primary p-6 text-white">
                    <h2 class="text-xl font-bold">Ready to look a step above?</h2>
                    <p class="mt-1 text-white/90">Browse our latest styles, or call us and we will help you choose.</p>
                    <div class="mt-4 flex flex-wrap gap-3 text-sm font-bold uppercase tracking-wide">
                        <a href="{{ route('shop.index') }}" class="rounded-lg bg-white px-6 py-2.5 text-jm-primary shadow transition hover:bg-slate-100">Shop now</a>
                        <a href="{{ route('site.contact') }}" class="rounded-lg px-5 py-2.5 ring-2 ring-white/70 transition hover:bg-white/15">Contact us</a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
