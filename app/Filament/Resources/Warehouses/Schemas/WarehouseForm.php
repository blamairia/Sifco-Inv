<?php

namespace App\Filament\Resources\Warehouses\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom de l\'Entrepôt')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Toggle::make('is_system')
                    ->label('Entrepôt Système')
                    ->disabled(fn ($record) => $record?->is_system),
            ]);
    }
}
