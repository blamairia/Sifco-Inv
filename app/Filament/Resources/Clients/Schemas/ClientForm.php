<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Code client')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),

                TextInput::make('name')
                    ->label('Raison sociale')
                    ->required()
                    ->maxLength(255),

                TextInput::make('contact_person')
                    ->label('Contact principal')
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('Téléphone fixe')
                    ->tel()
                    ->maxLength(50),

                TextInput::make('mobile')
                    ->label('Téléphone mobile')
                    ->tel()
                    ->maxLength(50),

                TextInput::make('tax_number')
                    ->label('Identifiant fiscal')
                    ->maxLength(100),

                TextInput::make('address_line1')
                    ->label('Adresse (ligne 1)')
                    ->maxLength(255),

                TextInput::make('address_line2')
                    ->label('Adresse (ligne 2)')
                    ->maxLength(255),

                TextInput::make('city')
                    ->label('Ville')
                    ->maxLength(100),

                TextInput::make('country')
                    ->label('Pays')
                    ->maxLength(100),

                Toggle::make('is_active')
                    ->label('Client actif')
                    ->default(true),
            ]);
    }
}
