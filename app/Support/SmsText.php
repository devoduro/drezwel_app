<?php

namespace App\Support;

/** How many SMS parts a message costs. Plain GSM text fits 160 characters per part, anything else (like the ₵ sign) only 70. */
class SmsText
{
    private const GSM = "@£\$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ ÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";
    private const GSM_EXT = '^{}\\[~]|€';

    /** @return array{length:int, unicode:bool, parts:int} */
    public static function measure(string $text): array
    {
        $units = 0;
        $unicode = false;
        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
            if (mb_strpos(self::GSM, $ch) !== false) {
                $units++;
            } elseif (mb_strpos(self::GSM_EXT, $ch) !== false) {
                $units += 2;
            } else {
                $unicode = true;
                $units++;
            }
        }
        if ($unicode) {
            $units = mb_strlen($text);
        }
        $single = $unicode ? 70 : 160;
        $multi = $unicode ? 67 : 153;
        $parts = $units === 0 ? 0 : ($units <= $single ? 1 : (int) ceil($units / $multi));

        return ['length' => $units, 'unicode' => $unicode, 'parts' => $parts];
    }

    /** Fills in {name}. Uses the first name, or "there" when we do not have one. */
    public static function personalise(string $message, ?string $name): string
    {
        $first = trim((string) strtok(trim((string) $name), ' '));

        return str_replace(['{name}', '{Name}'], $first !== '' ? $first : 'there', $message);
    }
}
