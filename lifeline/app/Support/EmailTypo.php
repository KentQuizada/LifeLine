<?php

namespace App\Support;

/**
 * Catches common misspellings of big email providers (gmail.con, gmial.com, yahoo.con...).
 * A verification email sent to such an address never arrives, and the person is stuck on
 * "Check your email" (found online on 2026-11-23: a sign-up as ...@gmail.con).
 */
final class EmailTypo
{
    private const DOMAINS = [
        'gmail.com' => ['gmail.con', 'gmail.co', 'gmail.cm', 'gmail.om', 'gmail.comm', 'gmail.coom', 'gmail.cpm',
            'gmial.com', 'gmal.com', 'gmaill.com', 'gamil.com', 'gnail.com', 'gmai.com', 'gmali.com', 'gimail.com'],
        'yahoo.com' => ['yahoo.con', 'yahoo.cm', 'yahooo.com', 'yaho.com', 'yhoo.com', 'yahho.com'],
        'hotmail.com' => ['hotmail.con', 'hotmal.com', 'hotmial.com', 'hotmai.com', 'hotmil.com'],
        'outlook.com' => ['outlook.con', 'outlok.com', 'outllok.com', 'outloo.com'],
        'icloud.com' => ['icloud.con', 'iclod.com', 'icoud.com'],
    ];

    /** The corrected address, or null when the domain does not look like a typo. */
    public static function suggestion(string $email): ?string
    {
        $at = strrpos($email, '@');
        if ($at === false) {
            return null;
        }

        $name = substr($email, 0, $at);
        $domain = strtolower(substr($email, $at + 1));

        foreach (self::DOMAINS as $correct => $typos) {
            if (in_array($domain, $typos, true)) {
                return $name.'@'.$correct;
            }
        }

        // No real address ends in ".con".
        if (str_ends_with($domain, '.con')) {
            return $name.'@'.substr($domain, 0, -4).'.com';
        }

        return null;
    }

    /** Validation rule: rejects a likely typo with a "Did you mean ...?" message. */
    public static function rule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (is_string($value) && ($suggestion = self::suggestion($value)) !== null) {
                $fail("Check your email address. Did you mean {$suggestion}?");
            }
        };
    }
}
