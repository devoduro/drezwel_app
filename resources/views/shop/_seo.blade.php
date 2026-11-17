{{-- Introduction text and FAQs at the bottom of the home and shop pages (editable in Manager > Website pages). --}}
@php
    $intro = \App\Support\Pages::text('shop_intro_text');
    $faqs = \App\Support\Markup::faqs(\App\Support\Pages::text('faq_text'));
@endphp
@if($intro !== '' || $faqs)
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 pb-6 space-y-3">
    @if($intro !== '')
        <section class="rounded-xl bg-white p-5 sm:p-6"><div class="md-text">{!! \App\Support\Markup::render($intro) !!}</div></section>
    @endif
    @if($faqs)
        <section class="rounded-xl bg-white p-5 sm:p-6">
            <h2 class="mb-3 text-xl font-bold text-jm-dark">Frequently asked questions</h2>
            <div class="divide-y divide-slate-200 rounded-lg border border-slate-200">
                @foreach($faqs as $f)
                    <details class="group p-4">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 font-semibold text-jm-dark">{{ $f['q'] }}<i class="fas fa-chevron-down text-xs text-jm-grey transition group-open:rotate-180"></i></summary>
                        <div class="md-text mt-2 text-[#535357]">{!! \App\Support\Markup::render($f['a']) !!}</div>
                    </details>
                @endforeach
            </div>
            <a href="{{ route('site.page', 'faqs') }}" class="mt-3 inline-block text-sm font-bold uppercase text-jm-primary hover:underline">All FAQs <i class="fas fa-chevron-right text-[10px]"></i></a>
        </section>
    @endif
</div>
@include('site._md-style')
@endif
