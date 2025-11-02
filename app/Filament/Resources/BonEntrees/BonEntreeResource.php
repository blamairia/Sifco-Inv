<?php

namespace App\Filament\Resources\BonEntrees;

use App\Filament\Resources\BonEntrees\Pages\CreateBonEntree;
use App\Filament\Resources\BonEntrees\Pages\EditBonEntree;
use App\Filament\Resources\BonEntrees\Pages\ListBonEntrees;
use App\Filament\Resources\BonEntrees\Schemas\BonEntreeForm;
use App\Filament\Resources\BonEntrees\Tables\BonEntreesTable;
use App\Models\BonEntree;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BonEntreeResource extends Resource
{
    protected static ?string $model = BonEntree::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowDownCircle;

    protected static ?string $navigationLabel = 'Bons d\'Entrée';

    protected static ?string $modelLabel = 'Bon d\'Entrée';

    protected static ?string $pluralModelLabel = 'Bons d\'Entrée';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return BonEntreeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BonEntreesTable::configure($table);
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
            'index' => ListBonEntrees::route('/'),
            'create' => CreateBonEntree::route('/create'),
            'edit' => EditBonEntree::route('/{record}/edit'),
        ];
    }
}
