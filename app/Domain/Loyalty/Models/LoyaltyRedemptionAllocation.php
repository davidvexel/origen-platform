<?php

namespace App\Domain\Loyalty\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyRedemptionAllocation extends Model
{
    protected $fillable = ['redemption_movement_id', 'earning_movement_id', 'points'];

    protected function casts(): array
    {
        return ['points' => 'decimal:2'];
    }

    public function redemption(): BelongsTo
    {
        return $this->belongsTo(LoyaltyMovement::class, 'redemption_movement_id');
    }

    public function earning(): BelongsTo
    {
        return $this->belongsTo(LoyaltyMovement::class, 'earning_movement_id');
    }
}
