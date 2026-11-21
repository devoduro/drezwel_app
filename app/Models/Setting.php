<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/** Business details used on the online store and on invoices. */
class Setting extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    protected $guarded = [];

    public const DEFAULTS = [
        'business_name' => 'Drezwel',
        'tagline' => 'Dealers in fashion and fashion accessories',
        'phone' => '0200680701/0203264474',
        'helpline' => '',
        'whatsapp' => '233200680701',
        'whatsapp_channel' => 'https://whatsapp.com/channel/0029VaiunvI23n3mU5tUFs2W',
        'facebook' => 'https://www.facebook.com/drezwel',
        'instagram' => 'https://www.instagram.com/drezwel_gh',
        'x' => 'https://x.com/drezwel',
        'youtube' => 'https://youtube.com/@drezwel',
        'tiktok' => 'https://www.tiktok.com/@drezwel',
        'hours' => '',
        'email' => 'drezwelgh@gmail.com',
        'address' => 'Loc. Beach Road, Lascala Bus Stop, HVP9 + 8VF Accra',
        'website' => 'https://drezwell.com',
        'default_delivery_fee' => '0',
        'express_station_fee' => '30',
        'invoice_note' => 'Thank you for choosing Drezwel',
        'invoice_terms' => 'Goods sold are not returnable',
        'store_open' => '1',
        'hero_image' => '',
    ];

    public static function all_(): array
    {
        return Cache::remember('settings.all', 3600, fn () => static::query()->pluck('value', 'key')->all());
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = static::all_();

        // A saved value (even an empty one) wins; then the built-in default; then the website wording in config/site.php.
        return $all[$key] ?? self::DEFAULTS[$key] ?? config("site.{$key}") ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings.all');
    }

    /** Every phone number listed in Settings (separated by / or comma), as [display, digits]. */
    public static function phones(): array
    {
        $out = [];
        foreach (array_filter([static::get('phone'), static::get('helpline')]) as $list) {
            foreach (preg_split('/[\/,;]/', (string) $list) as $n) {
                $n = trim($n);
                $digits = preg_replace('/\D/', '', $n);
                if ($digits !== '' && ! collect($out)->contains(fn ($o) => $o[1] === $digits)) {
                    $out[] = [$n, $digits];
                }
            }
        }

        return $out;
    }

    /** Phone digits for tel: and wa.me links. */
    public static function phoneLink(): ?string
    {
        // The phone setting may list several numbers ("0200680701/0203264474"): dial the first.
        $first = preg_split('/[\/,;]|\s{2,}/', (string) static::get('phone'))[0] ?? '';
        $p = preg_replace('/\D/', '', $first);

        return $p === '' ? null : $p;
    }
}
