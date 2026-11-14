<?php

namespace App\Services;

use App\Models\SmsLog;
use App\Support\Phone;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Sends SMS through the Speso business API and keeps a log of every attempt. */
class SmsService
{
    public function send(string $phone, string $message, ?int $deliveryId = null, string $purpose = 'general', ?int $campaignId = null): array
    {
        $phone = Phone::clean($phone) ?? $phone;
        $key = (string) config('services.speso_sms.api_key');
        $url = rtrim((string) (config('services.speso_sms.base_url') ?: 'https://business.speso.co/api/v1'), '/');

        if ($key === '') {
            Log::warning('SMS not sent: SPESO_API_KEY missing', ['phone' => $phone]);
            $result = ['success' => false, 'message' => 'Speso API key is not configured (SPESO_API_KEY).'];
        } else {
            try {
                $response = Http::withToken($key)->acceptJson()->timeout(15)->post($url . '/messages/send', [
                    'sender_id' => config('services.speso_sms.sender_id'),
                    'phone' => $phone,
                    'message' => $message,
                ]);
                $body = $response->json() ?? [];
                $ok = $response->successful() && ($body['success'] ?? true) !== false;
                $result = [
                    'success' => $ok,
                    'message' => $body['message'] ?? $body['error'] ?? ($ok ? 'Submitted' : 'SMS sending failed'),
                ];
                if (! $ok) {
                    Log::error('Speso SMS failed', ['phone' => $phone, 'status' => $response->status()]);
                }
            } catch (\Throwable $e) {
                Log::error('Speso SMS exception', ['error' => $e->getMessage()]);
                $result = ['success' => false, 'message' => $e->getMessage()];
            }
        }

        SmsLog::create([
            'delivery_id' => $deliveryId,
            'campaign_id' => $campaignId,
            'phone' => $phone,
            'purpose' => $purpose,
            // The one-time code itself is never stored.
            'message' => in_array($purpose, ['delivery_code', 'customer_login', 'password_reset'], true) ? '[one-time code]' : $message,
            'success' => $result['success'],
            'response' => mb_substr((string) $result['message'], 0, 250),
        ]);

        return $result;
    }
}
