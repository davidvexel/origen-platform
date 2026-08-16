<?php

namespace App\Filament\Pages;

use App\Domain\Loyalty\Models\LoyaltyCustomer;
use App\Domain\Sales\Models\Sale;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class PendingSrCustomers extends Page
{
    protected string $view = 'filament.pages.pending-sr-customers';

    protected static ?string $navigationLabel = 'Pendientes en SR';

    protected static ?string $title = 'Clientes pendientes en SoftRestaurant';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    #[Computed]
    public function customers()
    {
        return LoyaltyCustomer::query()
            ->where('sr_sync_status', 'pending')
            ->oldest()
            ->get();
    }

    public static function getNavigationBadge(): ?string
    {
        $count = LoyaltyCustomer::query()->where('sr_sync_status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public function markSynced(int $customerId): void
    {
        $customer = LoyaltyCustomer::query()
            ->where('sr_sync_status', 'pending')
            ->findOrFail($customerId);

        $externalId = $customer->external_id;
        DB::transaction(function () use ($customer, $externalId): void {
            $customer->update([
                'external_id' => $externalId,
                'sr_sync_status' => 'synced',
                'sr_synced_at' => now(),
            ]);

            Sale::query()
                ->where('customer_external_id', $externalId)
                ->update(['loyalty_customer_id' => $customer->id]);
        });

        unset($this->customers);
        Notification::make()->title('Cliente marcado como sincronizado')->success()->send();
    }
}
