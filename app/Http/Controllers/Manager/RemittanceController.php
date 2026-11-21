<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Remittance;
use App\Models\SmsLog;
use App\Models\User;
use App\Services\SmsService;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Rider custody: cash collected from customers and items brought back, until the manager clears them. */
class RemittanceController extends Controller
{
    public function index()
    {
        // each rider with what they hold, and the deliveries still on their hands (assigned or on the road) so they can be cleared one by one
        $riders = User::where('role', 'rider')->orderBy('name')->get()->map(fn ($r) => (object) [
            'rider' => $r, 'c' => $r->custody(),
            'open' => $r->deliveries()->whereIn('status', ['assigned', 'out_for_delivery'])->with('customer')->orderBy('delivery_date')->orderBy('id')->get(),
        ]);

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
            // Shown so a missing key is obvious. The key itself is never displayed.
            'config' => ['has_key' => (string) config('services.speso_sms.api_key') !== '', 'sender' => (string) config('services.speso_sms.sender_id')],
        ]);
    }

    /** Sends one real SMS so the manager can check the Speso key, sender ID and balance. */
    public function testSms(Request $request, SmsService $sms)
    {
        $request->merge(['phone' => Phone::clean($request->input('phone'))]);
        $data = $request->validate([
            'phone' => ['required', 'regex:/^0\d{9}$/'],
            'message' => 'nullable|string|max:300',
        ], ['phone.regex' => 'Enter a 10-digit phone number, e.g. 0241234567.']);

        $result = $sms->send($data['phone'], ($data['message'] ?? null) ?: 'Drezwel Go: this is a test message. Your SMS is working.', null, 'test');

        return back()->with($result['success'] ? 'success' : 'error', $result['success']
            ? "Test SMS accepted by Speso for {$data['phone']}. Check that phone."
            : 'The test SMS did not send: ' . $result['message']);
    }
}
