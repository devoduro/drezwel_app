<?php

namespace App\Support;

use App\Models\Delivery;
use App\Models\Setting;

/**
 * The texts customers get about their orders, all in one place so they stay short and read the same:
 * "Thank you John! ... Contact us: 0200680701". The contact numbers come from Settings.
 */
class CustomerSms
{
    /** The main phone number (the first one in Settings). */
    public static function contact(): string
    {
        return Setting::phones()[0][0] ?? '0200680701';
    }

    /** Every phone number in Settings, for the "not received?" text. */
    public static function allContacts(): string
    {
        return collect(Setting::phones())->map(fn ($p) => $p[0])->implode(' / ') ?: '0200680701 / 0203264474';
    }

    /** An order was placed and will be paid for on delivery (or on pick up). */
    public static function orderReceived(Delivery $d): string
    {
        return "Thank you {$d->customer->name}! Order {$d->reference} for GH₵ " . number_format($d->amount_due, 2) . ' received. '
            . ($d->service === 'pickup' ? 'We will call you when it is ready for pick up. ' : '')
            . 'Contact us: ' . self::contact();
    }

    /** Money was received for an order. */
    public static function paymentReceived(Delivery $d, float $amount): string
    {
        return "Thank you {$d->customer->name}! Payment of GH₵ " . number_format($amount, 2) . " received for order {$d->reference}. Contact us: " . self::contact();
    }

    /** A rider has been given the order. */
    public static function assigned(Delivery $d): string
    {
        $rider = $d->rider;

        return "Hello {$d->customer->name}! Order {$d->reference} is with rider {$rider->name}" . ($rider->phone ? " ({$rider->phone})" : '') . '. Contact us: ' . self::contact();
    }

    /** The order was delivered. Both numbers, so a customer who did not get it can reach us. */
    public static function delivered(Delivery $d): string
    {
        return "Thank you {$d->customer->name}! Order {$d->reference} has been delivered. Not received? Contact us: " . self::allContacts();
    }
}
