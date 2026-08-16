<?php

namespace App\Filament\Pages;

use App\Domain\Loyalty\Models\LoyaltyCustomer;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ScanLoyaltyCard extends Page
{
    protected string $view = 'filament.pages.scan-loyalty-card';

    protected static ?string $navigationLabel = 'Escanear tarjeta';

    protected static ?string $title = 'Escanear tarjeta de lealtad';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    public string $code = '';

    public function identify(): void
    {
        $externalId = strtoupper(trim($this->code));
        $customer = LoyaltyCustomer::query()
            ->where('external_id', $externalId)
            ->where('status', 'active')
            ->first();

        if ($customer === null) {
            Notification::make()->title('No encontramos una tarjeta activa con esa Clave SR')->danger()->send();

            return;
        }

        $customer->credential?->update(['last_used_at' => now()]);
        $this->redirect(route('filament.admin.pages.redeem-points', ['customer' => $externalId]));
    }
}
