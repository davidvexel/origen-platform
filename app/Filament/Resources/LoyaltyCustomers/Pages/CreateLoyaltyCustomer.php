<?php

namespace App\Filament\Resources\LoyaltyCustomers\Pages;

use App\Filament\Resources\LoyaltyCustomers\LoyaltyCustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLoyaltyCustomer extends CreateRecord
{
    protected static string $resource = LoyaltyCustomerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['registered_by'] = auth()->id();
        $data['sr_sync_status'] = 'pending';

        return $data;
    }
}
