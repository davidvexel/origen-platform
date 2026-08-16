<?php

namespace App\Filament\Resources\Sales\Tables;

use App\Domain\Sales\Models\Sale;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticket')->label('Ticket')->searchable()->sortable(),
                TextColumn::make('closed_at')->label('Cierre')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('station')->label('Estación')->searchable()->toggleable(),
                TextColumn::make('customer_name')->label('Cliente')->searchable()->placeholder('Sin cliente'),
                TextColumn::make('total')->label('Total')->money('MXN')->sortable(),
                TextColumn::make('tip')->label('Propina')->money('MXN')->toggleable(),
                IconColumn::make('loyalty_customer_id')->label('Loyalty')->boolean(),
            ])
            ->filters([
                SelectFilter::make('location_id')
                    ->label('Sucursal')
                    ->options(fn (): array => Sale::query()
                        ->distinct()->pluck('location_id', 'location_id')->all()),
            ])
            ->defaultSort('closed_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
