<?php

namespace App\Support;

/** How a customer approves a pending mobile money payment by hand if the prompt never shows. */
class MomoApproval
{
    public const NETWORKS = ['MTN' => 'MTN Mobile Money', 'VOD' => 'Telecel (Vodafone) Cash', 'ATL' => 'AirtelTigo Money'];

    private const CODES = ['MTN' => '*170#', 'VOD' => '*110#', 'ATL' => '*110#'];

    public static function code(?string $network): string
    {
        return self::CODES[strtoupper((string) $network)] ?? '*170#';
    }

    public static function hint(?string $network): string
    {
        return 'If the prompt does not appear, dial ' . self::code($network) . ', choose My Wallet > My Approvals, and approve the payment.';
    }
}
