<?php

namespace App\Filament\Pages;

use App\Domain\Loyalty\Actions\CancelRedemption;
use App\Domain\Loyalty\Actions\ExpireCustomerPoints;
use App\Domain\Loyalty\Actions\RedeemPoints as RedeemPointsAction;
use App\Domain\Loyalty\Models\LoyaltyCustomer;
use App\Domain\Loyalty\Models\LoyaltyProgramSetting;
use App\Domain\Loyalty\Models\LoyaltyRedemption;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;

class RedeemPoints extends Page
{
    protected string $view = 'filament.pages.redeem-points';

    protected static ?string $navigationLabel = 'Redimir puntos';

    protected static ?string $title = 'Redimir puntos';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    public string $search = '';

    public ?int $customerId = null;

    public string $points = '';

    public string $purchaseTotal = '';

    public string $srFolio = '';

    public string $reference = '';

    public string $notes = '';

    public ?int $lastRedemptionId = null;

    /** @return Collection<int, LoyaltyCustomer> */
    #[Computed]
    public function searchResults()
    {
        if (mb_strlen(trim($this->search)) < 2 || $this->customerId !== null) {
            return LoyaltyCustomer::query()->whereRaw('1 = 0')->get();
        }

        return LoyaltyCustomer::query()
            ->where('status', 'active')
            ->where('rewards_enabled', true)
            ->where(function ($query): void {
                $term = '%'.trim($this->search).'%';
                $query->where('name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('external_id', 'like', $term);
            })
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function customer(): ?LoyaltyCustomer
    {
        return $this->customerId === null ? null : LoyaltyCustomer::query()->find($this->customerId);
    }

    #[Computed]
    public function settings(): LoyaltyProgramSetting
    {
        return LoyaltyProgramSetting::current();
    }

    public function selectCustomer(int $customerId): void
    {
        $customer = LoyaltyCustomer::query()->where('status', 'active')->findOrFail($customerId);
        app(ExpireCustomerPoints::class)->execute($customer);
        $this->customerId = $customer->id;
        $this->search = $customer->name;
        unset($this->searchResults, $this->customer);
    }

    public function clearCustomer(): void
    {
        $this->reset(['customerId', 'search', 'points', 'purchaseTotal', 'srFolio', 'reference', 'notes', 'lastRedemptionId']);
        unset($this->searchResults, $this->customer);
    }

    public function redeem(RedeemPointsAction $action): void
    {
        $validated = $this->validate([
            'customerId' => ['required', 'integer', 'exists:loyalty_customers,id'],
            'points' => ['required', 'numeric', 'min:0.01'],
            'purchaseTotal' => ['required', 'numeric', 'min:0.01'],
            'srFolio' => ['required', 'integer', 'min:1'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $customer = LoyaltyCustomer::query()->findOrFail($validated['customerId']);
        $redemption = $action->execute(
            $customer,
            (string) $validated['points'],
            (string) $validated['purchaseTotal'],
            (int) $validated['srFolio'],
            (string) config('loyalty.location_id'),
            auth()->user(),
            $validated['reference'] ?: null,
            $validated['notes'] ?: null,
        );

        $this->lastRedemptionId = $redemption->id;
        unset($this->customer);
        Notification::make()->title('Puntos reservados; imprime el comprobante')->success()->send();
    }

    public function cancelLastRedemption(CancelRedemption $action): void
    {
        $redemption = LoyaltyRedemption::query()->findOrFail($this->lastRedemptionId);
        $action->execute($redemption, auth()->user(), 'cancelled_by_cashier');

        Notification::make()->title('Redención cancelada; los puntos fueron devueltos')->success()->send();
        $this->clearCustomer();
    }

    #[Computed]
    public function lastRedemption(): ?LoyaltyRedemption
    {
        return $this->lastRedemptionId === null
            ? null
            : LoyaltyRedemption::query()->with(['customer', 'cashier'])->find($this->lastRedemptionId);
    }
}
