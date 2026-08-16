<?php

namespace App\Domain\Loyalty\Support;

final class PointAmount
{
    public static function units(string|int|float $value): int
    {
        return (int) round((float) $value * 100);
    }

    public static function decimal(int $units): string
    {
        return number_format($units / 100, 2, '.', '');
    }
}
