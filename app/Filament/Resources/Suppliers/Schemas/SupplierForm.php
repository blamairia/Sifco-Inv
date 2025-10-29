<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom du Fournisseur')
                    ->required()
                    ->maxLength(255),
                TextInput::make('contact_person')
                    ->label('Personne Contact')
                    ->maxLength(255)
                    ->nullable(),
                TextInput::make('phone')
                    ->label('Téléphone')
                    ->tel()
                    ->nullable(),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->nullable(),
            ]);
    }
}
