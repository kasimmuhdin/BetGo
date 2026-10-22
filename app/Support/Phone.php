<?php

namespace App\Support;

/**
 * Ethiopian mobile numbers. Everything is stored as 09XXXXXXXX or 07XXXXXXXX,
 * which is also the format Chapa expects.
 *
 *  0911 234 567, +251911234567, 251911234567, 00251911234567, 911234567
 *  all become 0911234567.
 */
class Phone
{
    public static function normalize(?string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $raw);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00251')) {
            $digits = substr($digits, 5);
        } elseif (str_starts_with($digits, '251')) {
            $digits = substr($digits, 3);
        } elseif (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return preg_match('/^[79]\d{8}$/', $digits) ? '0' . $digits : null;
    }
}
