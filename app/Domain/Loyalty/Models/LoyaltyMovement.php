<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Sales\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyMovement extends Model
{
    protected $fillable = [
        'loyalty_customer_id', 'sale_id', 'type', 'points', 'remaining_points',
        'balance_before', 'balance_after', 'reference', 'notes', 'metadata',
        'created_by', 'occurred_at', 'expires_at', 'expired_at',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'decimal:2',
            'remaining_points' => 'decimal:2',
            'balance_before' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'occurred_at' => 'datetime',
            'expires_at' => 'datetime',
            'expired_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(LoyaltyCustomer::class, 'loyalty_customer_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
