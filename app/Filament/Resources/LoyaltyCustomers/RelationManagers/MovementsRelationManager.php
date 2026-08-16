<?php

namespace App\Filament\Resources\LoyaltyCustomers\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'movements';

    protected static ?string $title = 'Movimientos de puntos';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('type')->label('Tipo')->formatStateUsing(fn (string $state): string => match ($state) {
                    'earn' => 'Acumulación', 'redeem' => 'Redención', 'expire' => 'Vencimiento', default => $state,
                }),
                TextEntry::make('points')->label('Puntos')->numeric(decimalPlaces: 2),
                TextEntry::make('balance_before')->label('Saldo anterior')->numeric(decimalPlaces: 2),
                TextEntry::make('balance_after')->label('Saldo posterior')->numeric(decimalPlaces: 2),
                TextEntry::make('reference')->label('Referencia')->placeholder('—'),
                TextEntry::make('occurred_at')->label('Fecha')->dateTime('d/m/Y H:i'),
                TextEntry::make('expires_at')->label('Vence')->dateTime('d/m/Y H:i')->placeholder('—'),
                TextEntry::make('notes')->label('Notas')->placeholder('—'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('type')
                    ->label('Tipo')->badge()->formatStateUsing(fn (string $state): string => match ($state) {
                        'earn' => 'Acumulación', 'redeem' => 'Redención', 'expire' => 'Vencimiento', default => $state,
                    })->color(fn (string $state): string => match ($state) {
                        'earn' => 'success', 'redeem' => 'warning', 'expire' => 'danger', default => 'gray',
                    }),
                TextColumn::make('points')->label('Puntos')->numeric(decimalPlaces: 2),
                TextColumn::make('balance_after')->label('Saldo')->numeric(decimalPlaces: 2),
                TextColumn::make('sale.ticket')->label('Ticket')->placeholder('—'),
                TextColumn::make('reference')->label('Referencia')->placeholder('—'),
                TextColumn::make('occurred_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('expires_at')->label('Vence')->date('d/m/Y')->placeholder('—'),
            ])
            ->filters([
                //
            ])
            ->defaultSort('occurred_at', 'desc')
            ->headerActions([])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
