<?php

namespace App\Filament\Resources\LoyaltyCustomers\Pages;

use App\Filament\Resources\LoyaltyCustomers\LoyaltyCustomerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLoyaltyCustomers extends ListRecords
{
    protected static string $resource = LoyaltyCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
