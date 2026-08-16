<?php

namespace Tests\Feature\Loyalty;

use App\Domain\Loyalty\Actions\RedeemPoints;
use App\Domain\Loyalty\Models\LoyaltyCustomer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class RedeemPointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_redemption_updates_balance_and_creates_auditable_movement(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $customer = LoyaltyCustomer::query()->create([
            'name' => 'Cliente prueba',
            'points_balance' => 150,
        ]);

        $movement = app(RedeemPoints::class)->execute($customer, '40.50', $cashier, 'T-1744');

        $this->assertSame('109.50', $customer->fresh()->points_balance);
        $this->assertSame('-40.50', $movement->points);
        $this->assertSame('150.00', $movement->balance_before);
        $this->assertSame('109.50', $movement->balance_after);
        $this->assertSame($cashier->id, $movement->created_by);
        $this->assertSame('T-1744', $movement->reference);
    }

    public function test_redemption_cannot_exceed_available_balance(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $customer = LoyaltyCustomer::query()->create([
            'name' => 'Cliente prueba',
            'points_balance' => 10,
        ]);

        try {
            app(RedeemPoints::class)->execute($customer, '10.01', $cashier);
            $this->fail('Expected validation exception.');
        } catch (ValidationException) {
            $this->assertSame('10.00', $customer->fresh()->points_balance);
            $this->assertDatabaseCount('loyalty_movements', 0);
        }
    }
}
