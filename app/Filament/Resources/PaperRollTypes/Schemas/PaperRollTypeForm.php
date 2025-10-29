<?php

namespace App\Filament\Resources\PaperRollTypes\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;

class PaperRollTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('type_code')
                    ->label('Code Type (KL, TLB, TLM, FL)')
                    ->required()
                    ->maxLength(10)
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                TextInput::make('grammage')
                    ->label('Grammage (GSM)')
                    ->numeric()
                    ->required(),
                TextInput::make('laise')
                    ->label('Laise (mm)')
                    ->numeric()
                    ->required(),
                TextInput::make('weight')
                    ->label('Poids Total (kg)')
                    ->numeric()
                    ->required(),
                TextInput::make('description')
                    ->label('Description')
                    ->maxLength(1000)
                    ->nullable(),
            ]);
    }
}
