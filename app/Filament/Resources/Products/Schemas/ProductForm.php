<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Models\Category;
use App\Models\Unit;
use App\Models\PaperRollType;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom du Produit')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label('Type')
                    ->options([
                        'papier_roll' => 'Papier Rouleau',
                        'consommable' => 'Consommable',
                        'fini' => 'Produit Fini',
                    ])
                    ->required(),
                Select::make('category_id')
                    ->label('Catégorie')
                    ->options(Category::all()->pluck('name', 'id'))
                    ->nullable(),
                Select::make('unit_id')
                    ->label('Unité')
                    ->options(Unit::all()->pluck('name', 'id'))
                    ->nullable(),
                Select::make('paper_roll_type_id')
                    ->label('Type de Rouleau')
                    ->options(PaperRollType::all()->pluck('name', 'id'))
                    ->nullable()
                    ->visible(fn ($get) => $get('type') === 'papier_roll'),
                TextInput::make('gsm')
                    ->label('GSM')
                    ->numeric()
                    ->nullable(),
                TextInput::make('flute')
                    ->label('Flute')
                    ->maxLength(255)
                    ->nullable(),
                TextInput::make('width')
                    ->label('Largeur (mm)')
                    ->numeric()
                    ->nullable(),
                TextInput::make('min_stock')
                    ->label('Stock Minimum')
                    ->numeric()
                    ->default(0),
                TextInput::make('safety_stock')
                    ->label('Stock de Sécurité')
                    ->numeric()
                    ->default(0),
                TextInput::make('avg_cost')
                    ->label('Coût Moyen (DZD)')
                    ->numeric()
                    ->disabled()
                    ->default(0),
            ]);
    }
}
