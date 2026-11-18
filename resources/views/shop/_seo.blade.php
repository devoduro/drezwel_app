{{-- Introduction text and FAQs at the bottom of the home and shop pages, kept small: both are folded away until the visitor opens them.
     Edit the wording in Manager > Website pages. --}}
@php
    $intro = \App\Support\Pages::text('shop_intro_text');
    $faqs = \App\Support\Markup::faqs(\App\Support\Pages::text('faq_text'));
@endphp
@if($intro !== '' || $faqs)
<div class="max-w-[1180px] mx-auto px-3 sm:px-4 pb-6 space-y-2">
    @if($intro !== '')
        <section class="rounded-xl bg-white p-4">
            <div id="seoText" class="md-text md-small relative max-h-[4.6rem] overflow-hidden transition-all">
                {!! \App\Support\Markup::render($intro) !!}
                <div id="seoFade" class="pointer-events-none absolute inset-x-0 bottom-0 h-10 bg-gradient-to-t from-white to-transparent"></div>
            </div>
            <button type="button" id="seoMore" class="mt-2 text-sm font-bold uppercase text-jm-primary hover:underline">Read more <i class="fas fa-chevron-down text-[10px]"></i></button>
        </section>
        <script>
            document.getElementById('seoMore').addEventListener('click', function () {
                var box = document.getElementById('seoText'), open = box.classList.toggle('is-open');
                box.style.maxHeight = open ? box.scrollHeight + 'px' : '';
                document.getElementById('seoFade').style.display = open ? 'none' : '';
                this.innerHTML = open ? 'Show less <i class="fas fa-chevron-up text-[10px]"></i>' : 'Read more <i class="fas fa-chevron-down text-[10px]"></i>';
            });
        </script>
    @endif
    @if($faqs)
        <details class="group rounded-xl bg-white">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 p-4 text-[15px] font-bold text-jm-dark">
                <span><i class="far fa-circle-question mr-1 text-jm-primary"></i> Frequently asked questions <span class="font-normal text-jm-grey">({{ count($faqs) }})</span></span>
                <i class="fas fa-chevron-down text-xs text-jm-grey transition group-open:rotate-180"></i>
            </summary>
            <div class="divide-y divide-slate-200 border-t border-slate-200">
                @foreach($faqs as $f)
                    <details class="group/q px-4 py-3">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-semibold text-jm-dark">{{ $f['q'] }}<i class="fas fa-plus text-[10px] text-jm-grey group-open/q:hidden"></i><i class="fas fa-minus hidden text-[10px] text-jm-grey group-open/q:inline"></i></summary>
                        <div class="md-text md-small mt-2 text-[#535357]">{!! \App\Support\Markup::render($f['a']) !!}</div>
                    </details>
                @endforeach
                <div class="px-4 py-3"><a href="{{ route('site.page', 'faqs') }}" class="text-sm font-bold uppercase text-jm-primary hover:underline">See all FAQs <i class="fas fa-chevron-right text-[10px]"></i></a></div>
            </div>
        </details>
    @endif
</div>
@include('site._md-style')
@once
<style>.md-small { font-size: 13px; line-height: 1.55; } .md-small h1, .md-small h2 { font-size: 1.05rem; margin-top: .9rem; } .md-small h2:first-child { margin-top: 0; } .md-small h3 { font-size: .95rem; }</style>
@endonce
@endif
