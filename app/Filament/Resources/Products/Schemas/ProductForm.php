<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations Générales')
                    ->schema([
                        TextInput::make('code')
                            ->label('Code Produit')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20),

                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),

                        Select::make('type')
                            ->label('Famille Produit')
                            ->options(Product::typeOptions())
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state === Product::FORM_TYPE_PAPIER_ROLL && ! $get('is_roll')) {
                                    $set('is_roll', true);
                                }
                            }),

                        Select::make('product_type')
                            ->label('Type Logique')
                            ->options(Product::productTypeOptions())
                            ->default(Product::TYPE_RAW_MATERIAL)
                            ->required()
                            ->reactive(),

                        Select::make('unit_id')
                            ->label('Unité de Mesure')
                            ->relationship('unit', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),

                        Toggle::make('is_roll')
                            ->label('Est une Bobine')
                            ->helperText('Pilote les workflows associés aux bobines (bons d\'entrée/sortie).')
                            ->default(false)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state && $get('type') !== Product::FORM_TYPE_PAPIER_ROLL) {
                                    $set('type', Product::FORM_TYPE_PAPIER_ROLL);
                                }
                            }),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Select::make('categories')
                            ->label('Catégories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $primary = $get('primary_category_id');

                                if ($primary && ! in_array((int) $primary, array_map('intval', $state ?? []), true)) {
                                    $set('primary_category_id', null);
                                }
                            })
                            ->columnSpanFull(),

                        Select::make('primary_category_id')
                            ->label('Catégorie Principale')
                            ->options(fn () => Category::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->reactive()
                            ->afterStateHydrated(function ($state, callable $set, ?Product $record) {
                                $set('primary_category_id', $record?->category?->id);
                            })
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (! $state) {
                                    return;
                                }

                                $categories = array_map('intval', $get('categories') ?? []);
                                $stateId = (int) $state;

                                if (! in_array($stateId, $categories, true)) {
                                    $categories[] = $stateId;
                                    $set('categories', array_values(array_unique($categories)));
                                }
                            })
                            ->helperText('Utilisée comme catégorie par défaut dans les rapports et listes.')
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                
                Section::make('Attributs Papier')
                    ->schema([
                        TextInput::make('grammage')
                            ->label('Grammage (g/m²)')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('GSM - Pour papiers en bobine'),
                        
                        TextInput::make('laize')
                            ->label('Laize (mm)')
                            ->numeric()
                            ->minValue(0)
                            ->helperText('Largeur en millimètres'),
                        
                        TextInput::make('flute')
                            ->label('Type de Cannelure')
                            ->maxLength(10)
                            ->helperText('E, B, C, BC, etc.'),
                        
                        TextInput::make('type_papier')
                            ->label('Type de Papier')
                            ->maxLength(50)
                            ->helperText('Kraftliner, Test, Recyclé, etc.'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(fn (callable $get) => ! $get('is_roll'))
                    ->visible(fn (callable $get) => (bool) $get('is_roll')),

                Section::make('Dimensions Feuille / Palette')
                    ->schema([
                        TextInput::make('sheet_width_mm')
                            ->label('Largeur (mm)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('Largeur palette ou feuille en millimètres.'),

                        TextInput::make('sheet_length_mm')
                            ->label('Longueur (mm)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('Longueur palette ou feuille en millimètres.'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->visible(fn (callable $get) => ! $get('is_roll')),
                
                Section::make('Attributs Supplémentaires (JSON)')
                    ->schema([
                        KeyValue::make('extra_attributes')
                            ->label('Attributs Extra')
                            ->keyLabel('Attribut')
                            ->valueLabel('Valeur')
                            ->addActionLabel('Ajouter Attribut')
                            ->helperText('Ex: thickness_mm, resistance, color, etc.'),
                    ])
                    ->collapsible()
                    ->collapsed(),
                
                Section::make('Gestion Stock')
                    ->schema([
                        TextInput::make('min_stock')
                            ->label('Stock Minimum')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        
                        TextInput::make('safety_stock')
                            ->label('Stock de Sécurité')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(2),
            ]);
    }
}
