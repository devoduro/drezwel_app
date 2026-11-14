<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/** Thin wrapper over Speso's Collections API (mobile money prompts). */
class SpesoClient
{
    public function __construct(private readonly string $baseUrl, private readonly ?string $apiKey) {}

    public static function make(): self
    {
        return new self(rtrim((string) config('services.speso.base_url'), '/'), config('services.speso.api_key'));
    }

    public static function configured(): bool
    {
        return filled(config('services.speso.api_key'));
    }

    /** Ask the customer's handset to approve a payment. */
    public function requestCollection(string $orderId, string $mobile, string $network, float $amount, ?string $description = null): array
    {
        $callback = config('services.speso.callback_url');
        // Speso only accepts a public HTTPS callback; on localhost we rely on status polling instead.
        if (! $callback || ! str_starts_with($callback, 'https://')) {
            $callback = null;
        }

        return $this->handle($this->client()->post($this->baseUrl . '/collections', array_filter([
            'order_id' => $orderId, 'mobile' => $mobile, 'network' => $network, 'amount' => $amount,
            'description' => $description, 'callback_url' => $callback,
        ], fn ($v) => ! is_null($v))));
    }

    /** Status of a collection by our order_id or Speso's reference. */
    public function getCollection(string $reference): array
    {
        return $this->handle($this->client()->get($this->baseUrl . '/collections/' . rawurlencode($reference)));
    }

    private function client()
    {
        if (! $this->apiKey) {
            throw new RuntimeException('Speso API key is not configured. Set SPESO_API_KEY in .env.');
        }

        return Http::withToken($this->apiKey)->acceptJson()->timeout(8);
    }

    private function handle(Response $response): array
    {
        $body = $response->json() ?? [];
        if ($response->failed() || ($body['success'] ?? true) === false) {
            Log::error('Speso API request failed', ['status' => $response->status(), 'body' => $response->body()]);

            throw new RuntimeException($body['message'] ?? $body['error'] ?? 'Speso API request failed.');
        }

        return $body;
    }
}
