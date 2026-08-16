<?php

namespace App\Domain\Loyalty\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyProgramSetting extends Model
{
    protected $fillable = [
        'cashback_percent', 'point_value_mxn', 'minimum_redemption_points',
        'expiration_months', 'tips_earn_points', 'discounted_sales_earn_points',
        'maximum_redemption_percent', 'active',
    ];

    protected function casts(): array
    {
        return [
            'cashback_percent' => 'decimal:4',
            'point_value_mxn' => 'decimal:4',
            'minimum_redemption_points' => 'decimal:2',
            'expiration_months' => 'integer',
            'tips_earn_points' => 'boolean',
            'discounted_sales_earn_points' => 'boolean',
            'maximum_redemption_percent' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate(['id' => 1]);
    }
}
