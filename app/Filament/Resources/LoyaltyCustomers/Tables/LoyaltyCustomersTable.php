<?php

namespace App\Filament\Resources\LoyaltyCustomers\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LoyaltyCustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('customer_type')->label('Tipo')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                    'person' => 'Persona', 'channel' => 'Canal', 'business' => 'Empresa', default => $state,
                }),
                TextColumn::make('phone')->label('Teléfono')->searchable()->copyable()->placeholder('—'),
                TextColumn::make('email')->label('Correo')->searchable()->copyable()->placeholder('—'),
                TextColumn::make('external_id')->label('Clave SR')->searchable()->copyable()->placeholder('Pendiente'),
                TextColumn::make('points_balance')->label('Puntos')->numeric(decimalPlaces: 2)->sortable(),
                IconColumn::make('rewards_enabled')->label('Recompensas')->boolean(),
                TextColumn::make('sr_sync_status')->label('Estado SR')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                    'pending' => 'Pendiente', 'synced' => 'Sincronizado', default => 'Revisar',
                })->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning', 'synced' => 'success', default => 'danger',
                }),
            ])
            ->filters([
                SelectFilter::make('sr_sync_status')->label('Estado SR')->options([
                    'pending' => 'Pendiente', 'synced' => 'Sincronizado', 'failed' => 'Revisar',
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
