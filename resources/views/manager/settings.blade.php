@extends('layouts.app')
@section('title', 'Settings · Drezwel Go')
@php
    $input = 'mt-1 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]';
    $v = fn ($k) => old($k, $s[$k] ?? '');
@endphp
@section('content')
<x-dashboard-hero class="mb-6 !p-5" eyebrow="Business" title="Settings" subtitle="These details appear on your online store and on every invoice.">
    <a href="{{ route('shop.index') }}" target="_blank"><i class="fas fa-up-right-from-square"></i> View online store</a>
</x-dashboard-hero>

<form method="POST" enctype="multipart/form-data" action="{{ route('manager.settings.update') }}" class="bg-white rounded-xl p-5 grid md:grid-cols-2 gap-4 max-w-4xl">@csrf @method('PUT')
    <label class="text-sm font-medium">Business name<input name="business_name" required value="{{ $v('business_name') }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Tagline<input name="tagline" value="{{ $v('tagline') }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Phone(s) shown to customers - separate several with /<input name="phone" value="{{ $v('phone') }}" inputmode="tel" class="{{ $input }}" placeholder="0200680701/0203264474"></label>
    <label class="text-sm font-medium">WhatsApp number<input name="whatsapp" value="{{ $v('whatsapp') }}" inputmode="tel" class="{{ $input }}" placeholder="233241234567"></label>
    <label class="text-sm font-medium">Email<input type="email" name="email" value="{{ $v('email') }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Main website<input name="website" value="{{ $v('website') }}" class="{{ $input }}" placeholder="https://drezwell.com"></label>
    <label class="text-sm font-medium md:col-span-2">Address (shown on invoices)<input name="address" value="{{ $v('address') }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Delivery fee on website orders (GH₵)<input type="number" step="0.01" min="0" name="default_delivery_fee" required value="{{ $v('default_delivery_fee') }}" class="{{ $input }}"><span class="text-xs font-normal text-slate-500">Added to every online order. Leave 0 to confirm the fee by phone.</span></label>
    <label class="text-sm font-medium">Invoice thank-you line<input name="invoice_note" value="{{ $v('invoice_note') }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium md:col-span-2">Invoice terms (small print, e.g. returns policy)<input name="invoice_terms" value="{{ $v('invoice_terms') }}" class="{{ $input }}"></label>
    <div class="md:col-span-2 border-t pt-4 font-semibold"><i class="fas fa-image text-[#1c3041]"></i> Home page picture</div>
    <div class="md:col-span-2 text-sm">
        @if(! empty($s['hero_image']))
            <div class="relative mb-3 h-40 overflow-hidden rounded-xl bg-slate-900">
                <img src="{{ asset('uploads/' . $s['hero_image']) }}" alt="" class="h-full w-full object-cover opacity-60">
                <div class="absolute inset-0 grid place-items-center text-white font-bold text-xl">A step above class.</div>
            </div>
            <label class="mb-2 flex items-center gap-2 text-red-600"><input type="checkbox" name="remove_hero_image" value="1"> Remove the current picture</label>
        @endif
        <label class="block font-medium">Upload a picture (wide photo, at least 1600 x 800 px works best)
            <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-[#1c3041] file:px-4 file:py-2 file:text-white file:font-semibold"></label>
        <p class="mt-1 text-xs text-slate-500">It is shown behind the big headline on the home page, under a dark overlay so the words stay easy to read. Without a picture, a dark blue background is used.</p>
    </div>

    <div class="md:col-span-2 border-t pt-4 font-semibold"><i class="fas fa-share-nodes text-[#1c3041]"></i> Website: contact &amp; social links</div>
    <label class="text-sm font-medium">Second phone / helpline<input name="helpline" value="{{ $v('helpline') }}" class="{{ $input }}" placeholder="+233 24 695 6958"></label>
    <label class="text-sm font-medium">Opening hours<input name="hours" value="{{ $v('hours') }}" class="{{ $input }}" placeholder="Mon-Sat, 8am - 7pm"></label>
    <label class="text-sm font-medium">WhatsApp channel link<input name="whatsapp_channel" value="{{ $v('whatsapp_channel') }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Facebook<input name="facebook" value="{{ $v('facebook') }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">Instagram<input name="instagram" value="{{ $v('instagram') }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">X (Twitter)<input name="x" value="{{ $v('x') }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">TikTok<input name="tiktok" value="{{ $v('tiktok') }}" class="{{ $input }}"></label>
    <label class="text-sm font-medium">YouTube<input name="youtube" value="{{ $v('youtube') }}" class="{{ $input }}"></label>

    <div class="md:col-span-2 border-t pt-4 font-semibold"><i class="fas fa-file-lines text-[#1c3041]"></i> Website pages <span class="font-normal text-xs text-slate-500">(leave a blank line between paragraphs)</span></div>
    <label class="text-sm font-medium md:col-span-2">About us<textarea name="about_text" rows="6" class="{{ $input }}">{{ $v('about_text') }}</textarea></label>
    <label class="text-sm font-medium md:col-span-2">Delivery &amp; payment<textarea name="delivery_text" rows="8" class="{{ $input }}">{{ $v('delivery_text') }}</textarea></label>
    <label class="text-sm font-medium md:col-span-2">Return policy<textarea name="policy_returns" rows="6" class="{{ $input }}">{{ $v('policy_returns') }}</textarea></label>
    <label class="text-sm font-medium md:col-span-2">Terms &amp; conditions<textarea name="policy_terms" rows="8" class="{{ $input }}">{{ $v('policy_terms') }}</textarea></label>
    <label class="text-sm font-medium md:col-span-2">Privacy policy<textarea name="policy_privacy" rows="8" class="{{ $input }}">{{ $v('policy_privacy') }}</textarea><span class="text-xs font-normal text-slate-500">The starting text for the policies is a draft. Please read it and change it to match how you really work.</span></label>

    <label class="flex items-center gap-2 text-sm font-medium md:col-span-2"><input type="checkbox" name="store_open" value="1" @checked(old('store_open', $s['store_open'] ?? '1') == '1')> Accept orders on the online store (untick to pause - customers will be asked to call)</label>
    <div class="md:col-span-2"><button class="rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-6 py-2.5 font-semibold transition">Save settings</button></div>
</form>
@endsection
