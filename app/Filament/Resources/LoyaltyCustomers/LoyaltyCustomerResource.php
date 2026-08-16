<?php

namespace App\Filament\Resources\LoyaltyCustomers;

use App\Domain\Loyalty\Models\LoyaltyCustomer;
use App\Filament\Resources\LoyaltyCustomers\Pages\CreateLoyaltyCustomer;
use App\Filament\Resources\LoyaltyCustomers\Pages\EditLoyaltyCustomer;
use App\Filament\Resources\LoyaltyCustomers\Pages\ListLoyaltyCustomers;
use App\Filament\Resources\LoyaltyCustomers\Pages\ViewLoyaltyCustomer;
use App\Filament\Resources\LoyaltyCustomers\Schemas\LoyaltyCustomerForm;
use App\Filament\Resources\LoyaltyCustomers\Schemas\LoyaltyCustomerInfolist;
use App\Filament\Resources\LoyaltyCustomers\Tables\LoyaltyCustomersTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LoyaltyCustomerResource extends Resource
{
    protected static ?string $model = LoyaltyCustomer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Clientes Loyalty';

    protected static ?string $modelLabel = 'cliente Loyalty';

    protected static ?string $pluralModelLabel = 'clientes Loyalty';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        $count = LoyaltyCustomer::query()->where('sr_sync_status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return LoyaltyCustomerForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LoyaltyCustomerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoyaltyCustomersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoyaltyCustomers::route('/'),
            'create' => CreateLoyaltyCustomer::route('/create'),
            'view' => ViewLoyaltyCustomer::route('/{record}'),
            'edit' => EditLoyaltyCustomer::route('/{record}/edit'),
        ];
    }
}
