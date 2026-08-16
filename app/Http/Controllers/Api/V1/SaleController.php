<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Integrations\Models\ApiClient;
use App\Domain\Sales\Actions\RecordSale;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use Illuminate\Http\JsonResponse;

class SaleController extends Controller
{
    public function __invoke(StoreSaleRequest $request, RecordSale $recordSale): JsonResponse
    {
        /** @var ApiClient $client */
        $client = $request->attributes->get('connectorClient');
        $result = $recordSale->execute($client, $request->validated());

        if ($result->conflict) {
            return response()->json([
                'message' => 'A sale with this identity already exists with different data.',
                'sale_id' => $result->sale->id,
                'ticket' => $result->sale->ticket,
            ], 409);
        }

        return response()->json([
            'sale_id' => $result->sale->id,
            'ticket' => $result->sale->ticket,
            'duplicate' => $result->duplicate,
        ], $result->duplicate ? 200 : 201);
    }
}
