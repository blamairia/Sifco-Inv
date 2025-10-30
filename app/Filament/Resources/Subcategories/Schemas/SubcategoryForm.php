<?php

namespace App\Filament\Resources\Subcategories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubcategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Catégorie')
                    ->relationship('category', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                TextInput::make('name')
                    ->label('Nom de la sous-catégorie')
                    ->required()
                    ->maxLength(255),
                
                Textarea::make('description')
                    ->label('Description')
                    ->rows(3),
            ]);
    }
}
