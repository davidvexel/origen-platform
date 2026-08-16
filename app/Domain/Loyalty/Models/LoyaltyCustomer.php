<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Sales\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyCustomer extends Model
{
    protected $fillable = [
        'external_id', 'name', 'email', 'phone', 'birthday', 'points_balance',
        'status', 'sr_sync_status', 'sr_synced_at', 'sr_sync_notes', 'registered_by',
    ];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'points_balance' => 'decimal:2',
            'sr_synced_at' => 'datetime',
        ];
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(LoyaltyMovement::class)->latest('occurred_at');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
