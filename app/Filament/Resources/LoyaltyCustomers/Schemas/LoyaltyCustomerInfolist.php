<?php

namespace App\Filament\Resources\LoyaltyCustomers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LoyaltyCustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cliente')->schema([
                    TextEntry::make('name')->label('Nombre'),
                    TextEntry::make('customer_type')->label('Tipo')->formatStateUsing(fn (string $state): string => match ($state) {
                        'person' => 'Persona', 'channel' => 'Canal / agregador', 'business' => 'Empresa', default => $state,
                    }),
                    TextEntry::make('phone')->label('Teléfono')->placeholder('—')->copyable(),
                    TextEntry::make('email')->label('Correo')->placeholder('—')->copyable(),
                    TextEntry::make('birthday')->label('Cumpleaños')->date('d/m/Y')->placeholder('—'),
                    TextEntry::make('points_balance')->label('Puntos')->numeric(decimalPlaces: 2),
                    TextEntry::make('rewards_enabled')->label('Participa en recompensas')
                        ->formatStateUsing(fn (bool $state): string => $state ? 'Sí' : 'No'),
                    TextEntry::make('external_id')->label('Clave SR')->placeholder('Pendiente')->copyable(),
                    TextEntry::make('sr_sync_status')->label('Estado SR'),
                    TextEntry::make('sr_sync_notes')->label('Notas')->placeholder('—'),
                ])->columns(2),
            ]);
    }
}
