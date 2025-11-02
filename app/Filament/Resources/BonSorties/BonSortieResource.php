<?php

namespace App\Filament\Resources\BonSorties;

use App\Filament\Resources\BonSorties\Pages\CreateBonSortie;
use App\Filament\Resources\BonSorties\Pages\EditBonSortie;
use App\Filament\Resources\BonSorties\Pages\ListBonSorties;
use App\Filament\Resources\BonSorties\Schemas\BonSortieForm;
use App\Filament\Resources\BonSorties\Tables\BonSortiesTable;
use App\Models\BonSortie;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BonSortieResource extends Resource
{
    protected static ?string $model = BonSortie::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpCircle;

    protected static ?string $navigationLabel = 'Bons de Sortie';

    protected static ?string $modelLabel = 'Bon de Sortie';

    protected static ?string $pluralModelLabel = 'Bons de Sortie';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return BonSortieForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BonSortiesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBonSorties::route('/'),
            'create' => CreateBonSortie::route('/create'),
            'edit' => EditBonSortie::route('/{record}/edit'),
        ];
    }
}
