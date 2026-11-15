<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\CashbookEntry;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** The cash book: what the business bought and spent, set against what customers have paid. */
class CashbookController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = DashboardController::range(new Request(['period' => $request->input('period', '30d')] + $request->only(['from', 'to'])));

        $scope = fn () => CashbookEntry::query()
            ->when($from, fn ($q) => $q->whereDate('entry_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('entry_date', '<=', $to))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->input('category')))
            ->when($request->filled('method'), fn ($q) => $q->where('method', $request->input('method')))
            ->when(trim((string) $request->input('q')) !== '', function ($q) use ($request) {
                $t = trim((string) $request->input('q'));
                $q->where(fn ($w) => $w->where('description', 'like', "%{$t}%")->orWhere('payee', 'like', "%{$t}%")->orWhere('reference', 'like', "%{$t}%"));
            });

        $out = (float) $scope()->sum('amount');
        $byType = $scope()->selectRaw('type, SUM(amount) total')->groupBy('type')->pluck('total', 'type');
        $byCategory = $scope()->selectRaw('category, type, COUNT(*) n, SUM(amount) total')->groupBy('category', 'type')->orderByDesc('total')->get();

        // Money in: what customers have paid on orders in the same dates (the type/category filters do not apply to it).
        $in = (float) Delivery::active()
            ->when($from, fn ($q) => $q->whereDate('delivery_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('delivery_date', '<=', $to))->sum('amount_paid');

        return view('manager.cashbook', [
            'entries' => $scope()->with('creator')->latest('entry_date')->latest('id')->paginate(30)->withQueryString(),
            'edit' => $request->filled('edit') ? CashbookEntry::find($request->input('edit')) : null,
            'kpi' => ['out' => $out, 'purchases' => (float) ($byType['purchase'] ?? 0), 'expenses' => (float) ($byType['expense'] ?? 0), 'in' => $in, 'net' => $in - $out],
            'byCategory' => $byCategory,
            'period' => $request->input('period', '30d'), 'periods' => DashboardController::PERIODS,
            'from' => $from, 'to' => $to,
        ]);
    }

    public function store(Request $request)
    {
        CashbookEntry::create($this->validated($request) + ['created_by' => $request->user()->id]);

        return redirect()->route('manager.cashbook', $request->only(['period', 'from', 'to']))->with('success', 'Entry added to the cash book.');
    }

    public function update(Request $request, CashbookEntry $entry)
    {
        $entry->update($this->validated($request));

        return redirect()->route('manager.cashbook', $request->only(['period', 'from', 'to']))->with('success', 'Entry updated.');
    }

    public function destroy(CashbookEntry $entry)
    {
        $entry->delete();

        return back()->with('success', 'Entry deleted.');
    }

    public function export(Request $request)
    {
        [$from, $to] = DashboardController::range(new Request(['period' => $request->input('period', 'all')] + $request->only(['from', 'to'])));
        $rows = CashbookEntry::query()
            ->when($from, fn ($q) => $q->whereDate('entry_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('entry_date', '<=', $to))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->orderBy('entry_date')->orderBy('id')->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date', 'Type', 'Category', 'Description', 'Paid to / supplier', 'Amount (GHS)', 'Method', 'Reference']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->entry_date->format('Y-m-d'), CashbookEntry::TYPES[$r->type] ?? $r->type, $r->category, $r->description, $r->payee, number_format($r->amount, 2, '.', ''), CashbookEntry::METHODS[$r->method] ?? $r->method, $r->reference]);
            }
            fputcsv($out, ['', '', '', 'TOTAL', '', number_format($rows->sum('amount'), 2, '.', '')]);
            fclose($out);
        }, 'drezwel-cashbook-' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validated(Request $request): array
    {
        $type = $request->input('type');

        return $request->validate([
            'entry_date' => 'required|date|before_or_equal:today',
            'type' => ['required', Rule::in(array_keys(CashbookEntry::TYPES))],
            'category' => ['required', Rule::in(CashbookEntry::CATEGORIES[$type] ?? [])],
            'description' => 'required|string|max:200',
            'payee' => 'nullable|string|max:120',
            'amount' => 'required|numeric|min:0.01|max:9999999',
            'method' => ['required', Rule::in(array_keys(CashbookEntry::METHODS))],
            'reference' => 'nullable|string|max:60',
        ], ['category.in' => 'Choose a category that matches the type.', 'entry_date.before_or_equal' => 'The date cannot be in the future.']);
    }
}
