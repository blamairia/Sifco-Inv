<?php

namespace App\Filament\Resources\BonTransferts;

use App\Filament\Resources\BonTransferts\Pages\CreateBonTransfert;
use App\Filament\Resources\BonTransferts\Pages\EditBonTransfert;
use App\Filament\Resources\BonTransferts\Pages\ListBonTransferts;
use App\Filament\Resources\BonTransferts\Schemas\BonTransfertForm;
use App\Filament\Resources\BonTransferts\Tables\BonTransfertsTable;
use App\Models\BonTransfert;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BonTransfertResource extends Resource
{
    protected static ?string $model = BonTransfert::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BonTransfertForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BonTransfertsTable::configure($table);
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
            'index' => ListBonTransferts::route('/'),
            'create' => CreateBonTransfert::route('/create'),
            'edit' => EditBonTransfert::route('/{record}/edit'),
        ];
    }
}
