{{-- Help-center menu (Jumia-style), shared by Contact, Wholesale and About. $active = the highlighted item's key. --}}
@php
    $items = [
        ['contact', route('site.contact'), 'Contact us', 'fa-headset'],
        ['wholesale', route('site.wholesale'), 'Wholesale orders', 'fa-boxes-stacked'],
        ['delivery-info', route('site.page', 'delivery-info'), 'Delivery & payment', 'fa-truck-fast'],
        ['return-policy', route('site.page', 'return-policy'), 'Return policy', 'fa-rotate-left'],
        ['track', route('track'), 'Track your order', 'fa-location-crosshairs'],
        ['about', route('site.about'), 'About us', 'fa-circle-info'],
        ['terms', route('site.page', 'terms'), 'Terms & conditions', 'fa-file-contract'],
        ['privacy-policy', route('site.page', 'privacy-policy'), 'Privacy policy', 'fa-shield-halved'],
    ];
@endphp
<aside class="hidden overflow-hidden rounded-xl bg-white lg:block">
    <div class="border-b border-slate-200 px-4 py-3 text-[13px] font-bold uppercase tracking-wide text-jm-grey">Help center</div>
    <nav class="py-1 text-[14px]">
        @foreach($items as [$key, $url, $label, $icon])
            <a href="{{ $url }}" class="flex items-center gap-3 px-4 py-2.5 {{ $active === $key ? 'bg-[#e4eaf0]/60 font-bold text-jm-primary' : 'hover:bg-jm-bg' }}"><i class="fas {{ $icon }} w-4 text-center {{ $active === $key ? '' : 'text-jm-grey' }}"></i>{{ $label }}</a>
        @endforeach
    </nav>
</aside>
