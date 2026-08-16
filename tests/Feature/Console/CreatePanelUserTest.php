<?php

namespace Tests\Feature\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatePanelUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_user_non_interactively_with_a_generated_password(): void
    {
        $this->artisan('panel-user:create', [
            'name' => 'Admin Test',
            'email' => 'admin@example.test',
            '--role' => 'admin',
            '--generate-password' => true,
        ])->assertSuccessful();

        $user = User::query()->where('email', 'admin@example.test')->firstOrFail();

        $this->assertSame('Admin Test', $user->name);
        $this->assertSame('admin', $user->role);
        $this->assertTrue($user->active);
    }
}
