<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Sales\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyRedemption extends Model
{
    protected $fillable = [
        'code', 'loyalty_customer_id', 'movement_id', 'sale_id', 'cashier_id',
        'location_id', 'sr_folio', 'status', 'points', 'value_mxn',
        'purchase_total_mxn', 'point_value_mxn', 'notes', 'requested_at',
        'expires_at', 'completed_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
            'value_mxn' => 'decimal:2',
            'purchase_total_mxn' => 'decimal:2',
            'point_value_mxn' => 'decimal:4',
            'requested_at' => 'datetime',
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(LoyaltyCustomer::class, 'loyalty_customer_id');
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(LoyaltyMovement::class, 'movement_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
