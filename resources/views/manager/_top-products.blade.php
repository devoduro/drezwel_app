{{-- Top four products by profit and by units sold. Expects $topProducts = ['profit' => rows, 'qty' => rows]. --}}
@php $money = fn ($v) => 'GH₵ ' . number_format((float) $v, 2); @endphp
<section class="bg-white rounded-xl overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-200 border-l-4 border-l-[#1c3041] font-semibold text-sm text-slate-800">Top performing products</div>

    <div class="px-4 pt-3 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-500"><i class="fas fa-coins text-emerald-600"></i> Most profit</div>
    <table class="w-full text-sm">
        @forelse($topProducts['profit'] as $i => $p)
            <tr class="border-t border-slate-100">
                <td class="pl-4 py-2 w-8 text-slate-400">{{ $i + 1 }}</td>
                <td class="py-2 max-w-[200px] truncate" title="{{ $p->name }}">{{ $p->name }}</td>
                <td class="py-2 text-right text-slate-500">{{ (int) $p->qty }} sold</td>
                <td class="py-2 pr-4 text-right font-semibold {{ $p->profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $money($p->profit) }}</td>
            </tr>
        @empty
            <tr class="border-t border-slate-100"><td class="px-4 py-3 text-slate-400">No product sales yet.</td></tr>
        @endforelse
    </table>

    <div class="px-4 pt-4 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-500 border-t border-slate-200"><i class="fas fa-boxes-stacked text-sky-600"></i> Most units sold</div>
    <table class="w-full text-sm">
        @forelse($topProducts['qty'] as $i => $p)
            <tr class="border-t border-slate-100">
                <td class="pl-4 py-2 w-8 text-slate-400">{{ $i + 1 }}</td>
                <td class="py-2 max-w-[200px] truncate" title="{{ $p->name }}">{{ $p->name }}</td>
                <td class="py-2 text-right text-slate-500">{{ $money($p->revenue) }}</td>
                <td class="py-2 pr-4 text-right font-semibold text-[#1c3041]">{{ (int) $p->qty }} units</td>
            </tr>
        @empty
            <tr class="border-t border-slate-100"><td class="px-4 py-3 text-slate-400">No product sales yet.</td></tr>
        @endforelse
    </table>
</section>
