<?php

namespace App\Domain\Loyalty\Support;

use App\Domain\Loyalty\Models\LoyaltyCustomer;
use Illuminate\Support\Str;

class SoftRestaurantCustomerId
{
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public static function generate(): string
    {
        do {
            $suffix = collect(range(1, 6))
                ->map(fn (): string => self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)])
                ->implode('');
            $id = 'ON-'.$suffix;
        } while (LoyaltyCustomer::query()->where('external_id', $id)->exists());

        return Str::upper($id);
    }
}
