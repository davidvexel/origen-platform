<?php

namespace App\Domain\Loyalty\Actions;

use App\Domain\Loyalty\Models\LoyaltyCustomer;
use App\Domain\Loyalty\Models\LoyaltyMovement;
use App\Domain\Loyalty\Models\LoyaltyProgramSetting;
use App\Domain\Loyalty\Support\PointAmount;
use App\Domain\Sales\Models\Sale;

class AwardSalePoints
{
    public function execute(Sale $sale): ?LoyaltyMovement
    {
        if ($sale->loyalty_customer_id === null) {
            return null;
        }

        $settings = LoyaltyProgramSetting::current();
        $customer = LoyaltyCustomer::query()->lockForUpdate()->find($sale->loyalty_customer_id);

        if (! $settings->active || $customer === null || ! $customer->rewards_enabled || $customer->status !== 'active') {
            return null;
        }

        if (! $settings->discounted_sales_earn_points && $sale->items()->where('discount', '>', 0)->exists()) {
            return null;
        }

        $pointsPaymentAmount = (float) $sale->payments()
            ->where('method', config('loyalty.points_payment_method'))
            ->sum('amount');
        $eligibleAmount = max(0, (float) $sale->total - $pointsPaymentAmount);
        if ($settings->tips_earn_points) {
            $eligibleAmount += (float) $sale->tip;
        }

        $pointValue = (float) $settings->point_value_mxn;
        if ($eligibleAmount <= 0 || $pointValue <= 0) {
            return null;
        }

        $pointsUnits = (int) round(
            ($eligibleAmount * (float) $settings->cashback_percent / 100 / $pointValue) * 100
        );

        if ($pointsUnits <= 0) {
            return null;
        }

        $existing = LoyaltyMovement::query()
            ->where('sale_id', $sale->id)
            ->where('type', 'earn')
            ->first();
        if ($existing !== null) {
            return $existing;
        }

        $balanceBeforeUnits = PointAmount::units($customer->points_balance);
        $balanceAfterUnits = $balanceBeforeUnits + $pointsUnits;
        $points = PointAmount::decimal($pointsUnits);
        $customer->update(['points_balance' => PointAmount::decimal($balanceAfterUnits)]);

        return $customer->movements()->create([
            'sale_id' => $sale->id,
            'type' => 'earn',
            'points' => $points,
            'remaining_points' => $points,
            'balance_before' => PointAmount::decimal($balanceBeforeUnits),
            'balance_after' => PointAmount::decimal($balanceAfterUnits),
            'reference' => (string) $sale->ticket,
            'metadata' => [
                'eligible_amount_mxn' => number_format($eligibleAmount, 2, '.', ''),
                'cashback_percent' => $settings->cashback_percent,
                'point_value_mxn' => $settings->point_value_mxn,
                'tips_earn_points' => $settings->tips_earn_points,
            ],
            'occurred_at' => $sale->closed_at,
            'expires_at' => $sale->closed_at->copy()->addMonthsNoOverflow($settings->expiration_months),
        ]);
    }
}
