<?php

namespace App\Http\Middleware;

use App\Domain\Integrations\Models\ApiClient;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateConnectorToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! is_string($token) || ! str_starts_with($token, 'orp_')) {
            return $this->unauthorized();
        }

        $client = ApiClient::query()
            ->where('token_hash', hash('sha256', $token))
            ->where('active', true)
            ->first();

        if ($client === null) {
            return $this->unauthorized();
        }

        $client->forceFill(['last_used_at' => now()])->saveQuietly();
        $request->attributes->set('connectorClient', $client);

        return $next($request);
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json([
            'message' => 'Invalid or missing API token.',
        ], 401);
    }
}
