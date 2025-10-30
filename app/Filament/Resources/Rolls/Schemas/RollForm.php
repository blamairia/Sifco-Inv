<?php

namespace App\Filament\Resources\Rolls\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Produit (Bobines uniquement)')
                    ->relationship('product', 'name', fn ($query) => $query->where('is_roll', true))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Seuls les produits marqués comme bobines sont disponibles'),
                
                Select::make('warehouse_id')
                    ->label('Magasin')
                    ->relationship('warehouse', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                TextInput::make('ean_13')
                    ->label('Code EAN-13')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(13)
                    ->minLength(13)
                    ->helperText('13 caractères numériques'),
                
                TextInput::make('batch_number')
                    ->label('Numéro de Lot')
                    ->maxLength(255),
                
                \Filament\Forms\Components\DatePicker::make('received_date')
                    ->label('Date de Réception')
                    ->required()
                    ->default(now()),
                
                Select::make('status')
                    ->label('Statut')
                    ->options([
                        'in_stock' => 'En stock',
                        'reserved' => 'Réservé',
                        'consumed' => 'Consommé',
                        'damaged' => 'Endommagé',
                        'archived' => 'Archivé',
                    ])
                    ->required()
                    ->default('in_stock'),
                
                \Filament\Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->maxLength(65535),
            ]);
    }
}
