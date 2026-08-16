<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nombre')->required()->maxLength(255),
                TextInput::make('email')->label('Correo')->email()->required()->unique(ignoreRecord: true),
                TextInput::make('password')->label('Contraseña')->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->minLength(8),
                Select::make('role')->label('Rol')->options([
                    'admin' => 'Administrador',
                    'cashier' => 'Cajero',
                ])->required()->default('cashier'),
                Select::make('active')->label('Acceso')->options([
                    1 => 'Activo',
                    0 => 'Desactivado',
                ])->required()->default(1),
            ]);
    }
}
