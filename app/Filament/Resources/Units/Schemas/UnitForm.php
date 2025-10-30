<?php

namespace App\Filament\Resources\Units\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom de l\'unité')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                
                TextInput::make('symbol')
                    ->label('Symbole')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                
                Textarea::make('description')
                    ->label('Description')
                    ->rows(3),
            ]);
    }
}
