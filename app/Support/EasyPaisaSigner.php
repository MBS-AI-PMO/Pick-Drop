<?php

namespace App\Support;

class EasyPaisaSigner
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public static function hash(array $payload, string $key): string
    {
        unset($payload['hash'], $payload['Credentials']);
        ksort($payload);

        $parts = [];
        foreach ($payload as $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $parts[] = (string) $value;
        }

        return hash_hmac('sha256', implode('&', $parts), $key);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function matches(array $payload, string $key, ?string $given): bool
    {
        if ($key === '' || !filled($given)) {
            return false;
        }

        return hash_equals(self::hash($payload, $key), strtolower(trim((string) $given)))
            || hash_equals(self::hash($payload, $key), strtoupper(trim((string) $given)));
    }
}
