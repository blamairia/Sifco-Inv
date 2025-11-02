<?php

namespace App\Filament\Resources\StockMovements;

use App\Filament\Resources\StockMovements\Pages;
use App\Filament\Resources\StockMovements\Tables\StockMovementsTable;
use App\Models\StockMovement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;
    
    protected static ?string $navigationLabel = 'Mouvements Stock';
    
    protected static ?string $modelLabel = 'Mouvement';
    
    protected static ?string $pluralModelLabel = 'Mouvements';
    
    protected static ?int $navigationSort = 11;
    
    protected static string|UnitEnum|null $navigationGroup = 'Inventaire';

    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return StockMovementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockMovements::route('/'),
            'view' => Pages\ViewStockMovement::route('/{record}'),
        ];
    }
    
    public static function canCreate(): bool
    {
        return false; // Read-only audit log
    }
    
    public static function canEdit($record): bool
    {
        return false;
    }
    
    public static function canDelete($record): bool
    {
        return false;
    }
}
