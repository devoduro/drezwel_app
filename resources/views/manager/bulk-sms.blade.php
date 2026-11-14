@extends('layouts.app')
@section('title', 'Bulk SMS · Drezwel Go')
@php
    $icons = ['customers_all' => 'fa-users', 'customers_owing' => 'fa-hand-holding-dollar', 'customers_open' => 'fa-truck-fast', 'customers_recent' => 'fa-clock-rotate-left', 'riders_all' => 'fa-motorcycle', 'selected' => 'fa-user-check', 'numbers' => 'fa-keyboard'];
    $hints = ['customers_all' => 'Everyone who has ordered', 'customers_owing' => 'Unpaid balances: a payment reminder', 'customers_open' => 'Orders being prepared or delivered', 'customers_recent' => 'Ordered in the last 30 days', 'riders_all' => 'Every active rider', 'selected' => 'Tick customers and riders yourself', 'numbers' => 'Paste phone numbers'];
    $field = 'rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1c3041]';
@endphp
@section('content')
<x-dashboard-hero class="mb-6" eyebrow="Speso" title="Bulk SMS" subtitle="Send one message to a whole group of customers or riders, to people you pick, or to numbers you type.">
    <x-slot:chips>
        <span><i class="fas fa-bullhorn"></i> {{ $campaigns->count() }} recent campaigns</span>
        <span><i class="fas fa-comment-sms"></i> {{ $hasKey ? 'Speso connected' : 'Speso key missing' }}</span>
    </x-slot:chips>
    <a href="{{ route('manager.sms') }}"><i class="fas fa-list"></i> SMS log</a>
</x-dashboard-hero>

@unless($hasKey)
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><i class="fas fa-triangle-exclamation"></i> No Speso API key is set, so nothing can be sent yet. Add <code>SPESO_API_KEY</code> to <code>.env</code>, then use "Send test SMS" on the SMS log page to check it.</div>
@endunless

