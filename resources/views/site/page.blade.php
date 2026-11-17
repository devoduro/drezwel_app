@extends('layouts.shop')
@section('title', $title . ' | ' . \App\Models\Setting::get('business_name'))
@section('description', \Illuminate\Support\Str::limit(\App\Support\Markup::plain($body), 155))
@section('content')
@include('site._md-style')
<div class="bg-jm-dark text-white">
    <div class="max-w-[1180px] mx-auto px-4 py-10"><h1 class="text-3xl font-bold">{{ $title }}</h1></div>
</div>
<div class="max-w-3xl mx-auto px-4 py-10">
    @if($slug === 'faqs')
        <div class="divide-y divide-slate-200 rounded-xl bg-white">
            @foreach(\App\Support\Markup::faqs($body) as $f)
                <details class="group p-5" @if($loop->first) open @endif>
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-lg font-semibold text-jm-dark">{{ $f['q'] }}<i class="fas fa-chevron-down text-xs text-jm-grey transition group-open:rotate-180"></i></summary>
                    <div class="md-text mt-2">{!! \App\Support\Markup::render($f['a']) !!}</div>
                </details>
            @endforeach
        </div>
    @else
        <article class="rounded-xl bg-white p-6 sm:p-8"><div class="md-text">{!! \App\Support\Markup::render($body) !!}</div></article>
    @endif
    <div class="mt-6 flex flex-wrap gap-3 text-sm font-semibold">
        <a href="{{ route('site.contact') }}" class="rounded-xl bg-white px-5 py-2.5">Questions? Contact us</a>
        <a href="{{ route('shop.index') }}" class="rounded-xl bg-jm-primary text-white px-5 py-2.5">Back to shop</a>
    </div>
</div>
@endsection
