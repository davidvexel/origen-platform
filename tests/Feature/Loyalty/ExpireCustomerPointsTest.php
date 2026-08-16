<?php

namespace Tests\Feature\Loyalty;

use App\Domain\Loyalty\Actions\ExpireCustomerPoints;
use App\Domain\Loyalty\Models\LoyaltyCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireCustomerPointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_due_remaining_points_expire(): void
    {
        $customer = LoyaltyCustomer::query()->create([
            'name' => 'Cliente prueba',
            'points_balance' => 30,
        ]);
        $expiredLot = $customer->movements()->create([
            'type' => 'earn', 'points' => 20, 'remaining_points' => 12,
            'balance_before' => 0, 'balance_after' => 20,
            'occurred_at' => now()->subMonths(7), 'expires_at' => now()->subDay(),
        ]);
        $activeLot = $customer->movements()->create([
            'type' => 'earn', 'points' => 18, 'remaining_points' => 18,
            'balance_before' => 12, 'balance_after' => 30,
            'occurred_at' => now(), 'expires_at' => now()->addMonths(6),
        ]);

        $expiredUnits = app(ExpireCustomerPoints::class)->execute($customer);

        $this->assertSame(1200, $expiredUnits);
        $this->assertSame('18.00', $customer->fresh()->points_balance);
        $this->assertSame('0.00', $expiredLot->fresh()->remaining_points);
        $this->assertNotNull($expiredLot->fresh()->expired_at);
        $this->assertSame('18.00', $activeLot->fresh()->remaining_points);
        $this->assertDatabaseHas('loyalty_movements', ['type' => 'expire', 'points' => -12]);
    }
}
