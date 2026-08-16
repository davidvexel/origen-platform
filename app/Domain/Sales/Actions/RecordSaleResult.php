<?php

namespace App\Domain\Sales\Actions;

use App\Domain\Sales\Models\Sale;

readonly class RecordSaleResult
{
    public function __construct(
        public Sale $sale,
        public bool $duplicate,
        public bool $conflict,
    ) {}
}
