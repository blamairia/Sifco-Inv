<?php

namespace App\Filament\Resources\StockQuantities;

use App\Filament\Resources\StockQuantities\Pages;
use App\Filament\Resources\StockQuantities\Tables\StockQuantitiesTable;
use App\Models\StockQuantity;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class StockQuantityResource extends Resource
{
    protected static ?string $model = StockQuantity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;
    
    protected static ?string $navigationLabel = 'Stock Actuel';
    
    protected static ?string $modelLabel = 'Stock';
    
    protected static ?string $pluralModelLabel = 'Stocks';
    
    protected static ?int $navigationSort = 10;
    
    protected static string|UnitEnum|null $navigationGroup = 'Inventaire';

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return StockQuantitiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockQuantities::route('/'),
        ];
    }
    
    public static function canCreate(): bool
    {
        return false; // Read-only resource
    }
}
