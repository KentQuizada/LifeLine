<?php

namespace App\Support;

/**
 * Philippine mobile numbers are stored in one format: 09XXXXXXXXX (11 digits).
 * The UNIQUE index on users.mobile_number only catches exact matches, so every
 * number is normalized before validation, saving and login.
 */
class MobileNumber
{
    public static function normalize(?string $number): ?string
    {
        $digits = preg_replace('/[\s\-().]/', '', (string) $number);

        if (preg_match('/^(?:\+?63|0)?(9\d{9})$/', $digits, $match)) {
            return '0'.$match[1];
        }

        return null;
    }
}
