<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Select::make('type')
                    ->options(['papier_roll' => 'Papier roll', 'consommable' => 'Consommable', 'fini' => 'Fini'])
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('grammage')
                    ->numeric(),
                TextInput::make('laize')
                    ->numeric(),
                TextInput::make('flute'),
                TextInput::make('type_papier'),
                TextInput::make('extra_attributes'),
                TextInput::make('unit_id')
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('min_stock')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('safety_stock')
                    ->required()
                    ->numeric()
                    ->default(0.0),
            ]);
    }
}
