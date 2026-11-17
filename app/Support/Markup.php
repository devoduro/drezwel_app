<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Str;

/** Turns the website text the manager writes (simple Markdown) into safe HTML, filling in the shop's own details. */
class Markup
{
    public static function render(?string $text): string
    {
        $text = strtr((string) $text, self::tokens());

        // Raw HTML is escaped and dangerous links (javascript:) are dropped, so a page can never run a script.
        return Str::markdown($text, ['html_input' => 'escape', 'allow_unsafe_links' => false]);
    }

    /** Text with the placeholders filled in and no formatting, for descriptions and search snippets. */
    public static function plain(?string $text): string
    {
        $html = self::render($text);

        return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html))));
    }

    /** Split FAQ text written as "### Question" followed by the answer. */
    public static function faqs(?string $text): array
    {
        $out = [];
        foreach (preg_split('/^###\s+/m', "\n" . trim((string) $text)) as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }
            [$q, $a] = array_pad(preg_split('/\R/', $block, 2), 2, '');
            if (trim($q) !== '') {
                $out[] = ['q' => trim($q), 'a' => trim($a)];
            }
        }

        return $out;
    }

    private static function tokens(): array
    {
        $S = fn ($k) => (string) Setting::get($k);
        $phones = collect(Setting::phones())->map(fn ($p) => $p[0])->implode(' / ');

        return [
            '{business}' => $S('business_name') ?: 'Drezwel',
            '{phone}' => $phones ?: $S('phone'),
            '{email}' => $S('email'),
            '{address}' => $S('address'),
            '{hours}' => $S('hours'),
            '{website}' => $S('website'),
            '{shop}' => route('shop.index'),
            '{contact}' => route('site.contact'),
            '{track}' => route('track'),
            '{delivery}' => route('site.page', 'delivery-info'),
            '{returns}' => route('site.page', 'return-policy'),
        ];
    }
}
