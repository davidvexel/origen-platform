<?php

namespace Tests\Feature\Console;

use App\Domain\Integrations\Models\ApiClient;
use App\Domain\Sales\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarkSaleAsTestTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_preserves_sale_and_releases_production_identity(): void
    {
        $client = ApiClient::query()->create([
            'name' => 'Test connector',
            'location_id' => 'origen-playa',
            'token_prefix' => 'orp_test',
            'token_hash' => hash('sha256', 'orp_test'),
            'active' => true,
        ]);
        $sale = $this->createSale($client);

        $this->artisan('sales:mark-test', ['sale_id' => $sale->id, '--force' => true])
            ->assertSuccessful();

        $sale->refresh();
        $this->assertTrue($sale->is_test);
        $this->assertSame("test-{$sale->id}-softrestaurant", $sale->source);

        $replacement = $this->createSale($client);
        $replacement->refresh();
        $this->assertNotSame($sale->id, $replacement->id);
        $this->assertFalse($replacement->is_test);
    }

    private function createSale(ApiClient $client): Sale
    {
        return Sale::query()->create([
            'api_client_id' => $client->id,
            'source' => 'softrestaurant',
            'location_id' => 'origen-playa',
            'folio' => 11,
            'ticket' => 1737,
            'opened_at' => '2026-08-15 15:21:42',
            'closed_at' => '2026-08-15 15:22:55',
            'subtotal' => 100,
            'tax' => 16,
            'total' => 116,
            'tip' => 0,
            'total_with_tip' => 116,
            'payload_hash' => hash('sha256', 'payload'),
            'raw_payload' => ['ticket' => 1737],
            'received_at' => now(),
        ]);
    }
}