<div class="grid gap-5 xl:grid-cols-[1fr_360px]">
<form method="POST" action="{{ route('manager.bulk.send') }}" id="bulkForm" class="space-y-4 min-w-0">@csrf
    {{-- 1. Audience --}}
    <section class="bg-white rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm">1. Who should get it?</div>
        <div class="grid gap-2 p-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($groups as $key => $label)
                <label class="cursor-pointer">
                    <input type="radio" name="audience" value="{{ $key }}" class="peer sr-only" @checked(old('audience', 'customers_all') === $key)>
                    <span class="flex h-full items-start gap-3 rounded-lg border-2 border-slate-200 p-3 transition peer-checked:border-[#1c3041] peer-checked:bg-[#f3f6f9] hover:border-slate-300">
                        <i class="fas {{ $icons[$key] }} mt-0.5 w-5 text-center text-[#1c3041]"></i>
                        <span class="text-sm"><span class="block font-semibold">{{ $label }}</span><span class="text-xs text-slate-500">{{ $hints[$key] }}</span></span>
                    </span>
                </label>
            @endforeach
        </div>

        {{-- hand-picked people --}}
        <div id="pickBox" class="hidden border-t border-slate-100 p-4">
            <div class="mb-2 flex flex-wrap items-center gap-2">
                <input id="pickSearch" placeholder="Search name or phone…" class="{{ $field }} flex-1 min-w-48">
                <button type="button" id="pickAll" class="rounded-lg border px-3 py-2 text-sm font-medium hover:border-[#1c3041]">Tick all shown</button>
                <button type="button" id="pickNone" class="rounded-lg border px-3 py-2 text-sm font-medium hover:border-[#1c3041]">Clear</button>
            </div>
            <div class="grid gap-3 md:grid-cols-2">
                @foreach([['Riders', 'rider_ids', $riders, 'fa-motorcycle'], ['Customers', 'customer_ids', $customers, 'fa-users']] as [$title, $name, $list, $ic])
                    <div class="rounded-lg border border-slate-200">
                        <div class="border-b bg-slate-50 px-3 py-2 text-xs font-bold uppercase tracking-wide text-slate-500"><i class="fas {{ $ic }}"></i> {{ $title }} ({{ $list->count() }})</div>
                        <div class="max-h-60 overflow-y-auto divide-y divide-slate-100 text-sm">
                            @forelse($list as $p)
                                <label class="pick flex cursor-pointer items-center gap-2 px-3 py-2 hover:bg-slate-50" data-text="{{ mb_strtolower($p->name . ' ' . $p->phone) }}">
                                    <input type="checkbox" name="{{ $name }}[]" value="{{ $p->id }}" @checked(in_array($p->id, (array) old($name, [])))>
                                    <span class="min-w-0 flex-1 truncate">{{ $p->name }}</span>
                                    <span class="{{ $p->phone ? 'text-slate-400' : 'text-red-500' }} text-xs">{{ $p->phone ?: 'no phone' }}</span>
                                </label>
                            @empty <div class="p-3 text-slate-400">None yet.</div> @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- typed numbers --}}
        <div id="numbersBox" class="hidden border-t border-slate-100 p-4">
            <label class="text-sm font-medium">Phone numbers <span class="font-normal text-slate-500">(one per line or separated by commas; you can put a name first, like "Ama 0241234567")</span>
                <textarea name="numbers" rows="5" class="{{ $field }} mt-1 w-full font-mono" placeholder="0241234567&#10;Kofi 0551112233">{{ old('numbers') }}</textarea></label>
        </div>

        {{-- live count --}}
        <div id="countBox" class="border-t border-slate-100 bg-slate-50 px-4 py-3 text-sm text-slate-600">Counting…</div>
    </section>

    {{-- 2. Message --}}
    <section class="bg-white rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm">2. Your message</div>
        <div class="p-4 space-y-3">
            <textarea name="message" id="msg" rows="5" maxlength="640" required class="{{ $field }} w-full" placeholder="e.g. Hello {name}, new styles have arrived at Drezwel! Call 0200680701 to order.">{{ old('message') }}</textarea>
            <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                <button type="button" id="insName" class="rounded-lg border px-3 py-1.5 font-medium text-slate-700 hover:border-[#1c3041]">Insert {name}</button>
                <span id="counter">0 characters · 0 SMS</span>
            </div>
            <div id="unicodeNote" class="hidden rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 text-xs text-amber-800">This message has a special character (like ₵), so each SMS part holds only 70 characters instead of 160. Writing "GHS 50" instead of "GH₵ 50" keeps it shorter and cheaper.</div>
            <div class="rounded-xl bg-slate-100 p-3">
                <div class="mb-1 text-[11px] font-bold uppercase tracking-wide text-slate-400">Preview</div>
                <div id="preview" class="max-w-md whitespace-pre-line rounded-2xl rounded-tl-sm bg-white px-3 py-2 text-sm shadow-sm">Your message appears here.</div>
            </div>
        </div>
    </section>

    @if($errors->any())<div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    <div class="flex flex-wrap items-center gap-3">
        <button id="sendBtn" class="rounded-lg bg-[#1c3041] hover:bg-[#14212d] text-white px-6 py-3 font-semibold transition disabled:opacity-40 disabled:cursor-not-allowed" disabled><i class="fas fa-paper-plane"></i> <span id="sendLabel">Send</span></button>
        <span class="text-xs text-slate-500">Only message people who ordered from you or expect to hear from you. Up to {{ $max }} people per send.</span>
    </div>
</form>

{{-- Recent campaigns --}}
<aside class="space-y-3">
    <section class="bg-white rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm">Recent campaigns</div>
        <div class="divide-y divide-slate-100 text-sm">
            @forelse($campaigns as $c)
                <a href="{{ route('manager.bulk.show', $c) }}" class="block px-4 py-3 hover:bg-slate-50">
                    <div class="flex items-center justify-between gap-2"><span class="font-semibold">{{ $c->audience_label }}</span>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $c->status === 'done' && ! $c->failed ? 'bg-green-100 text-green-800' : ($c->status === 'sending' ? 'bg-slate-100 text-slate-600' : 'bg-amber-100 text-amber-800') }}">{{ $c->status === 'aborted' ? 'Stopped' : ($c->failed ? $c->failed . ' failed' : 'Sent') }}</span></div>
                    <div class="mt-0.5 line-clamp-2 text-slate-600">{{ $c->message }}</div>
                    <div class="mt-1 text-xs text-slate-400">{{ $c->created_at->format('d M, g:ia') }} · {{ $c->sent }}/{{ $c->recipients }} delivered to Speso · {{ $c->user?->name }}</div>
                </a>
            @empty <div class="p-8 text-center text-slate-400"><i class="fas fa-bullhorn mb-2 block text-3xl"></i>No campaigns yet.</div> @endforelse
        </div>
    </section>
</aside>
</div>

