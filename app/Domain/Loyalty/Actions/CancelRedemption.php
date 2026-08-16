<?php

namespace App\Domain\Loyalty\Actions;

use App\Domain\Loyalty\Models\LoyaltyCustomer;
use App\Domain\Loyalty\Models\LoyaltyRedemption;
use App\Domain\Loyalty\Models\LoyaltyRedemptionAllocation;
use App\Domain\Loyalty\Support\PointAmount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelRedemption
{
    public function execute(LoyaltyRedemption $redemption, ?User $actor = null, string $reason = 'cancelled'): void
    {
        DB::transaction(function () use ($redemption, $actor, $reason): void {
            $lockedRedemption = LoyaltyRedemption::query()->lockForUpdate()->findOrFail($redemption->id);
            if ($lockedRedemption->status !== 'pending') {
                throw ValidationException::withMessages(['redemption' => 'Sólo una redención pendiente puede cancelarse.']);
            }

            $customer = LoyaltyCustomer::query()->lockForUpdate()->findOrFail($lockedRedemption->loyalty_customer_id);
            $balanceBeforeUnits = PointAmount::units($customer->points_balance);
            $restoredUnits = 0;
            $allocations = LoyaltyRedemptionAllocation::query()
                ->where('redemption_movement_id', $lockedRedemption->movement_id)
                ->with('earning')
                ->lockForUpdate()
                ->get();

            foreach ($allocations as $allocation) {
                $units = PointAmount::units($allocation->points);
                $earningUnits = PointAmount::units($allocation->earning->remaining_points);
                $allocation->earning->update([
                    'remaining_points' => PointAmount::decimal($earningUnits + $units),
                ]);
                $restoredUnits += $units;
            }

            $balanceAfterUnits = $balanceBeforeUnits + $restoredUnits;
            $customer->update(['points_balance' => PointAmount::decimal($balanceAfterUnits)]);
            $customer->movements()->create([
                'type' => 'refund',
                'points' => PointAmount::decimal($restoredUnits),
                'balance_before' => PointAmount::decimal($balanceBeforeUnits),
                'balance_after' => PointAmount::decimal($balanceAfterUnits),
                'reference' => $lockedRedemption->code,
                'metadata' => [
                    'redemption_id' => $lockedRedemption->id,
                    'reason' => $reason,
                ],
                'created_by' => $actor?->id,
                'occurred_at' => now(),
            ]);
            $lockedRedemption->update([
                'status' => $reason === 'expired' ? 'expired' : 'cancelled',
                'cancelled_at' => now(),
            ]);
        }, attempts: 3);
    }
}
