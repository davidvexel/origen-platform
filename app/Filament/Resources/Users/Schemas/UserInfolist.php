<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label('Nombre'),
                TextEntry::make('email')->label('Correo'),
                TextEntry::make('role')->label('Rol')->formatStateUsing(
                    fn (string $state): string => $state === 'admin' ? 'Administrador' : 'Cajero'
                ),
                IconEntry::make('active')->label('Activo')->boolean(),
            ]);
    }
}
