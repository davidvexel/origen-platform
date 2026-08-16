<?php

namespace App\Domain\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalePayment extends Model
{
    protected $fillable = ['position', 'method', 'amount', 'tip', 'reference'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:6',
            'tip' => 'decimal:6',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
