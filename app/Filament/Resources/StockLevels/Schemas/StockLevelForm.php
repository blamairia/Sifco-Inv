<?php

namespace App\Filament\Resources\StockLevels\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\Product;
use App\Models\Warehouse;

class StockLevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Produit')
                    ->options(Product::all()->pluck('name', 'id'))
                    ->required(),
                Select::make('warehouse_id')
                    ->label('Entrepôt')
                    ->options(Warehouse::all()->pluck('name', 'id'))
                    ->required(),
                TextInput::make('qty')
                    ->label('Quantité')
                    ->numeric()
                    ->required()
                    ->minValue(0),
            ]);
    }
}
