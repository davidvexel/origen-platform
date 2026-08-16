<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Venta')->schema([
                    TextEntry::make('ticket')->label('Ticket'),
                    TextEntry::make('folio')->label('Folio de turno'),
                    TextEntry::make('location_id')->label('Sucursal'),
                    TextEntry::make('station')->label('Estación')->placeholder('—'),
                    TextEntry::make('opened_at')->label('Apertura')->dateTime('d/m/Y H:i:s'),
                    TextEntry::make('closed_at')->label('Cierre')->dateTime('d/m/Y H:i:s'),
                    TextEntry::make('total')->label('Total')->money('MXN'),
                    TextEntry::make('tip')->label('Propina')->money('MXN'),
                ])->columns(4),
                Section::make('Cliente')->schema([
                    TextEntry::make('customer_external_id')->label('Clave SR')->placeholder('Sin cliente'),
                    TextEntry::make('customer_name')->label('Nombre')->placeholder('Sin cliente'),
                    TextEntry::make('loyaltyCustomer.name')->label('Cliente Loyalty')->placeholder('No vinculado'),
                ])->columns(3),
                RepeatableEntry::make('items')->label('Productos')->schema([
                    TextEntry::make('product_id')->label('Producto'),
                    TextEntry::make('name')->label('Descripción'),
                    TextEntry::make('quantity')->label('Cantidad'),
                    TextEntry::make('unit_price')->label('Precio')->money('MXN'),
                    TextEntry::make('modifier')->label('Modificador')->formatStateUsing(fn (bool $state): string => $state ? 'Sí' : 'No'),
                ])->columns(5),
                RepeatableEntry::make('payments')->label('Pagos')->schema([
                    TextEntry::make('method')->label('Método'),
                    TextEntry::make('amount')->label('Importe')->money('MXN'),
                    TextEntry::make('tip')->label('Propina')->money('MXN'),
                    TextEntry::make('reference')->label('Referencia')->placeholder('—'),
                ])->columns(4),
            ]);
    }
}
