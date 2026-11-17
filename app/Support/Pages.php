<?php

namespace App\Support;

use App\Models\Setting;

/** The website's text pages: which exist, where their wording is kept, and the suggested draft used until the manager writes their own. */
class Pages
{
    /** slug => [title, setting key, short description for the manager] */
    public const ALL = [
        'delivery-info' => ['Delivery & payment', 'delivery_text', 'How orders are delivered, delivery areas and fees, and ways to pay.'],
        'return-policy' => ['Returns & exchanges', 'policy_returns', 'What customers can return or exchange, and how refunds work.'],
        'terms' => ['Terms & conditions', 'policy_terms', 'The rules for ordering from the shop.'],
        'privacy-policy' => ['Privacy policy', 'policy_privacy', 'What customer information is collected and how it is used.'],
        'faqs' => ['FAQs', 'faq_text', 'Common questions and answers. Write each question as a "### Question" line, with the answer beneath it.'],
        'about' => ['About us', 'about_text', 'Who you are. Shown on the About us page.'],
        'shop-intro' => ['Shop introduction', 'shop_intro_text', 'The text at the bottom of the home page and the shop page that helps customers (and Google) find you.'],
    ];

    /** The wording to show: the manager's own text, or the suggested draft when they have not written any. */
    public static function text(string $key): string
    {
        $saved = trim((string) Setting::get($key));

        return $saved !== '' ? $saved : self::draft($key);
    }

    public static function draft(string $key): string
    {
        return trim((string) config("pages.{$key}"));
    }

    public static function usesDraft(string $key): bool
    {
        return trim((string) Setting::get($key)) === '';
    }

    public static function find(string $slug): ?array
    {
        return self::ALL[$slug] ?? null;
    }

    /** Public address of a page. */
    public static function url(string $slug): string
    {
        return match ($slug) {
            'about' => route('site.about'),
            'shop-intro' => route('home'),
            default => route('site.page', $slug),
        };
    }
}
