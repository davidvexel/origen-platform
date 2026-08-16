<?php

namespace App\Domain\Sales\Support;

class PayloadHasher
{
    /** @param array<string, mixed> $payload */
    public static function hash(array $payload): string
    {
        $normalized = self::normalize($payload);

        return hash('sha256', json_encode($normalized, JSON_THROW_ON_ERROR));
    }

    private static function normalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (! array_is_list($value)) {
            ksort($value);
        }

        return array_map(self::normalize(...), $value);
    }
}
