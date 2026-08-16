<?php

namespace App\Filament\Resources\LoyaltyProgramSettings;

use App\Domain\Loyalty\Models\LoyaltyProgramSetting;
use App\Filament\Resources\LoyaltyProgramSettings\Pages\EditLoyaltyProgramSetting;
use App\Filament\Resources\LoyaltyProgramSettings\Pages\ListLoyaltyProgramSettings;
use App\Filament\Resources\LoyaltyProgramSettings\Schemas\LoyaltyProgramSettingForm;
use App\Filament\Resources\LoyaltyProgramSettings\Tables\LoyaltyProgramSettingsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LoyaltyProgramSettingResource extends Resource
{
    protected static ?string $model = LoyaltyProgramSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Reglas de recompensas';

    protected static ?string $modelLabel = 'reglas de recompensas';

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return LoyaltyProgramSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoyaltyProgramSettingsTable::configure($table);
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
            'index' => ListLoyaltyProgramSettings::route('/'),
            'edit' => EditLoyaltyProgramSetting::route('/{record}/edit'),
        ];
    }
}
