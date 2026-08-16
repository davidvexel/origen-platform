<?php

namespace App\Domain\Loyalty\Actions;

use App\Domain\Loyalty\Models\LoyaltyRedemption;
use App\Domain\Sales\Models\Sale;

class FinalizeSaleRedemption
{
    public function execute(Sale $sale): ?LoyaltyRedemption
    {
        $paymentAmount = (float) $sale->payments()
            ->where('method', config('loyalty.points_payment_method'))
            ->sum('amount');

        if ($paymentAmount <= 0) {
            return null;
        }

        $query = LoyaltyRedemption::query()
            ->where('location_id', $sale->location_id)
            ->where('sr_folio', $sale->folio)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->whereBetween('value_mxn', [$paymentAmount - 0.01, $paymentAmount + 0.01]);

        if ($sale->loyalty_customer_id !== null) {
            $query->where('loyalty_customer_id', $sale->loyalty_customer_id);
        }

        $matches = $query->lockForUpdate()->get();
        if ($matches->count() !== 1) {
            return null;
        }

        $redemption = $matches->sole();
        $redemption->update([
            'sale_id' => $sale->id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        $redemption->movement()->update(['sale_id' => $sale->id]);

        return $redemption;
    }
}
