<?php

namespace App\Domain\Sales\Models;

use App\Domain\Integrations\Models\ApiClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'api_client_id', 'source', 'location_id', 'folio', 'ticket',
        'opened_at', 'closed_at', 'station', 'customer_external_id',
        'customer_name', 'subtotal', 'tax', 'total', 'tip',
        'total_with_tip', 'payload_hash', 'raw_payload', 'received_at',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'received_at' => 'datetime',
            'raw_payload' => 'array',
            'subtotal' => 'decimal:6',
            'tax' => 'decimal:6',
            'total' => 'decimal:6',
            'tip' => 'decimal:6',
            'total_with_tip' => 'decimal:6',
        ];
    }

    public function apiClient(): BelongsTo
    {
        return $this->belongsTo(ApiClient::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class)->orderBy('position');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class)->orderBy('position');
    }
}
