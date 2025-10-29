<?php

namespace App\Filament\Resources\Rolls\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\Product;
use App\Models\Warehouse;

class RollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Produit')
                    ->options(Product::where('type', 'papier_roll')->pluck('name', 'id'))
                    ->required(),
                Select::make('warehouse_id')
                    ->label('Entrepôt')
                    ->options(Warehouse::all()->pluck('name', 'id'))
                    ->required(),
                TextInput::make('ean_13')
                    ->label('EAN-13')
                    ->required()
                    ->maxLength(13)
                    ->unique(ignoreRecord: true),
                TextInput::make('qty')
                    ->label('Quantité')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                Select::make('status')
                    ->label('Statut')
                    ->options([
                        'in_stock' => 'En Stock',
                        'consumed' => 'Consommé',
                    ])
                    ->default('in_stock'),
            ]);
    }
}
