<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Every state change of a delivery goes through here so the manager and rider screens
 * follow exactly the same rules:
 *
 *   pending -> assigned -> out_for_delivery -> delivered
 *                                           -> undelivered -> (manager) pending again
 */
class DeliveryWorkflow
{
    public function __construct(private SmsService $sms, private StockService $stock) {}

    /** Why this order cannot be deleted, or null when it can. */
    public function deletionBlocker(Delivery $d): ?string
    {
        if ($d->payment_status === 'paid') {
            return "{$d->reference} was paid online through Speso, so it cannot be deleted. Cancel it instead and refund the customer.";
        }
        if ($d->status === 'out_for_delivery') {
            return "A rider is out delivering {$d->reference}. Mark it undelivered or cancel it before deleting.";
        }

        return null;
    }

    /** Permanently remove an order: whatever it still has out of stock goes back; payments, items and history go with it. */
    public function destroy(Delivery $d, User $by): void
    {
        DB::transaction(function () use ($d, $by) {
            $this->stock->restoreDeletedDelivery($d, $by);
            $d->delete();
        });
    }

    public function assign(Delivery $d, User $rider, User $by): Delivery
    {
        $this->ensure($rider->isRider() && $rider->is_active, 'rider', 'That rider is not active.');

        return DB::transaction(function () use ($d, $rider, $by) {
            $d = Delivery::lockForUpdate()->findOrFail($d->id);
            $this->ensure(in_array($d->status, ['pending', 'assigned'], true), 'status', 'Only pending or assigned deliveries can be (re)assigned.');
            $d->update(['rider_id' => $rider->id, 'status' => 'assigned', 'assigned_at' => now()]);
            $d->logEvent('assigned', "Assigned to {$rider->name}", $by->id);
            $this->notifyAssigned($d->load(['customer', 'rider']));

            return $d;
        });
    }

    /** A rider picks an unassigned delivery. The conditional UPDATE means two riders can't both get it. */
    public function claim(Delivery $d, User $rider): bool
    {
        $won = Delivery::where('id', $d->id)->where('status', 'pending')->whereNull('rider_id')
            ->where('delivery_mode', 'rider')
            ->update(['rider_id' => $rider->id, 'status' => 'assigned', 'assigned_at' => now(), 'updated_at' => now()]) === 1;

        if ($won) {
            $d->refresh()->logEvent('assigned', "{$rider->name} picked this delivery", $rider->id);
            $this->notifyAssigned($d->load(['customer', 'rider']));
        }

        return $won;
    }

    /** Rider hands a delivery back before starting it. */
    public function release(Delivery $d, User $rider): void
    {
        $this->ensure($d->rider_id === $rider->id && $d->status === 'assigned', 'status', 'This delivery cannot be released.');
        $d->update(['rider_id' => null, 'status' => 'pending', 'assigned_at' => null]);
        $d->logEvent('released', "{$rider->name} released the delivery", $rider->id);
    }

    /** Rider sets off: the customer receives a 4-digit code by SMS. */
    public function start(Delivery $d, User $rider): array
    {
        $this->ensure($d->rider_id === $rider->id && $d->status === 'assigned', 'status', 'This delivery cannot be started.');

        $d->update(['status' => 'out_for_delivery', 'started_at' => now()]);
        $d->logEvent('started', "{$rider->name} is on the way", $rider->id);

        return $this->sendCode($d, $rider);
    }

    /** Generates a fresh code and texts it to the customer. Used on start and for "resend". */
    public function sendCode(Delivery $d, User $by): array
    {
        $this->ensure($d->status === 'out_for_delivery', 'status', 'A code can only be sent while the delivery is out.');

        $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        $d->update(['code_hash' => $this->hashCode($d, $code), 'code_attempts' => 0, 'code_sent_at' => now()]);

        $d->loadMissing(['customer', 'rider']);
        $rider = $d->rider;
        $msg = "Drezwel Go: Your order {$d->reference} is on the way with rider {$rider->name}"
            . ($rider->phone ? " ({$rider->phone})" : '') . ". "
            . "Your delivery code is {$code}. Give it to the rider ONLY after you have received your order.";

        $result = $this->sms->send($d->customer->phone, $msg, $d->id, 'delivery_code');
        $d->logEvent($result['success'] ? 'code_sent' : 'code_failed', $result['success'] ? 'Delivery code sent to customer' : 'Delivery code SMS failed', $by->id);

        return $result;
    }

    /** Rider types the customer's code to confirm receipt. */
    public function confirm(Delivery $d, User $rider, string $code, ?int $itemsKept, ?float $cash): Delivery
    {
        $left = null;
        $result = DB::transaction(function () use ($d, $rider, $code, $itemsKept, $cash, &$left) {
            $d = Delivery::lockForUpdate()->findOrFail($d->id);
            $this->ensure($d->rider_id === $rider->id && $d->status === 'out_for_delivery', 'code', 'This delivery is not out for delivery.');
            $this->ensure($d->code_hash !== null, 'code', 'No code has been sent yet. Tap "Resend code" first.');
            $this->ensure($d->code_attempts < Delivery::MAX_CODE_ATTEMPTS, 'code', 'Too many wrong codes. Ask the manager to resend a new code.');

            if (! hash_equals($d->code_hash, $this->hashCode($d, $code))) {
                $d->increment('code_attempts');
                $d->logEvent('code_wrong', 'Wrong code entered', $rider->id);
                $left = Delivery::MAX_CODE_ATTEMPTS - $d->code_attempts;

                return null; // commit the attempt counter, then report the error below
            }

            $this->complete($d, $rider, $itemsKept, $cash, 'Customer confirmed with delivery code');

            return $d;
        });

        if ($result === null) {
            throw ValidationException::withMessages(['code' => $left > 0 ? "Wrong code. {$left} attempt(s) left." : 'Wrong code. No attempts left - ask the manager to resend a code.']);
        }

        return $result;
    }

