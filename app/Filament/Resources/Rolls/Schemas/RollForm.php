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
                    ->label('Produit')
                    ->relationship('product', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                
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
                    ->minLength(13),
                
                TextInput::make('qty')
                    ->label('Quantité')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
                
                Select::make('status')
                    ->label('Statut')
                    ->options([
                        'in_stock' => 'En stock',
                        'consumed' => 'Consommé',
                    ])
                    ->required()
                    ->default('in_stock'),
            ]);
    }
}
