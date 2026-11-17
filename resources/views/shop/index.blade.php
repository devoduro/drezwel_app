@extends('layouts.shop')
@section('title', (request('category') ?: (request()->boolean('deals') ? "Today's deals" : 'Shop')) . ' · ' . \App\Models\Setting::get('business_name'))
@php
    $tel = \App\Models\Setting::phoneLink();
    $filtered = $products !== null;
    $keep = request()->except('page', 'category');
@endphp
@section('content')
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 py-4">
    @if(\App\Models\Setting::get('store_open') !== '1')
        <div class="mb-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"><i class="fas fa-circle-info"></i> Online ordering is paused right now. Please call us to place an order.</div>
    @endif

    <div class="grid gap-4 lg:grid-cols-[230px_1fr]">
        {{-- Left filters (Jumia-style) --}}
        <aside class="hidden lg:block">
            <div class="rounded-xl bg-white p-4">
                <div class="mb-2 text-[13px] font-bold uppercase tracking-wide text-jm-grey">Category</div>
                <ul class="space-y-0.5 text-[14px]">
                    <li><a href="{{ route('shop.index', request()->except('page', 'category')) }}" class="flex justify-between rounded px-2 py-1.5 hover:bg-jm-bg {{ ! request('category') ? 'font-bold text-jm-primary' : '' }}">All products <span class="text-jm-grey">{{ $categoryCounts->sum() }}</span></a></li>
                    @foreach($categoryCounts as $c => $n)
                        <li><a href="{{ route('shop.index', $keep + ['category' => $c]) }}" class="flex justify-between rounded px-2 py-1.5 hover:bg-jm-bg {{ request('category') === $c ? 'font-bold text-jm-primary' : '' }}">{{ $c }} <span class="text-jm-grey">{{ $n }}</span></a></li>
                    @endforeach
                </ul>
            </div>

            <form method="GET" action="{{ route('shop.index') }}" class="mt-3 rounded-xl bg-white p-4">
                @foreach(request()->only('q', 'category', 'sort', 'deals') as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                <div class="mb-2 text-[13px] font-bold uppercase tracking-wide text-jm-grey">Price (GH₵)</div>
                <div class="flex items-center gap-2">
                    <input type="number" min="0" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="h-10 w-full min-w-0 rounded-lg border border-slate-300 px-2 text-sm focus:border-jm-primary focus:outline-none">
                    <span class="text-jm-grey">-</span>
                    <input type="number" min="0" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="h-10 w-full min-w-0 rounded-lg border border-slate-300 px-2 text-sm focus:border-jm-primary focus:outline-none">
                </div>
                <button class="mt-3 h-10 w-full rounded-lg bg-jm-primary text-sm font-bold uppercase text-white transition hover:bg-[#14212d]">Apply</button>
                @if(request()->filled('min_price') || request()->filled('max_price'))<a href="{{ route('shop.index', request()->except('min_price', 'max_price', 'page')) }}" class="mt-2 block text-center text-sm text-jm-primary">Clear price</a>@endif
            </form>

            <div class="mt-3 rounded-xl bg-white p-4 text-sm">
                <div class="mb-1 font-bold">Prefer to call?</div>
                <p class="text-jm-grey">We take orders by phone too.</p>
                @if($tel)<a href="tel:{{ $tel }}" class="mt-2 inline-flex items-center gap-2 font-bold text-jm-primary"><i class="fas fa-phone"></i> {{ \App\Models\Setting::get('phone') }}</a>@endif
            </div>
        </aside>

        <div class="min-w-0">
            @if($filtered)
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2 rounded-xl bg-white px-4 py-3">
                    <h1 class="text-[17px] font-bold">
                        {{ request('category') ?: (request()->boolean('deals') ? "Today's deals" : (request()->filled('q') ? 'Results for “' . request('q') . '”' : 'All products')) }}
                        <span class="text-sm font-normal text-jm-grey">· {{ $products->total() }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }} found</span>
                    </h1>
                    <form method="GET" action="{{ route('shop.index') }}" class="flex items-center gap-2 text-sm">
                        @foreach(request()->except('sort', 'page') as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                        <label class="text-jm-grey" for="sort">Sort by:</label>
                        <select id="sort" name="sort" onchange="this.form.submit()" class="h-9 rounded-lg border border-slate-300 bg-white px-2 focus:border-jm-primary focus:outline-none">
                            @foreach(['new' => 'Newest', 'price_asc' => 'Price: Low to High', 'price_desc' => 'Price: High to Low'] as $k => $l)<option value="{{ $k }}" @selected(request('sort', 'new') === $k)>{{ $l }}</option>@endforeach
                        </select>
                    </form>
                </div>
                {{-- phone: category chips since the sidebar is hidden --}}
                <div class="no-scrollbar mb-3 flex gap-2 overflow-x-auto lg:hidden">
                    <a href="{{ route('shop.index', $keep) }}" class="shrink-0 rounded-full border px-3.5 py-1.5 text-sm {{ request('category') ? 'bg-white' : 'border-jm-primary bg-jm-primary text-white' }}">All</a>
                    @foreach($categories as $c)<a href="{{ route('shop.index', $keep + ['category' => $c]) }}" class="shrink-0 rounded-full border px-3.5 py-1.5 text-sm {{ request('category') === $c ? 'border-jm-primary bg-jm-primary text-white' : 'bg-white' }}">{{ $c }}</a>@endforeach
                </div>

                @if($products->count())
                    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 xl:grid-cols-4">@foreach($products as $p) @include('shop._card', ['p' => $p]) @endforeach</div>
                    <div class="mt-6">{{ $products->links() }}</div>
                @else
                    <div class="rounded-xl bg-white p-12 text-center text-jm-grey"><i class="fas fa-shirt mb-3 block text-4xl text-slate-300"></i>No products found. @if($tel)Call <a class="font-bold text-jm-primary" href="tel:{{ $tel }}">{{ \App\Models\Setting::get('phone') }}</a> and we will help.@endif</div>
                @endif
            @else
                {{-- phone: category chips --}}
                <div class="no-scrollbar mb-3 flex gap-2 overflow-x-auto lg:hidden">
                    @foreach($categories as $c)<a href="{{ route('shop.index', ['category' => $c]) }}" class="shrink-0 rounded-full border bg-white px-3.5 py-1.5 text-sm">{{ $c }}</a>@endforeach
                </div>
                <div class="space-y-3">
                    @forelse($sections as $s)
                        @include('shop._row', ['title' => $s['title'], 'products' => $s['products'], 'url' => $s['url'], 'deal' => $s['deal'] ?? false])
                    @empty
                        <div class="rounded-xl bg-white p-12 text-center text-jm-grey"><i class="fas fa-shirt mb-3 block text-4xl text-slate-300"></i>New styles are coming soon. @if($tel)Call <a class="font-bold text-jm-primary" href="tel:{{ $tel }}">{{ \App\Models\Setting::get('phone') }}</a> to ask what we have.@endif</div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</div>
@if($products === null)@include('shop._seo')@endif
@endsection
