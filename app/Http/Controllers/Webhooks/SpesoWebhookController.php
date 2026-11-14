<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Services\SpesoPayments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/** Speso calls this when a mobile money payment completes or fails. Signed, not session-authenticated. */
class SpesoWebhookController extends Controller
{
    public function __invoke(Request $request, SpesoPayments $payments)
    {
        if (! $this->hasValidSignature($request)) {
            Log::warning('Speso webhook received with an invalid or missing signature');

            return response()->json(['success' => false, 'message' => 'Invalid signature'], 401);
        }

        $v = $request->validate([
            'order_id' => ['nullable', 'string'],
            'speso_reference' => ['required', 'string'],
            'status' => ['required', 'string', 'in:processing,completed,failed'],
            'amount' => ['required', 'numeric'],
        ]);

        $delivery = $v['order_id'] ? Delivery::where('payment_order_id', $v['order_id'])->first() : null;
        if (! $delivery) {
            Log::warning('Speso webhook for an unknown order', ['order_id' => $v['order_id'] ?? null]);

            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $result = $payments->apply($delivery, $v['status'], $v['amount'], $v['speso_reference'], $request->input('network'));
        if ($result === 'mismatch') {
            return response()->json(['success' => false, 'message' => 'Amount mismatch'], 422);
        }

        return response()->json(['success' => true]);
    }

    /** X-Speso-Signature: t=<unix time>,v1=<HMAC-SHA256 of "t.rawBody" with the webhook secret>; max 5 minutes old. */
    private function hasValidSignature(Request $request): bool
    {
        $secret = config('services.speso.webhook_secret');
        if (! $secret) {
            Log::error('SPESO_WEBHOOK_SECRET is not configured; rejecting webhook.');

            return false;
        }

        if (! preg_match('/^t=(\d+),v1=([0-9a-f]+)$/', (string) $request->header('X-Speso-Signature'), $m)) {
            return false;
        }
        [, $timestamp, $signature] = $m;
        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $timestamp . '.' . $request->getContent(), $secret), $signature);
    }
}
