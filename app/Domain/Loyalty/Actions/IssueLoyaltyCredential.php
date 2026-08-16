<?php

namespace App\Domain\Loyalty\Actions;

use App\Domain\Loyalty\Models\LoyaltyCredential;
use App\Domain\Loyalty\Models\LoyaltyCustomer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IssueLoyaltyCredential
{
    public function execute(LoyaltyCustomer $customer, bool $replace = false): LoyaltyCredential
    {
        return DB::transaction(function () use ($customer, $replace): LoyaltyCredential {
            $credential = LoyaltyCredential::query()
                ->where('loyalty_customer_id', $customer->id)
                ->lockForUpdate()
                ->first();

            if ($credential !== null && ! $replace && $credential->status === 'active') {
                return $credential;
            }

            $token = Str::random(64);
            $attributes = [
                'token_encrypted' => $token,
                'token_hash' => hash('sha256', $token),
                'status' => 'active',
                'issued_at' => now(),
                'last_used_at' => null,
                'revoked_at' => null,
            ];

            if ($credential !== null) {
                $credential->update($attributes);

                return $credential->refresh();
            }

            return LoyaltyCredential::query()->create($attributes + [
                'loyalty_customer_id' => $customer->id,
                'member_number' => $customer->external_id,
            ]);
        }, attempts: 3);
    }
}