    /** Manager confirms without a code (customer could not get the SMS, etc.). Always logged. */
    public function overrideConfirm(Delivery $d, User $manager, string $reason, ?int $itemsKept, ?float $cash): Delivery
    {
        return DB::transaction(function () use ($d, $manager, $reason, $itemsKept, $cash) {
            $d = Delivery::lockForUpdate()->findOrFail($d->id);
            $this->ensure(in_array($d->status, ['assigned', 'out_for_delivery', 'undelivered'], true), 'status', 'This delivery cannot be confirmed.');
            $d->confirmed_by_manager = true;
            $this->complete($d, $manager, $itemsKept, $cash, "Confirmed by manager without code: {$reason}", $d->rider_id);

            return $d;
        });
    }

    public function markUndelivered(Delivery $d, User $by, string $reason): void
    {
        $this->ensure(in_array($d->status, ['assigned', 'out_for_delivery'], true), 'status', 'This delivery cannot be marked undelivered.');
        $this->ensure($by->isManager() || $d->rider_id === $by->id, 'status', 'Not your delivery.');

        $d->update(['status' => 'undelivered', 'undelivered_reason' => $reason, 'code_hash' => null]);
        $d->logEvent('undelivered', $reason, $by->id);
    }

    /** Manager puts an undelivered/cancelled delivery back in the queue (optionally for a new date). */
    public function reopen(Delivery $d, User $by, ?string $date = null): void
    {
        $this->ensure(in_array($d->status, ['undelivered', 'cancelled'], true), 'status', 'Only undelivered or cancelled deliveries can be reopened.');
        DB::transaction(function () use ($d, $by, $date) {
        if ($d->status === 'cancelled') {
            $this->stock->reserveDelivery($d, $by); // goods were put back on cancel; take them off again
        }
        $d->update([
            'status' => 'pending', 'rider_id' => null, 'assigned_at' => null, 'started_at' => null,
            'undelivered_reason' => null, 'code_hash' => null, 'code_attempts' => 0,
            'delivery_date' => $date ?: $d->delivery_date,
        ]);
        $d->logEvent('reopened', 'Back in the queue', $by->id);
        });
    }

    public function cancel(Delivery $d, User $by): void
    {
        $this->ensure(! in_array($d->status, ['delivered', 'cancelled'], true), 'status', 'Delivered or cancelled deliveries cannot be cancelled.');
        DB::transaction(function () use ($d, $by) {
            $this->stock->releaseDelivery($d, $by);
            $d->update(['status' => 'cancelled', 'code_hash' => null]);
            $d->logEvent('cancelled', 'Cancelled by manager', $by->id);
        });
    }

    /** Manager records money a customer paid (e.g. paying off a balance by MoMo). */
    public function recordPayment(Delivery $d, User $by, float $amount, string $method, ?string $notes = null): Payment
    {
        $this->ensure($amount > 0, 'amount', 'Amount must be more than zero.');
        $this->ensure($amount <= $d->balance + 0.001, 'amount', 'That is more than the balance of GH₵ ' . number_format($d->balance, 2) . '.');

        $p = $d->payments()->create(['amount' => $amount, 'method' => $method, 'notes' => $notes, 'recorded_by' => $by->id, 'paid_at' => now()]);
        $d->recalcPaid();
        $d->logEvent('payment', 'Payment of GH₵ ' . number_format($amount, 2) . " ({$method})", $by->id);

        return $p;
    }

    // ----------------------------------------------------------------------------------------

    private function complete(Delivery $d, User $actor, ?int $itemsKept, ?float $cash, string $note, ?int $riderId = null): void
    {
        $riderId ??= $actor->id;
        $d->fill([
            'status' => 'delivered',
            'delivered_at' => now(),
            'code_hash' => null,
            'items_kept' => $itemsKept === null ? $d->items_sent : max(0, min($itemsKept, $d->items_sent)),
        ])->save();

        $cash = $cash === null ? null : min(max($cash, 0), $d->balance);
        if ($cash > 0) {
            $d->payments()->create(['amount' => $cash, 'method' => 'cash', 'rider_id' => $riderId, 'recorded_by' => $actor->id, 'paid_at' => now(), 'notes' => 'Collected on delivery']);
            $d->recalcPaid();
        }

        $d->logEvent('delivered', $note . ($cash > 0 ? ' - cash collected GH₵ ' . number_format($cash, 2) : ''), $actor->id);

        $d->loadMissing('customer');
        $this->sms->send($d->customer->phone, "Drezwel Go: Thank you! Your order {$d->reference} has been delivered."
            . ($d->balance > 0 ? ' Balance due: GH₵ ' . number_format($d->balance, 2) . '.' : ''), $d->id, 'delivered');
    }

    private function notifyAssigned(Delivery $d): void
    {
        $this->sms->send($d->customer->phone,
            "Drezwel Go: Your order {$d->reference} has been assigned to rider {$d->rider->name}" . ($d->rider->phone ? " ({$d->rider->phone})" : '')
            . '. Track it at ' . route('track', ['ref' => $d->reference, 'phone' => $d->customer->phone]),
            $d->id, 'assigned');
    }

    private function hashCode(Delivery $d, string $code): string
    {
        return hash_hmac('sha256', $d->id . '|' . $code, (string) config('app.key'));
    }

    private function ensure(bool $ok, string $field, string $message): void
    {
        if (! $ok) {
            throw ValidationException::withMessages([$field => $message]);
        }
    }
}
