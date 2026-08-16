<?php

namespace App\Console\Commands;

use App\Domain\Integrations\Models\ApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateApiClient extends Command
{
    protected $signature = 'api-client:create {name} {location_id}';

    protected $description = 'Create a connector API client and display its token once';

    public function handle(): int
    {
        $token = 'orp_'.Str::random(64);

        $client = ApiClient::query()->create([
            'name' => $this->argument('name'),
            'location_id' => $this->argument('location_id'),
            'token_prefix' => substr($token, 0, 12),
            'token_hash' => hash('sha256', $token),
            'active' => true,
        ]);

        $this->info("API client {$client->id} created for {$client->location_id}.");
        $this->warn('Copy this token now. It will not be shown again:');
        $this->line($token);

        return self::SUCCESS;
    }
}
