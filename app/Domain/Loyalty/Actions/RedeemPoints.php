<?php

namespace App\Domain\Loyalty\Actions;

use App\Domain\Loyalty\Models\LoyaltyCustomer;
use App\Domain\Loyalty\Models\LoyaltyMovement;
use App\Domain\Loyalty\Models\LoyaltyProgramSetting;
use App\Domain\Loyalty\Models\LoyaltyRedemption;
use App\Domain\Loyalty\Models\LoyaltyRedemptionAllocation;
use App\Domain\Loyalty\Support\PointAmount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RedeemPoints
{
    public function execute(
        LoyaltyCustomer $customer,
        string $points,
        string $purchaseTotal,
        int $srFolio,
        string $locationId,
        User $cashier,
        ?string $reference = null,
        ?string $notes = null,
    ): LoyaltyRedemption {
        app(ExpireCustomerPoints::class)->execute($customer);

        return DB::transaction(function () use ($customer, $points, $purchaseTotal, $srFolio, $locationId, $cashier, $reference, $notes): LoyaltyRedemption {
            $lockedCustomer = LoyaltyCustomer::query()->lockForUpdate()->findOrFail($customer->id);
            $settings = LoyaltyProgramSetting::current();
            $amountUnits = PointAmount::units($points);
            $balanceBeforeUnits = PointAmount::units($lockedCustomer->points_balance);
            $purchaseTotalValue = round((float) $purchaseTotal, 2);

            if (! $settings->active || ! $lockedCustomer->rewards_enabled || $lockedCustomer->status !== 'active') {
                throw ValidationException::withMessages(['customerId' => 'Este cliente no participa en recompensas.']);
            }

            if ($amountUnits <= 0) {
                throw ValidationException::withMessages(['points' => 'Los puntos deben ser mayores a cero.']);
            }

            if ($amountUnits < PointAmount::units($settings->minimum_redemption_points)) {
                throw ValidationException::withMessages([
                    'points' => "La redención mínima es de {$settings->minimum_redemption_points} puntos.",
                ]);
            }

            if ($amountUnits > $balanceBeforeUnits) {
                throw ValidationException::withMessages(['points' => 'El cliente no tiene puntos suficientes.']);
            }

            if ($purchaseTotalValue <= 0) {
                throw ValidationException::withMessages(['purchaseTotal' => 'El total de compra debe ser mayor a cero.']);
            }

            if ($srFolio <= 0) {
                throw ValidationException::withMessages(['srFolio' => 'El folio de SoftRestaurant debe ser mayor a cero.']);
            }

            $activeFolioRedemptionExists = LoyaltyRedemption::query()
                ->where('location_id', $locationId)
                ->where('sr_folio', $srFolio)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->exists();
            if ($activeFolioRedemptionExists) {
                throw ValidationException::withMessages([
                    'srFolio' => 'Este folio ya tiene una redención pendiente.',
                ]);
            }

            $redemptionValue = ($amountUnits / 100) * (float) $settings->point_value_mxn;
            $maximumValue = $purchaseTotalValue * (float) $settings->maximum_redemption_percent / 100;
            if ($redemptionValue > $maximumValue + 0.001) {
                throw ValidationException::withMessages([
                    'points' => 'La redención supera el máximo permitido para esta compra.',
                ]);
            }

            $balanceAfterUnits = $balanceBeforeUnits - $amountUnits;
            $lockedCustomer->update(['points_balance' => PointAmount::decimal($balanceAfterUnits)]);

            $movement = $lockedCustomer->movements()->create([
                'type' => 'redeem',
                'points' => PointAmount::decimal(-$amountUnits),
                'balance_before' => PointAmount::decimal($balanceBeforeUnits),
                'balance_after' => PointAmount::decimal($balanceAfterUnits),
                'reference' => $reference,
                'notes' => $notes,
                'metadata' => [
                    'purchase_total_mxn' => number_format($purchaseTotalValue, 2, '.', ''),
                    'redemption_value_mxn' => number_format($redemptionValue, 2, '.', ''),
                    'point_value_mxn' => $settings->point_value_mxn,
                    'sr_folio' => $srFolio,
                    'location_id' => $locationId,
                ],
                'created_by' => $cashier->id,
                'occurred_at' => now(),
            ]);

            $remainingUnits = $amountUnits;
            $earnings = LoyaltyMovement::query()
                ->where('loyalty_customer_id', $lockedCustomer->id)
                ->where('type', 'earn')
                ->where('remaining_points', '>', 0)
                ->oldest('expires_at')
                ->oldest('id')
                ->lockForUpdate()
                ->get();

            foreach ($earnings as $earning) {
                if ($remainingUnits === 0) {
                    break;
                }

                $availableUnits = PointAmount::units($earning->remaining_points);
                $usedUnits = min($availableUnits, $remainingUnits);
                if ($usedUnits <= 0) {
                    continue;
                }

                $earning->update([
                    'remaining_points' => PointAmount::decimal($availableUnits - $usedUnits),
                ]);
                LoyaltyRedemptionAllocation::query()->create([
                    'redemption_movement_id' => $movement->id,
                    'earning_movement_id' => $earning->id,
                    'points' => PointAmount::decimal($usedUnits),
                ]);
                $remainingUnits -= $usedUnits;
            }

            if ($remainingUnits !== 0) {
                throw ValidationException::withMessages(['points' => 'El saldo disponible necesita conciliación.']);
            }

            do {
                $code = 'OP-'.Str::upper(Str::random(10));
            } while (LoyaltyRedemption::query()->where('code', $code)->exists());

            return LoyaltyRedemption::query()->create([
                'code' => $code,
                'loyalty_customer_id' => $lockedCustomer->id,
                'movement_id' => $movement->id,
                'cashier_id' => $cashier->id,
                'location_id' => $locationId,
                'sr_folio' => $srFolio,
                'status' => 'pending',
                'points' => PointAmount::decimal($amountUnits),
                'value_mxn' => number_format($redemptionValue, 2, '.', ''),
                'purchase_total_mxn' => number_format($purchaseTotalValue, 2, '.', ''),
                'point_value_mxn' => $settings->point_value_mxn,
                'notes' => $notes,
                'requested_at' => now(),
                'expires_at' => now()->addHours(config('loyalty.redemption_expiration_hours')),
            ]);
        }, attempts: 3);
    }
}
