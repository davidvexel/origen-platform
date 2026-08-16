<?php

namespace Tests\Feature\Loyalty;

use App\Domain\Loyalty\Actions\CancelRedemption;
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
        $customer->movements()->create([
            'type' => 'earn',
            'points' => 150,
            'remaining_points' => 150,
            'balance_before' => 0,
            'balance_after' => 150,
            'occurred_at' => now(),
            'expires_at' => now()->addMonths(6),
        ]);

        $redemption = app(RedeemPoints::class)->execute(
            $customer, '40.50', '100.00', 12, 'origen-playa', $cashier, 'T-1744'
        );
        $movement = $redemption->movement;

        $this->assertSame('109.50', $customer->fresh()->points_balance);
        $this->assertSame('-40.50', $movement->points);
        $this->assertSame('150.00', $movement->balance_before);
        $this->assertSame('109.50', $movement->balance_after);
        $this->assertSame($cashier->id, $movement->created_by);
        $this->assertSame('T-1744', $movement->reference);
        $this->assertSame('pending', $redemption->status);
        $this->assertSame(12, $redemption->sr_folio);
        $this->assertSame('40.50', $redemption->value_mxn);
    }

    public function test_redemption_cannot_exceed_available_balance(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $customer = LoyaltyCustomer::query()->create([
            'name' => 'Cliente prueba',
            'points_balance' => 10,
        ]);
        $customer->movements()->create([
            'type' => 'earn',
            'points' => 10,
            'remaining_points' => 10,
            'balance_before' => 0,
            'balance_after' => 10,
            'occurred_at' => now(),
            'expires_at' => now()->addMonths(6),
        ]);

        try {
            app(RedeemPoints::class)->execute($customer, '20.00', '100.00', 12, 'origen-playa', $cashier);
            $this->fail('Expected validation exception.');
        } catch (ValidationException) {
            $this->assertSame('10.00', $customer->fresh()->points_balance);
            $this->assertDatabaseMissing('loyalty_movements', ['type' => 'redeem']);
        }
    }

    public function test_redemption_can_cover_all_but_not_more_than_the_purchase(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $customer = LoyaltyCustomer::query()->create([
            'name' => 'Cliente prueba',
            'points_balance' => 200,
        ]);
        $customer->movements()->create([
            'type' => 'earn', 'points' => 200, 'remaining_points' => 200,
            'balance_before' => 0, 'balance_after' => 200,
            'occurred_at' => now(), 'expires_at' => now()->addMonths(6),
        ]);

        app(RedeemPoints::class)->execute($customer, '100.00', '100.00', 12, 'origen-playa', $cashier);
        $this->assertSame('100.00', $customer->fresh()->points_balance);

        $this->expectException(ValidationException::class);
        app(RedeemPoints::class)->execute($customer, '20.01', '20.00', 13, 'origen-playa', $cashier);
    }

    public function test_cancelling_pending_redemption_restores_customer_balance(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $customer = LoyaltyCustomer::query()->create(['name' => 'Cliente prueba', 'points_balance' => 50]);
        $earning = $customer->movements()->create([
            'type' => 'earn', 'points' => 50, 'remaining_points' => 50,
            'balance_before' => 0, 'balance_after' => 50,
            'occurred_at' => now(), 'expires_at' => now()->addMonths(6),
        ]);
        $redemption = app(RedeemPoints::class)->execute(
            $customer, '20.00', '100.00', 15, 'origen-playa', $cashier
        );

        app(CancelRedemption::class)->execute($redemption, $cashier, 'cancelled_by_cashier');

        $this->assertSame('50.00', $customer->fresh()->points_balance);
        $this->assertSame('50.00', $earning->fresh()->remaining_points);
        $this->assertSame('cancelled', $redemption->fresh()->status);
        $this->assertDatabaseHas('loyalty_movements', ['type' => 'refund', 'points' => 20]);
    }
}
