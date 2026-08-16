<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Integrations\Models\ApiClient;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConnectorStatusController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var ApiClient $client */
        $client = $request->attributes->get('connectorClient');

        return response()->json([
            'status' => 'ok',
            'client' => $client->name,
            'location_id' => $client->location_id,
        ]);
    }
}
