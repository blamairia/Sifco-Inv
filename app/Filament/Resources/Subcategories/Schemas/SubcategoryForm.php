<?php

namespace App\Filament\Resources\Subcategories\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Models\Category;

class SubcategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Catégorie')
                    ->options(Category::all()->pluck('name', 'id'))
                    ->required(),
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                TextInput::make('description')
                    ->label('Description')
                    ->maxLength(1000)
                    ->nullable(),
            ]);
    }
}
