<?php

namespace App\Filament\Resources\LoyaltyCustomers\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                TextColumn::make('phone')->label('Teléfono')->searchable()->copyable()->placeholder('—'),
                TextColumn::make('email')->label('Correo')->searchable()->copyable()->placeholder('—'),
                TextColumn::make('external_id')->label('Clave SR')->searchable()->copyable()->placeholder('Pendiente'),
                TextColumn::make('points_balance')->label('Puntos')->numeric(decimalPlaces: 2)->sortable(),
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
