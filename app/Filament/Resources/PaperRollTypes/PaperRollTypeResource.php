<?php

namespace App\Filament\Resources\PaperRollTypes;

use App\Filament\Resources\PaperRollTypes\Pages\CreatePaperRollType;
use App\Filament\Resources\PaperRollTypes\Pages\EditPaperRollType;
use App\Filament\Resources\PaperRollTypes\Pages\ListPaperRollTypes;
use App\Filament\Resources\PaperRollTypes\Schemas\PaperRollTypeForm;
use App\Filament\Resources\PaperRollTypes\Tables\PaperRollTypesTable;
use App\Models\PaperRollType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaperRollTypeResource extends Resource
{
    protected static ?string $model = PaperRollType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Types de Rouleau';

    public static function form(Schema $schema): Schema
    {
        return PaperRollTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaperRollTypesTable::configure($table);
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
            'index' => ListPaperRollTypes::route('/'),
            'create' => CreatePaperRollType::route('/create'),
            'edit' => EditPaperRollType::route('/{record}/edit'),
        ];
    }
}
