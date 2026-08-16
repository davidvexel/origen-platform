<?php

namespace App\Filament\Resources\LoyaltyCustomers\Pages;

use App\Filament\Resources\LoyaltyCustomers\LoyaltyCustomerResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLoyaltyCustomer extends EditRecord
{
    protected static string $resource = LoyaltyCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
