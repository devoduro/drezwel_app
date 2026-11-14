<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SmsCampaign;
use App\Models\User;
use App\Services\SmsAudience;
use App\Services\SmsService;
use App\Support\SmsText;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Bulk SMS: text a group of customers or riders, hand-picked people, or typed numbers. */
class BulkSmsController extends Controller
{
    /** One send is limited, so a slow or failing gateway cannot freeze the page for long. */
    public const MAX_RECIPIENTS = 500;
    /** Stop after this many failures in a row (wrong key, unapproved sender ID, no credit...). */
    private const STOP_AFTER_FAILURES = 5;

    public function __construct(private SmsAudience $audience, private SmsService $sms) {}

    public function index()
    {
        return view('manager.bulk-sms', [
            'groups' => SmsAudience::GROUPS,
            'customers' => Customer::where('phone', '!=', \App\Services\SalesService::WALK_IN_PHONE)->orderBy('name')->limit(1500)->get(['id', 'name', 'phone']),
            'riders' => User::where('role', 'rider')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'phone']),
            'campaigns' => SmsCampaign::with('user')->latest('id')->limit(15)->get(),
            'max' => self::MAX_RECIPIENTS,
            'hasKey' => (string) config('services.speso_sms.api_key') !== '',
        ]);
    }

    /** JSON for the live "this will go to N people" line. */
    public function preview(Request $request)
    {
        $request->validate(['audience' => ['required', Rule::in(array_keys(SmsAudience::GROUPS))]]);
        $r = $this->audience->resolve($request->input('audience'), $request->only(['customer_ids', 'rider_ids', 'numbers']));

        return response()->json([
            'count' => $r['recipients']->count(),
            'sample' => $r['recipients']->take(4)->map(fn ($p) => $p['name'] ?: $p['phone'])->all(),
            'skipped' => array_slice($r['skipped'], 0, 8),
            'skipped_count' => count($r['skipped']),
            'over_limit' => $r['recipients']->count() > self::MAX_RECIPIENTS,
        ]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'audience' => ['required', Rule::in(array_keys(SmsAudience::GROUPS))],
            'message' => 'required|string|min:3|max:640',
            'customer_ids' => 'nullable|array', 'customer_ids.*' => 'integer',
            'rider_ids' => 'nullable|array', 'rider_ids.*' => 'integer',
            'numbers' => 'nullable|string|max:20000',
        ]);

        $r = $this->audience->resolve($data['audience'], $request->only(['customer_ids', 'rider_ids', 'numbers']));
        $recipients = $r['recipients'];

        if ($recipients->isEmpty()) {
            return back()->withInput()->with('error', 'There is nobody to send to. Check the audience' . (count($r['skipped']) ? ' (' . count($r['skipped']) . ' had no usable phone number)' : '') . '.');
        }
        if ($recipients->count() > self::MAX_RECIPIENTS) {
            return back()->withInput()->with('error', 'That is ' . $recipients->count() . ' people. Please send to at most ' . self::MAX_RECIPIENTS . ' at a time (pick a smaller group, or send in two goes).');
        }

        set_time_limit(0); // a few hundred messages can take a while

        $campaign = SmsCampaign::create([
            'user_id' => $request->user()->id, 'audience' => $data['audience'], 'audience_label' => $r['label'],
            'message' => $data['message'], 'recipients' => $recipients->count(), 'status' => 'sending',
        ]);

        $sent = $failed = $streak = 0;
        $aborted = null;
        foreach ($recipients as $p) {
            $result = $this->sms->send($p['phone'], SmsText::personalise($data['message'], $p['name']), null, 'bulk', $campaign->id);
            if ($result['success']) {
                $sent++;
                $streak = 0;
            } else {
                $failed++;
                if (++$streak >= self::STOP_AFTER_FAILURES) {
                    $aborted = 'Stopped after ' . self::STOP_AFTER_FAILURES . ' failures in a row: ' . $result['message'];
                    break;
                }
            }
        }

        $campaign->update(['sent' => $sent, 'failed' => $failed, 'status' => $aborted ? 'aborted' : 'done', 'note' => $aborted ? mb_substr($aborted, 0, 250) : null]);

        $flash = $aborted
            ? ['error', "Sending stopped early: {$sent} sent, {$failed} failed, " . $campaign->not_sent . " not attempted. {$aborted}"]
            : ($failed
                ? ['error', "Done, but {$failed} of {$recipients->count()} messages failed. See the results below."]
                : ['success', "Sent to {$sent} " . ($sent === 1 ? 'person' : 'people') . '.']);

        return redirect()->route('manager.bulk.show', $campaign)->with($flash[0], $flash[1]);
    }

    public function show(SmsCampaign $campaign)
    {
        return view('manager.bulk-sms-show', [
            'campaign' => $campaign->load('user'),
            'logs' => $campaign->logs()->orderBy('id')->paginate(50),
            'parts' => SmsText::measure($campaign->message)['parts'],
        ]);
    }
}
