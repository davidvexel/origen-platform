<?php

namespace Tests\Feature\Loyalty;

use App\Domain\Loyalty\Actions\IssueLoyaltyCredential;
use App\Domain\Loyalty\Models\LoyaltyCustomer;
use App\Filament\Pages\PendingSrCustomers;
use App\Filament\Pages\ScanLoyaltyCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoyaltyCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_cashier_can_issue_and_view_customer_card(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'active' => true]);
        $customer = LoyaltyCustomer::query()->create([
            'name' => 'David Leal',
            'points_balance' => 2,
        ]);

        $this->actingAs($cashier)
            ->get(route('loyalty-card.admin', $customer))
            ->assertOk()
            ->assertSee('Origen Rewards')
            ->assertSee('David Leal')
            ->assertSee('2.00')
            ->assertSee('<svg', escape: false);

        $this->assertDatabaseCount('loyalty_credentials', 1);
        $this->assertMatchesRegularExpression('/^ON-[A-Z2-9]{6}$/', $customer->external_id);
        $this->assertSame($customer->external_id, $customer->credential->member_number);
        $this->assertSame(64, strlen($customer->credential->token_encrypted));
    }

    public function test_public_token_displays_card_and_unknown_token_is_rejected(): void
    {
        $customer = LoyaltyCustomer::query()->create(['name' => 'Cliente QR']);
        $credential = app(IssueLoyaltyCredential::class)->execute($customer);

        $this->get($credential->publicUrl())
            ->assertOk()
            ->assertSee('Cliente QR')
            ->assertSee($credential->member_number);
        $this->assertNotNull($credential->fresh()->last_used_at);

        $this->get(route('loyalty-card.show', ['token' => str_repeat('x', 64)]))->assertNotFound();
    }

    public function test_replacing_private_card_link_invalidates_previous_link_without_changing_sr_id(): void
    {
        $customer = LoyaltyCustomer::query()->create(['name' => 'Cliente QR']);
        $original = app(IssueLoyaltyCredential::class)->execute($customer);
        $oldUrl = $original->publicUrl();
        $replacement = app(IssueLoyaltyCredential::class)->execute($customer, replace: true);

        $this->assertSame($original->member_number, $replacement->member_number);
        $this->assertNotSame($oldUrl, $replacement->publicUrl());
        $this->get($oldUrl)->assertNotFound();
        $this->get($replacement->publicUrl())->assertOk();
    }

    public function test_scanned_sr_id_opens_redemption_with_customer_selected(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'active' => true]);
        $customer = LoyaltyCustomer::query()->create([
            'name' => 'Cliente escaneado',
            'points_balance' => 25,
        ]);
        app(IssueLoyaltyCredential::class)->execute($customer);

        $this->actingAs($cashier)
            ->get(route('filament.admin.pages.redeem-points', [
                'customer' => $customer->external_id,
            ]))
            ->assertOk()
            ->assertSee('Cliente escaneado')
            ->assertSee('25.00');

        Livewire::actingAs($cashier)
            ->test(ScanLoyaltyCard::class)
            ->set('code', $customer->external_id)
            ->call('identify')
            ->assertRedirect(route('filament.admin.pages.redeem-points', [
                'customer' => $customer->external_id,
            ]));
    }

    public function test_new_customers_receive_distinct_sr_ids_automatically(): void
    {
        $first = LoyaltyCustomer::query()->create(['name' => 'Primero']);
        $second = LoyaltyCustomer::query()->create(['name' => 'Segundo']);

        $this->assertMatchesRegularExpression('/^ON-[A-Z2-9]{6}$/', $first->external_id);
        $this->assertMatchesRegularExpression('/^ON-[A-Z2-9]{6}$/', $second->external_id);
        $this->assertNotSame($first->external_id, $second->external_id);
    }

    public function test_cashier_marks_generated_sr_id_as_synchronized_without_changing_it(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'active' => true]);
        $customer = LoyaltyCustomer::query()->create(['name' => 'Cliente pendiente']);
        $generatedId = $customer->external_id;

        Livewire::actingAs($cashier)
            ->test(PendingSrCustomers::class)
            ->assertSee($generatedId)
            ->call('markSynced', $customer->id);

        $customer->refresh();
        $this->assertSame($generatedId, $customer->external_id);
        $this->assertSame('synced', $customer->sr_sync_status);
        $this->assertNotNull($customer->sr_synced_at);
    }
}
