<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Remittance;
use App\Models\SmsLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Rider custody: cash collected from customers and items brought back, until the manager clears them. */
class RemittanceController extends Controller
{
    public function index()
    {
        $riders = User::where('role', 'rider')->orderBy('name')->get()->map(fn ($r) => (object) ['rider' => $r, 'c' => $r->custody()]);

        return view('manager.remittances', [
            'riders' => $riders,
            'history' => Remittance::with(['rider', 'recorder'])->latest('remitted_at')->limit(40)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'rider_id' => ['required', Rule::exists('users', 'id')->where('role', 'rider')],
            'cash' => 'required|numeric|min:0',
            'items' => 'required|integer|min:0',
            'method' => ['required', Rule::in(array_keys(Remittance::METHODS))],
            'notes' => 'nullable|string|max:200',
        ]);
        abort_if((float) $data['cash'] <= 0 && (int) $data['items'] === 0, 422, 'Enter cash or items.');

        Remittance::create($data + ['recorded_by' => $request->user()->id, 'remitted_at' => now()]);

        return back()->with('success', 'Remittance recorded.');
    }

    /** One tap: record exactly what the rider still owes, which clears them. */
    public function clear(Request $request, User $rider)
    {
        abort_unless($rider->isRider(), 404);
        $c = $rider->custody();
        if ($c['cleared']) {
            return back()->with('success', "{$rider->name} is already cleared.");
        }
        Remittance::create([
            'rider_id' => $rider->id, 'recorded_by' => $request->user()->id,
            'cash' => max($c['cash_held'], 0), 'items' => $c['items_pending'],
            'method' => 'cash', 'notes' => 'Cleared by manager', 'remitted_at' => now(),
        ]);

        return back()->with('success', "{$rider->name} cleared: GH₵ " . number_format(max($c['cash_held'], 0), 2) . " and {$c['items_pending']} item(s) received.");
    }

    /** The business pays the rider back the delivery fees they earned (and already handed in with the cash). */
    public function payFees(Request $request, User $rider)
    {
        abort_unless($rider->isRider(), 404);

        $result = \Illuminate\Support\Facades\DB::transaction(function () use ($rider, $request) {
            $rows = \App\Models\Delivery::where('rider_id', $rider->id)->where('status', 'delivered')
                ->where('rider_fee_status', 'pending')->where('delivery_fee', '>', 0)->lockForUpdate()->get();
            foreach ($rows as $d) {
                $d->update(['rider_fee_status' => 'paid', 'rider_fee_paid_at' => now(), 'rider_fee_paid_by' => $request->user()->id]);
                $d->logEvent('fee_paid', 'Delivery fee GH₵ ' . number_format($d->delivery_fee, 2) . " paid to {$rider->name}", $request->user()->id);
            }

            return [$rows->count(), (float) $rows->sum('delivery_fee')];
        });

        if ($result[0] === 0) {
            return back()->with('success', "{$rider->name} has no unpaid delivery fees.");
        }

        return back()->with('success', "Paid {$rider->name} GH₵ " . number_format($result[1], 2) . " in delivery fees for {$result[0]} delivery(ies).");
    }

    public function smsLogs()
    {
        return view('manager.sms', [
            'logs' => SmsLog::latest()->paginate(40),
            'stats' => ['total' => SmsLog::count(), 'sent' => SmsLog::where('success', true)->count(), 'failed' => SmsLog::where('success', false)->count()],
        ]);
    }
}
