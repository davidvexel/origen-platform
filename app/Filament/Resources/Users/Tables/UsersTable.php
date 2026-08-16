<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('email')->label('Correo')->searchable(),
                TextColumn::make('role')->label('Rol')->badge()->formatStateUsing(
                    fn (string $state): string => $state === 'admin' ? 'Administrador' : 'Cajero'
                ),
                IconColumn::make('active')->label('Activo')->boolean(),
                TextColumn::make('created_at')->label('Creado')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
