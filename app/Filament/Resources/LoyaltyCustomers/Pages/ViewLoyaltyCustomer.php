<?php

namespace App\Filament\Resources\LoyaltyCustomers\Pages;

use App\Filament\Resources\LoyaltyCustomers\LoyaltyCustomerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLoyaltyCustomer extends ViewRecord
{
    protected static string $resource = LoyaltyCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
