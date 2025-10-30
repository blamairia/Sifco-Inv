<?php

namespace App\Filament\Resources\Warehouses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom du magasin')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                
                Toggle::make('is_system')
                    ->label('Magasin système')
                    ->helperText('Les magasins système ne peuvent pas être supprimés')
                    ->default(false),
            ]);
    }
}
