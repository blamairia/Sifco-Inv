<?php

namespace App\Filament\Resources\BonReintegrations;

use App\Filament\Resources\BonReintegrations\Pages\CreateBonReintegration;
use App\Filament\Resources\BonReintegrations\Pages\EditBonReintegration;
use App\Filament\Resources\BonReintegrations\Pages\ListBonReintegrations;
use App\Filament\Resources\BonReintegrations\Schemas\BonReintegrationForm;
use App\Filament\Resources\BonReintegrations\Tables\BonReintegrationsTable;
use App\Models\BonReintegration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class BonReintegrationResource extends Resource
{
    protected static ?string $model = BonReintegration::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-uturn-left';

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion des Bons';

    protected static ?string $navigationLabel = "Bons de Réintégration";

    protected static ?string $modelLabel = "Bon de Réintégration";

    protected static ?string $pluralModelLabel = "Bons de Réintégration";

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return BonReintegrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BonReintegrationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBonReintegrations::route('/'),
            'create' => CreateBonReintegration::route('/create'),
            'edit' => EditBonReintegration::route('/{record}/edit'),
        ];
    }
}
