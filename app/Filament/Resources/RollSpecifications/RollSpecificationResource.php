<?php

namespace App\Filament\Resources\RollSpecifications;

use App\Filament\Resources\RollSpecifications\Pages\CreateRollSpecification;
use App\Filament\Resources\RollSpecifications\Pages\EditRollSpecification;
use App\Filament\Resources\RollSpecifications\Pages\ListRollSpecifications;
use App\Filament\Resources\RollSpecifications\Schemas\RollSpecificationForm;
use App\Filament\Resources\RollSpecifications\Tables\RollSpecificationsTable;
use App\Models\RollSpecification;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RollSpecificationResource extends Resource
{
    protected static ?string $model = RollSpecification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return RollSpecificationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RollSpecificationsTable::configure($table);
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
            'index' => ListRollSpecifications::route('/'),
            'create' => CreateRollSpecification::route('/create'),
            'edit' => EditRollSpecification::route('/{record}/edit'),
        ];
    }
}
