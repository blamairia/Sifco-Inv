<?php

namespace App\Filament\Resources\RollAdjustments;

use App\Filament\Resources\RollAdjustments\Pages\CreateRollAdjustment;
use App\Filament\Resources\RollAdjustments\Pages\ListRollAdjustments;
use App\Filament\Resources\RollAdjustments\Schemas\RollAdjustmentForm;
use App\Filament\Resources\RollAdjustments\Tables\RollAdjustmentsTable;
use App\Models\RollAdjustment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RollAdjustmentResource extends Resource
{
    protected static ?string $model = RollAdjustment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|\UnitEnum|null $navigationGroup = 'Gestion du Stock';

    protected static ?int $navigationSort = 7;

    public static function getNavigationLabel(): string
    {
        return 'Ajustements Bobines';
    }

    public static function getModelLabel(): string
    {
        return 'Ajustement de Bobine';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Ajustements de Bobines';
    }

    public static function form(Schema $schema): Schema
    {
        return RollAdjustmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RollAdjustmentsTable::configure($table);
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
            'index' => ListRollAdjustments::route('/'),
            'create' => CreateRollAdjustment::route('/create'),
        ];
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canForceDelete($record): bool
    {
        return false;
    }

    public static function canRestore($record): bool
    {
        return false;
    }
}
