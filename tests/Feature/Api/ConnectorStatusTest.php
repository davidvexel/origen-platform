<?php

namespace Tests\Feature\Api;

use App\Domain\Integrations\Models\ApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConnectorStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_connector_can_verify_its_identity(): void
    {
        $token = 'orp_status-test-token';
        ApiClient::query()->create([
            'name' => 'SoftRestaurant Playa',
            'location_id' => 'origen-playa',
            'token_prefix' => substr($token, 0, 12),
            'token_hash' => hash('sha256', $token),
            'active' => true,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/connector')
            ->assertOk()
            ->assertExactJson([
                'status' => 'ok',
                'client' => 'SoftRestaurant Playa',
                'location_id' => 'origen-playa',
            ]);
    }

    public function test_connector_identity_requires_token(): void
    {
        $this->getJson('/api/v1/connector')->assertUnauthorized();
    }
}
