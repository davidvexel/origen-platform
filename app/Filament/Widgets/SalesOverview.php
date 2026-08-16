<?php

namespace App\Filament\Widgets;

use App\Domain\Loyalty\Models\LoyaltyCustomer;
use App\Domain\Sales\Models\Sale;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Ventas de hoy', Sale::query()->whereDate('closed_at', today())->count())
                ->description('Recibidas desde SoftRestaurant'),
            Stat::make(
                'Total de hoy',
                '$'.number_format((float) Sale::query()->whereDate('closed_at', today())->sum('total'), 2)
            )->description('Importe antes de propina'),
            Stat::make(
                'Pendientes en SR',
                LoyaltyCustomer::query()->where('sr_sync_status', 'pending')->count()
            )->description('Requieren captura manual'),
            Stat::make('Clientes Loyalty', LoyaltyCustomer::query()->where('status', 'active')->count())
                ->description('Clientes activos'),
        ];
    }
}
