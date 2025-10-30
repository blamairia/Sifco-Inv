<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Code Fournisseur')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20)
                    ->helperText('Ex: SUPP-GPM-001'),
                
                TextInput::make('name')
                    ->label('Nom du fournisseur')
                    ->required()
                    ->maxLength(255),
                
                TextInput::make('contact_person')
                    ->label('Personne de contact')
                    ->maxLength(255),
                
                TextInput::make('phone')
                    ->label('Téléphone')
                    ->tel()
                    ->maxLength(255),
                
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255),
                
                \Filament\Forms\Components\Textarea::make('address')
                    ->label('Adresse')
                    ->rows(2)
                    ->maxLength(65535),
                
                TextInput::make('payment_terms')
                    ->label('Conditions de Paiement')
                    ->maxLength(255)
                    ->helperText('Ex: Net 30, Net 45'),
                
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->label('Actif')
                    ->default(true),
            ]);
    }
}
