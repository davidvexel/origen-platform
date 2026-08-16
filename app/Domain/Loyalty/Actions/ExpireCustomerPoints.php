<?php

namespace App\Domain\Loyalty\Actions;

use App\Domain\Loyalty\Models\LoyaltyCustomer;
use App\Domain\Loyalty\Models\LoyaltyMovement;
use App\Domain\Loyalty\Support\PointAmount;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

class ExpireCustomerPoints
{
    public function execute(LoyaltyCustomer $customer, ?DateTimeInterface $asOf = null): int
    {
        return DB::transaction(function () use ($customer, $asOf): int {
            $now = $asOf ?? now();
            $lockedCustomer = LoyaltyCustomer::query()->lockForUpdate()->findOrFail($customer->id);
            $balanceUnits = PointAmount::units($lockedCustomer->points_balance);
            $expiredUnits = 0;

            $earnings = LoyaltyMovement::query()
                ->where('loyalty_customer_id', $lockedCustomer->id)
                ->where('type', 'earn')
                ->where('remaining_points', '>', 0)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', $now)
                ->oldest('expires_at')
                ->lockForUpdate()
                ->get();

            foreach ($earnings as $earning) {
                $lotUnits = PointAmount::units($earning->remaining_points);
                if ($lotUnits <= 0) {
                    continue;
                }

                $before = $balanceUnits;
                $balanceUnits = max(0, $balanceUnits - $lotUnits);
                $actualExpired = $before - $balanceUnits;
                $earning->update(['remaining_points' => 0, 'expired_at' => $now]);

                if ($actualExpired > 0) {
                    $lockedCustomer->movements()->create([
                        'type' => 'expire',
                        'points' => PointAmount::decimal(-$actualExpired),
                        'balance_before' => PointAmount::decimal($before),
                        'balance_after' => PointAmount::decimal($balanceUnits),
                        'reference' => "earn:{$earning->id}",
                        'metadata' => ['earning_movement_id' => $earning->id],
                        'occurred_at' => $now,
                    ]);
                    $expiredUnits += $actualExpired;
                }
            }

            if ($expiredUnits > 0) {
                $lockedCustomer->update(['points_balance' => PointAmount::decimal($balanceUnits)]);
            }

            return $expiredUnits;
        }, attempts: 3);
    }
}
