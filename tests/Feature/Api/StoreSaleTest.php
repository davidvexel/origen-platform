<?php

namespace Tests\Feature\Api;

use App\Domain\Integrations\Models\ApiClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreSaleTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'orp_test-token-with-sufficient-entropy';

    public function test_token_is_required(): void
    {
        $this->postJson('/api/v1/sales', $this->payload())
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Invalid or missing API token.']);

        $this->assertDatabaseCount('sales', 0);
    }

    public function test_inactive_token_is_rejected(): void
    {
        $client = $this->createClient();
        $client->update(['active' => false]);

        $this->withToken(self::TOKEN)
            ->postJson('/api/v1/sales', $this->payload())
            ->assertUnauthorized();

        $this->assertDatabaseCount('sales', 0);
    }

    public function test_valid_token_stores_sale_items_payments_and_customer(): void
    {
        $this->createClient();

        $this->withToken(self::TOKEN)
            ->postJson('/api/v1/sales', $this->payload())
            ->assertCreated()
            ->assertJson([
                'ticket' => 1737,
                'duplicate' => false,
            ]);

        $this->assertDatabaseHas('sales', [
            'source' => 'softrestaurant',
            'location_id' => 'origen-playa',
            'folio' => 11,
            'ticket' => 1737,
            'customer_external_id' => 'DASDASDSAD2323',
            'total' => 395,
        ]);
        $this->assertDatabaseCount('sale_items', 2);
        $this->assertDatabaseHas('sale_items', [
            'product_id' => 'GRANDE',
            'modifier' => true,
            'compound_id' => '_XYLGXPK6R',
        ]);
        $this->assertDatabaseCount('sale_payments', 2);
    }

    public function test_repeated_identical_sale_is_idempotent(): void
    {
        $this->createClient();

        $this->withToken(self::TOKEN)->postJson('/api/v1/sales', $this->payload())->assertCreated();
        $this->withToken(self::TOKEN)
            ->postJson('/api/v1/sales', $this->payload())
            ->assertOk()
            ->assertJson(['ticket' => 1737, 'duplicate' => true]);

        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseCount('sale_items', 2);
        $this->assertDatabaseCount('sale_payments', 2);
    }

    public function test_same_identity_with_different_payload_returns_conflict(): void
    {
        $this->createClient();
        $this->withToken(self::TOKEN)->postJson('/api/v1/sales', $this->payload())->assertCreated();

        $changed = $this->payload();
        $changed['totals']['total'] = 999;

        $this->withToken(self::TOKEN)
            ->postJson('/api/v1/sales', $changed)
            ->assertConflict()
            ->assertJsonPath('ticket', 1737);

        $this->assertDatabaseCount('sales', 1);
    }

    public function test_same_ticket_is_allowed_for_different_locations(): void
    {
        $this->createClient('origen-playa', self::TOKEN);
        $secondToken = 'orp_second-location-token';
        $this->createClient('origen-tulum', $secondToken);

        $this->withToken(self::TOKEN)->postJson('/api/v1/sales', $this->payload())->assertCreated();
        $this->withToken($secondToken)->postJson('/api/v1/sales', $this->payload())->assertCreated();

        $this->assertDatabaseCount('sales', 2);
    }

    public function test_sale_without_customer_is_accepted(): void
    {
        $this->createClient();
        $payload = $this->payload();
        unset($payload['customer']);

        $this->withToken(self::TOKEN)->postJson('/api/v1/sales', $payload)->assertCreated();

        $this->assertDatabaseHas('sales', [
            'ticket' => 1737,
            'customer_external_id' => null,
            'customer_name' => null,
        ]);
    }

    private function createClient(
        string $locationId = 'origen-playa',
        string $token = self::TOKEN,
    ): ApiClient {
        return ApiClient::query()->create([
            'name' => 'Test connector',
            'location_id' => $locationId,
            'token_prefix' => substr($token, 0, 12),
            'token_hash' => hash('sha256', $token),
            'active' => true,
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return [
            'source' => 'softrestaurant',
            'folio' => 11,
            'ticket' => 1737,
            'opened_at' => '2026-08-15T15:21:42',
            'closed_at' => '2026-08-15T15:22:55',
            'station' => 'SERVIDOR',
            'customer' => [
                'external_id' => 'DASDASDSAD2323',
                'name' => 'PRUEBA 2',
            ],
            'totals' => [
                'subtotal' => 340.5172,
                'tax' => 54.4828,
                'total' => 395,
                'tip' => 25,
                'total_with_tip' => 395,
            ],
            'items' => [
                [
                    'product_id' => '04002',
                    'name' => 'DETOX VERDE',
                    'quantity' => 1,
                    'unit_price' => 160,
                    'discount' => 0,
                    'modifier' => false,
                    'compound_id' => '_XYLGXPK6R',
                    'compound_main' => true,
                ],
                [
                    'product_id' => 'GRANDE',
                    'name' => 'GRANDE',
                    'quantity' => 1,
                    'unit_price' => 30,
                    'discount' => 0,
                    'modifier' => true,
                    'compound_id' => '_XYLGXPK6R',
                    'compound_main' => false,
                ],
            ],
            'payments' => [
                ['method' => 'EF', 'amount' => 200, 'tip' => 10, 'reference' => null],
                ['method' => 'VISA', 'amount' => 195, 'tip' => 15, 'reference' => 'ABC'],
            ],
        ];
    }
}
