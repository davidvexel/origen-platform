<?php

namespace App\Domain\Loyalty\Models;

use App\Domain\Loyalty\Support\SoftRestaurantCustomerId;
use App\Domain\Sales\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LoyaltyCustomer extends Model
{
    protected $fillable = [
        'external_id', 'name', 'customer_type', 'email', 'phone', 'birthday', 'points_balance',
        'rewards_enabled', 'status', 'sr_sync_status', 'sr_synced_at', 'sr_sync_notes', 'registered_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (LoyaltyCustomer $customer): void {
            if (blank($customer->external_id)) {
                $customer->external_id = SoftRestaurantCustomerId::generate();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'points_balance' => 'decimal:2',
            'rewards_enabled' => 'boolean',
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

    public function redemptions(): HasMany
    {
        return $this->hasMany(LoyaltyRedemption::class);
    }

    public function credential(): HasOne
    {
        return $this->hasOne(LoyaltyCredential::class);
    }
}
