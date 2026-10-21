<?php

namespace App\Support;

class JazzCashSigner
{
    /**
     * @param  array<string, mixed>  $fields
     */
    public static function hash(array $fields, string $salt): string
    {
        unset($fields['pp_SecureHash']);
        ksort($fields);

        $parts = [$salt];
        foreach ($fields as $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $parts[] = (string) $value;
        }

        return strtoupper(hash_hmac('sha256', implode('&', $parts), $salt));
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    public static function matches(array $fields, string $salt, ?string $given): bool
    {
        if ($salt === '' || !filled($given)) {
            return false;
        }

        return hash_equals(self::hash($fields, $salt), strtoupper(trim((string) $given)));
    }
}
