<?php

namespace App\Console\Commands;

use App\Domain\Loyalty\Actions\CancelRedemption;
use App\Domain\Loyalty\Actions\ExpireCustomerPoints;
use App\Domain\Loyalty\Models\LoyaltyCustomer;
use App\Domain\Loyalty\Models\LoyaltyRedemption;
use Illuminate\Console\Command;

class ExpireLoyaltyPoints extends Command
{
    protected $signature = 'loyalty:expire-points';

    protected $description = 'Expire due Loyalty earning lots and update customer balances';

    public function handle(ExpireCustomerPoints $action, CancelRedemption $cancelRedemption): int
    {
        $customers = LoyaltyCustomer::query()
            ->whereHas('movements', fn ($query) => $query
                ->where('type', 'earn')
                ->where('remaining_points', '>', 0)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now()))
            ->cursor();

        $customerCount = 0;
        $expiredUnits = 0;
        foreach ($customers as $customer) {
            $units = $action->execute($customer);
            if ($units > 0) {
                $customerCount++;
                $expiredUnits += $units;
            }
        }

        $this->info(sprintf(
            'Expired %.2f points across %d customers.',
            $expiredUnits / 100,
            $customerCount,
        ));

        $expiredRedemptions = 0;
        LoyaltyRedemption::query()
            ->where('status', 'pending')
            ->where('expires_at', '<=', now())
            ->eachById(function (LoyaltyRedemption $redemption) use ($cancelRedemption, &$expiredRedemptions): void {
                $cancelRedemption->execute($redemption, reason: 'expired');
                $expiredRedemptions++;
            });
        $this->info("Expired {$expiredRedemptions} pending redemptions.");

        return self::SUCCESS;
    }
}
