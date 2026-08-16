<?php

namespace App\Filament\Resources\LoyaltyCustomers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LoyaltyCustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del cliente')->schema([
                    TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                    Select::make('customer_type')->label('Tipo de cliente')->options([
                        'person' => 'Persona',
                        'channel' => 'Canal / agregador',
                        'business' => 'Empresa',
                    ])->default('person')->required()
                        ->disabled(fn (): bool => ! (auth()->user()?->isAdmin() ?? false)),
                    TextInput::make('phone')->label('Teléfono')->tel()->maxLength(30),
                    TextInput::make('email')->label('Correo')->email()->maxLength(255),
                    DatePicker::make('birthday')->label('Cumpleaños'),
                    Select::make('status')->label('Estado')->options([
                        'active' => 'Activo',
                        'inactive' => 'Inactivo',
                    ])->default('active')->required(),
                    Select::make('rewards_enabled')->label('Participa en recompensas')->options([
                        1 => 'Sí',
                        0 => 'No',
                    ])->default(1)->required()
                        ->disabled(fn (): bool => ! (auth()->user()?->isAdmin() ?? false)),
                ])->columns(2),
                Section::make('Sincronización manual con SoftRestaurant')->schema([
                    TextInput::make('external_id')->label('Clave en SoftRestaurant')->maxLength(100)
                        ->helperText('Déjalo vacío hasta que el cliente sea capturado manualmente en SR.'),
                    Select::make('sr_sync_status')->label('Estado SR')->options([
                        'pending' => 'Pendiente',
                        'synced' => 'Sincronizado',
                        'failed' => 'Revisar',
                    ])->default('pending')->required(),
                    Textarea::make('sr_sync_notes')->label('Notas')->columnSpanFull(),
                ])->columns(2),
            ]);
    }
}
