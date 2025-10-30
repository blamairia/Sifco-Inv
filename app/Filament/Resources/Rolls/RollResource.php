<?php

namespace App\Filament\Resources\Rolls;

use App\Filament\Resources\Rolls\Pages\CreateRoll;
use App\Filament\Resources\Rolls\Pages\EditRoll;
use App\Filament\Resources\Rolls\Pages\ListRolls;
use App\Filament\Resources\Rolls\Schemas\RollForm;
use App\Filament\Resources\Rolls\Tables\RollsTable;
use App\Models\Roll;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RollResource extends Resource
{
    protected static ?string $model = Roll::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Bobines';

    protected static ?string $modelLabel = 'Bobine';

    protected static ?string $pluralModelLabel = 'Bobines';

    public static function form(Schema $schema): Schema
    {
        return RollForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RollsTable::configure($table);
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
            'index' => ListRolls::route('/'),
            'create' => CreateRoll::route('/create'),
            'edit' => EditRoll::route('/{record}/edit'),
        ];
    }
}
