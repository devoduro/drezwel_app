<?php

namespace App\Services;

use App\Models\Delivery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Online (Speso) payment for website orders. Speso is the authority on whether money moved:
 * the webhook, the status poll on the order page, the manager's "Verify" button and the
 * speso:reconcile command all funnel into apply(), which is idempotent and row-locked so a
 * payment is recorded exactly once however many of them fire together.
 */
class SpesoPayments
{
    public function __construct(private StockService $stock, private SalesService $sales) {}

    /** Send a MoMo prompt for the order. Rethrows when Speso is unreachable or refuses. */
    public function request(Delivery $d, string $mobile, string $network): void
    {
        $attempt = $d->payment_attempts + 1;
        $orderId = $d->reference . '-' . $attempt;

        try {
            SpesoClient::make()->requestCollection($orderId, $mobile, $network, $d->balance, "Drezwel order {$d->reference}");
        } catch (Throwable $e) {
            $d->update(['payment_method' => 'speso', 'payment_status' => 'failed', 'payment_network' => $network, 'payment_mobile' => $mobile]);
            $d->logEvent('payment', 'Could not send the MoMo prompt: ' . $e->getMessage());

            throw $e;
        }

        $d->update([
            'payment_method' => 'speso', 'payment_status' => 'awaiting', 'payment_order_id' => $orderId, 'payment_attempts' => $attempt,
            'payment_network' => $network, 'payment_mobile' => $mobile, 'payment_requested_at' => now(),
        ]);
        $d->logEvent('payment', "MoMo prompt sent to {$mobile} ({$network}), attempt {$attempt}");
    }

    /**
     * Ask Speso about the order and act on the answer.
     *
     * @return string paid|failed|awaiting|mismatch|unreachable|none
     */
    public function verify(Delivery $d): string
    {
        $d->refresh();
        if ($d->payment_status === 'paid') {
            return 'paid';
        }
        if (! $d->payment_order_id) {
            return 'none';
        }

        try {
            $result = SpesoClient::make()->getCollection($d->payment_order_id);
        } catch (Throwable $e) {
            report($e);

            return 'unreachable';
        }

        return $this->apply($d, (string) ($result['status'] ?? ''), $result['amount'] ?? null, $result['speso_reference'] ?? null, $result['network'] ?? null);
    }

    /**
     * Record what Speso says: completed, failed or still processing.
     *
     * @return string paid|failed|awaiting|mismatch
     */
    public function apply(Delivery $d, string $status, mixed $amount, ?string $reference, ?string $network): string
    {
        $d->refresh();

        if ($status === 'completed') {
            if ($d->payment_status !== 'paid' && $amount !== null && bccomp((string) $amount, number_format($d->balance, 2, '.', ''), 2) !== 0) {
                Log::warning('Speso amount mismatch; left for manual review', ['delivery' => $d->reference, 'expected' => $d->balance, 'received' => $amount]);

                return 'mismatch';
            }
            $this->settle($d, $reference, $network);

            return 'paid';
        }

        if ($status === 'failed') {
            DB::transaction(function () use ($d) {
                $row = Delivery::lockForUpdate()->find($d->id);
                if ($row->payment_status !== 'awaiting') {
                    return;
                }
                $row->update(['payment_status' => 'failed']);
                $row->logEvent('payment', 'Payment was not completed (declined, cancelled or timed out)');
            });

            return $d->fresh()->payment_status === 'paid' ? 'paid' : 'failed';
        }

        return 'awaiting';
    }

    /** Record the money once. Returns true only for the call that actually recorded it. */
    private function settle(Delivery $d, ?string $reference, ?string $network): bool
    {
        $first = DB::transaction(function () use ($d, $reference, $network) {
            $row = Delivery::lockForUpdate()->find($d->id);
            if ($row->payment_status === 'paid') {
                return false;
            }

            $amount = $row->balance;
            if ($amount > 0) {
                $row->payments()->create(['amount' => $amount, 'method' => 'momo', 'paid_at' => now(), 'notes' => 'Paid online via Speso' . ($reference ? " ({$reference})" : '')]);
            }
            $row->payment_status = 'paid';
            $row->payment_reference = $reference ?: $row->payment_reference;
            $row->payment_network = $network && strlen($network) <= 4 ? $network : $row->payment_network;
            $row->save();
            $row->recalcPaid();
            $row->logEvent('payment', 'Paid online via Speso' . ($reference ? " ({$reference})" : '') . ' · GH₵ ' . number_format($amount, 2)
                . ($row->status === 'cancelled' ? ' · ORDER WAS ALREADY CANCELLED: refund this customer' : ''));

            return true;
        });

        if ($first) {
            $d->refresh();
            if ($d->status !== 'cancelled') {
                $this->sales->notifyOnlineOrder($d->load('customer'));
            }
        }

        return $first;
    }

    /** Give up on an unpaid order: cancel it and put the goods back on the shelf. */
    public function abandon(Delivery $d): bool
    {
        return DB::transaction(function () use ($d) {
            $row = Delivery::lockForUpdate()->find($d->id);
            if ($row->payment_status === 'paid' || $row->status !== 'pending') {
                return false;
            }
            $row->update(['status' => 'cancelled', 'payment_status' => 'abandoned', 'code_hash' => null]);
            $row->logEvent('cancelled', 'Cancelled automatically: online payment was not completed in time');
            $this->stock->releaseDelivery($row, null);

            return true;
        });
    }

    /** Customer gives up on paying online and will pay the rider instead. */
    public function switchToCashOnDelivery(Delivery $d): bool
    {
        $done = DB::transaction(function () use ($d) {
            $row = Delivery::lockForUpdate()->find($d->id);
            if (! in_array($row->payment_status, ['failed', 'awaiting'], true) || $row->status !== 'pending' || $row->pay_now_only) {
                return false;
            }
            $row->update(['payment_method' => 'cod', 'payment_status' => null]);
            $row->logEvent('payment', 'Customer chose pay on delivery instead of paying online');

            return true;
        });
        if ($done) {
            $this->sales->notifyOnlineOrder($d->refresh()->load('customer'));
        }

        return $done;
    }
}