<script>
(function () {
    const form = document.getElementById('bulkForm'), msg = document.getElementById('msg'), $ = id => document.getElementById(id);
    const GSM = "@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ ÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà", EXT = '^{}\\[~]|€';
    let sample = 'Ama', count = 0, over = false, timer;

    function measure(t) {
        let u = 0, uni = false;
        for (const ch of t) { if (GSM.includes(ch)) u++; else if (EXT.includes(ch)) u += 2; else { uni = true; u++; } }
        if (uni) u = [...t].length;
        const parts = u === 0 ? 0 : u <= (uni ? 70 : 160) ? 1 : Math.ceil(u / (uni ? 67 : 153));
        return {u, uni, parts};
    }
    function paintMessage() {
        const m = measure(msg.value);
        $('counter').textContent = m.u + ' characters · ' + m.parts + ' SMS part' + (m.parts === 1 ? '' : 's') + (m.parts > 1 ? ' each' : '');
        $('unicodeNote').classList.toggle('hidden', !m.uni);
        $('preview').textContent = msg.value ? msg.value.replace(/\{name\}/gi, sample) : 'Your message appears here.';
        button();
    }
    function button() {
        const ok = count > 0 && !over && msg.value.trim().length >= 3;
        $('sendBtn').disabled = !ok;
        $('sendLabel').textContent = count > 0 ? 'Send to ' + count + (count === 1 ? ' person' : ' people') : 'Send';
    }
    function audience() { return form.querySelector('input[name=audience]:checked').value; }
    function panels() { const a = audience(); $('pickBox').classList.toggle('hidden', a !== 'selected'); $('numbersBox').classList.toggle('hidden', a !== 'numbers'); }

    async function refreshCount() {
        $('countBox').textContent = 'Counting…';
        const fd = new FormData(form); fd.delete('message');
        try {
            const r = await fetch(@json(route('manager.bulk.preview')), {method: 'POST', body: fd, headers: {'Accept': 'application/json'}});
            if (r.status === 429) { $('countBox').textContent = 'Too many checks in a short time. Wait a moment and try again.'; count = 0; paintMessage(); return; }
            const j = await r.json(); count = j.count; over = j.over_limit; if (j.sample[0]) sample = String(j.sample[0]).split(' ')[0];
            let html = count ? '<strong class="text-slate-800">' + count + '</strong> ' + (count === 1 ? 'person' : 'people') + ' will get this' + (j.sample.length ? ': ' + j.sample.map(s => s.replace(/</g, '&lt;')).join(', ') + (count > j.sample.length ? '…' : '') : '') : '<span class="text-red-600">Nobody selected yet.</span>';
            if (over) html += ' <span class="font-semibold text-red-600">· over the limit of {{ $max }}, please narrow it down.</span>';
            if (j.skipped_count) html += '<div class="mt-1 text-xs text-amber-700"><i class="fas fa-triangle-exclamation"></i> ' + j.skipped_count + ' skipped: ' + j.skipped.map(s => (s.label + ' (' + s.reason.toLowerCase() + ')').replace(/</g, '&lt;')).join('; ') + (j.skipped_count > j.skipped.length ? '…' : '') + '</div>';
            $('countBox').innerHTML = html;
        } catch (e) { $('countBox').textContent = 'Could not count recipients.'; count = 0; }
        paintMessage();
    }
    const later = () => { clearTimeout(timer); timer = setTimeout(refreshCount, 450); };

    form.querySelectorAll('input[name=audience]').forEach(r => r.addEventListener('change', () => { panels(); refreshCount(); }));
    form.addEventListener('change', e => { if (e.target.matches('.pick input')) later(); });
    form.querySelector('textarea[name=numbers]').addEventListener('input', later);
    $('pickSearch').addEventListener('input', e => { const q = e.target.value.toLowerCase(); document.querySelectorAll('.pick').forEach(l => l.classList.toggle('hidden', q && !l.dataset.text.includes(q))); });
    $('pickAll').addEventListener('click', () => { document.querySelectorAll('.pick:not(.hidden) input').forEach(i => i.checked = true); later(); });
    $('pickNone').addEventListener('click', () => { document.querySelectorAll('.pick input').forEach(i => i.checked = false); later(); });
    $('insName').addEventListener('click', () => { const s = msg.selectionStart ?? msg.value.length; msg.value = msg.value.slice(0, s) + '{name}' + msg.value.slice(msg.selectionEnd ?? s); msg.focus(); paintMessage(); });
    msg.addEventListener('input', paintMessage);
    form.addEventListener('submit', e => { if (!confirm('Send this SMS to ' + count + (count === 1 ? ' person' : ' people') + '? This cannot be undone.')) e.preventDefault(); });

    panels(); paintMessage(); refreshCount();
})();
</script>
@endsection
