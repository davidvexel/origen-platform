<?php

namespace App\Domain\Loyalty\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyCredential extends Model
{
    protected $fillable = [
        'loyalty_customer_id', 'member_number', 'token_encrypted', 'token_hash',
        'status', 'issued_at', 'last_used_at', 'revoked_at',
        'apple_serial_number', 'google_object_id',
    ];

    protected $hidden = ['token_encrypted', 'token_hash'];

    protected function casts(): array
    {
        return [
            'token_encrypted' => 'encrypted',
            'issued_at' => 'datetime',
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(LoyaltyCustomer::class, 'loyalty_customer_id');
    }

    public function publicUrl(): string
    {
        return route('loyalty-card.show', ['token' => $this->token_encrypted]);
    }
}
