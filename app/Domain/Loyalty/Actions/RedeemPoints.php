<?php

namespace App\Domain\Loyalty\Actions;

use App\Domain\Loyalty\Models\LoyaltyCustomer;
use App\Domain\Loyalty\Models\LoyaltyMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RedeemPoints
{
    public function execute(
        LoyaltyCustomer $customer,
        string $points,
        User $cashier,
        ?string $reference = null,
        ?string $notes = null,
    ): LoyaltyMovement {
        return DB::transaction(function () use ($customer, $points, $cashier, $reference, $notes): LoyaltyMovement {
            $lockedCustomer = LoyaltyCustomer::query()->lockForUpdate()->findOrFail($customer->id);
            $amount = round((float) $points, 2);
            $balanceBefore = round((float) $lockedCustomer->points_balance, 2);

            if ($amount <= 0) {
                throw ValidationException::withMessages(['points' => 'Los puntos deben ser mayores a cero.']);
            }

            if ($amount > $balanceBefore) {
                throw ValidationException::withMessages(['points' => 'El cliente no tiene puntos suficientes.']);
            }

            $balanceAfter = $balanceBefore - $amount;
            $lockedCustomer->update(['points_balance' => $balanceAfter]);

            return $lockedCustomer->movements()->create([
                'type' => 'redeem',
                'points' => -$amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference' => $reference,
                'notes' => $notes,
                'created_by' => $cashier->id,
                'occurred_at' => now(),
            ]);
        }, attempts: 3);
    }
}
