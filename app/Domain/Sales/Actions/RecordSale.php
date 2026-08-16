<?php

namespace App\Domain\Sales\Actions;

use App\Domain\Integrations\Models\ApiClient;
use App\Domain\Loyalty\Models\LoyaltyCustomer;
use App\Domain\Sales\Models\Sale;
use App\Domain\Sales\Support\PayloadHasher;
use Illuminate\Support\Facades\DB;

class RecordSale
{
    /** @param array<string, mixed> $payload */
    public function execute(ApiClient $client, array $payload): RecordSaleResult
    {
        return DB::transaction(function () use ($client, $payload): RecordSaleResult {
            $payloadHash = PayloadHasher::hash($payload);
            $customer = $payload['customer'] ?? null;
            $totals = $payload['totals'];
            $loyaltyCustomer = empty($customer['external_id'] ?? null)
                ? null
                : LoyaltyCustomer::query()->where('external_id', $customer['external_id'])->first();

            $sale = Sale::query()->firstOrCreate(
                [
                    'source' => $payload['source'],
                    'location_id' => $client->location_id,
                    'ticket' => $payload['ticket'],
                ],
                [
                    'api_client_id' => $client->id,
                    'folio' => $payload['folio'],
                    'opened_at' => $payload['opened_at'],
                    'closed_at' => $payload['closed_at'],
                    'station' => $payload['station'] ?? null,
                    'customer_external_id' => $customer['external_id'] ?? null,
                    'customer_name' => $customer['name'] ?? null,
                    'loyalty_customer_id' => $loyaltyCustomer?->id,
                    'subtotal' => $totals['subtotal'],
                    'tax' => $totals['tax'],
                    'total' => $totals['total'],
                    'tip' => $totals['tip'],
                    'total_with_tip' => $totals['total_with_tip'],
                    'payload_hash' => $payloadHash,
                    'raw_payload' => $payload,
                    'received_at' => now(),
                ],
            );

            if (! $sale->wasRecentlyCreated) {
                return new RecordSaleResult(
                    sale: $sale,
                    duplicate: true,
                    conflict: ! hash_equals($sale->payload_hash, $payloadHash),
                );
            }

            $sale->items()->createMany(
                collect($payload['items'])->map(
                    fn (array $item, int $position): array => [
                        'position' => $position,
                        'product_id' => $item['product_id'],
                        'name' => $item['name'] ?? null,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'discount' => $item['discount'],
                        'modifier' => $item['modifier'],
                        'compound_id' => $item['compound_id'] ?? null,
                        'compound_main' => $item['compound_main'],
                    ],
                )->all(),
            );

            $sale->payments()->createMany(
                collect($payload['payments'])->map(
                    fn (array $payment, int $position): array => [
                        'position' => $position,
                        'method' => $payment['method'],
                        'amount' => $payment['amount'],
                        'tip' => $payment['tip'],
                        'reference' => $payment['reference'] ?? null,
                    ],
                )->all(),
            );

            return new RecordSaleResult($sale, duplicate: false, conflict: false);
        }, attempts: 3);
    }
}
