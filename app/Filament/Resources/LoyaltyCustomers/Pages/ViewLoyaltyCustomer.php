<?php

namespace App\Filament\Resources\LoyaltyCustomers\Pages;

use App\Filament\Resources\LoyaltyCustomers\LoyaltyCustomerResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewLoyaltyCustomer extends ViewRecord
{
    protected static string $resource = LoyaltyCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('loyaltyCard')
                ->label('Ver tarjeta y QR')
                ->icon(Heroicon::OutlinedQrCode)
                ->url(fn (): string => route('loyalty-card.admin', $this->record))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
