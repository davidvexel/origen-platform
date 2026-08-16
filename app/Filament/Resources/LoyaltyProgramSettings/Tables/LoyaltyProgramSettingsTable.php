<?php

namespace App\Filament\Resources\LoyaltyProgramSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LoyaltyProgramSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('cashback_percent')->label('Cashback')->suffix('%'),
                TextColumn::make('point_value_mxn')->label('Valor por punto')->money('MXN'),
                TextColumn::make('minimum_redemption_points')->label('Mínimo')->suffix(' puntos'),
                TextColumn::make('expiration_months')->label('Vencimiento')->suffix(' meses'),
                TextColumn::make('maximum_redemption_percent')->label('Máximo')->suffix('%'),
                IconColumn::make('active')->label('Activo')->boolean(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
