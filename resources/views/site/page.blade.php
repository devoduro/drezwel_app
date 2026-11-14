@extends('layouts.shop')
@section('title', $title . ' | ' . \App\Models\Setting::get('business_name'))
@section('description', \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($body))), 155))
@section('content')
<div class="bg-jm-dark text-white">
    <div class="max-w-[1180px] mx-auto px-4 py-10"><h1 class="text-3xl font-bold">{{ $title }}</h1></div>
</div>
<div class="max-w-3xl mx-auto px-4 py-10">
    <article class="rounded-xl bg-white p-6 sm:p-8 text-slate-700 leading-relaxed space-y-4">
        @php $paras = preg_split("/\n\s*\n/", trim($body)); @endphp
        @foreach($paras as $i => $para)
            @if($i === 0 && ! str_contains($para, "\n") && mb_strlen($para) < 60)
                {{-- first short line is the page's own heading, already shown above --}}
            @else
                <p class="whitespace-pre-line">{{ $para }}</p>
            @endif
        @endforeach
    </article>
    <div class="mt-6 flex flex-wrap gap-3 text-sm font-semibold">
        <a href="{{ route('site.contact') }}" class="rounded-xl bg-white px-5 py-2.5">Questions? Contact us</a>
        <a href="{{ route('shop.index') }}" class="rounded-xl bg-jm-orange text-white px-5 py-2.5">Back to shop</a>
    </div>
</div>
@endsection
