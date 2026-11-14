<?php

namespace App\Console\Commands;

use App\Models\Delivery;
use App\Services\SpesoPayments;
use Illuminate\Console\Command;

class ReconcileSpesoPayments extends Command
{
    protected $signature = 'speso:reconcile {--minutes=1 : Only look at prompts at least this old} {--abandon-after= : Cancel unpaid orders older than this many minutes} {--limit=200}';

    protected $description = 'Settle online orders whose Speso callback never arrived, and cancel ones that were never paid';

    public function handle(SpesoPayments $payments): int
    {
        $abandonAfter = (int) ($this->option('abandon-after') ?: config('services.speso.abandon_after', 60));
        $stats = ['paid' => 0, 'failed' => 0, 'awaiting' => 0, 'abandoned' => 0, 'unreachable' => 0];

        Delivery::where('payment_method', 'speso')->whereIn('payment_status', ['awaiting', 'failed'])->where('status', 'pending')
            ->where('payment_requested_at', '<=', now()->subMinutes((int) $this->option('minutes')))
            ->orderBy('id')->limit((int) $this->option('limit'))->get()
            ->each(function (Delivery $d) use ($payments, $abandonAfter, &$stats) {
                $state = $payments->verify($d);
                if (isset($stats[$state])) {
                    $stats[$state]++;
                }
                // Only give up on an order Speso has told us is unpaid; never when we could not ask.
                if (in_array($state, ['awaiting', 'failed'], true) && $d->payment_requested_at->lte(now()->subMinutes($abandonAfter)) && $payments->abandon($d)) {
                    $stats['abandoned']++;
                }
            });

        $this->info(collect($stats)->map(fn ($n, $k) => "{$k}: {$n}")->implode(', '));

        return self::SUCCESS;
    }
}
