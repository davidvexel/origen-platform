<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_admin_and_cashier_can_access_panel(): void
    {
        $panel = Filament::getPanel('admin');

        $this->assertTrue(User::factory()->make(['role' => 'admin', 'active' => true])->canAccessPanel($panel));
        $this->assertTrue(User::factory()->make(['role' => 'cashier', 'active' => true])->canAccessPanel($panel));
    }

    public function test_inactive_or_unknown_role_cannot_access_panel(): void
    {
        $panel = Filament::getPanel('admin');

        $this->assertFalse(User::factory()->make(['role' => 'cashier', 'active' => false])->canAccessPanel($panel));
        $this->assertFalse(User::factory()->make(['role' => 'viewer', 'active' => true])->canAccessPanel($panel));
    }

    public function test_cashier_can_open_operational_pages_but_not_user_administration(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier', 'active' => true]);

        $this->actingAs($cashier)->get('/admin/sales')->assertOk();
        $this->actingAs($cashier)->get('/admin/loyalty-customers')->assertOk();
        $this->actingAs($cashier)->get('/admin/pending-sr-customers')->assertOk();
        $this->actingAs($cashier)->get('/admin/redeem-points')->assertOk();
        $this->actingAs($cashier)->get('/admin/users')->assertForbidden();
        $this->actingAs($cashier)->get('/admin/loyalty-program-settings')->assertForbidden();
        $this->actingAs($cashier)->get('/admin/loyalty-program-settings/1/edit')->assertForbidden();
    }

    public function test_admin_can_open_user_administration(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/loyalty-program-settings')->assertOk();
    }
}
