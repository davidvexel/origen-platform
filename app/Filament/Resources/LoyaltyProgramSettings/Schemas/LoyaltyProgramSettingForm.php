<?php

namespace App\Filament\Resources\LoyaltyProgramSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LoyaltyProgramSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Acumulación')->schema([
                    TextInput::make('cashback_percent')->label('Cashback')->numeric()->suffix('%')
                        ->minValue(0)->maxValue(100)->step(0.01)->required(),
                    TextInput::make('point_value_mxn')->label('Valor de 1 punto')->numeric()->prefix('$')->suffix('MXN')
                        ->minValue(0.0001)->step(0.01)->required(),
                    TextInput::make('expiration_months')->label('Vencimiento')->numeric()->suffix('meses')
                        ->minValue(1)->maxValue(120)->required(),
                    Toggle::make('tips_earn_points')->label('La propina genera puntos'),
                    Toggle::make('discounted_sales_earn_points')->label('Ventas con descuento generan puntos'),
                ])->columns(2),
                Section::make('Redención')->schema([
                    TextInput::make('minimum_redemption_points')->label('Redención mínima')->numeric()->suffix('puntos')
                        ->minValue(0.01)->step(0.01)->required(),
                    TextInput::make('maximum_redemption_percent')->label('Máximo de la compra cubierto')->numeric()->suffix('%')
                        ->minValue(1)->maxValue(100)->step(0.01)->required(),
                    Toggle::make('active')->label('Programa activo')->required(),
                ])->columns(2),
            ]);
    }
}
