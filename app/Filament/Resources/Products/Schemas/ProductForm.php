<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Category;
use App\Models\Product;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::components());
    }

    public static function components(): array
    {
        return [
            Section::make('Informations Générales')
                ->columns(2)
                ->schema([
                    TextInput::make('code')
                        ->label('Code Produit')
                        ->required()
                        ->maxLength(50)
                        ->unique(Product::class, 'code', ignoreRecord: true),

                    TextInput::make('name')
                        ->label('Nom')
                        ->required()
                        ->maxLength(255),

                    Select::make('product_type')
                        ->label('Type Logique (Stade de Fabrication)')
                        ->options(Product::productTypeOptions())
                        ->default(Product::TYPE_RAW_MATERIAL)
                        ->required()
                        ->live()
                        ->helperText('Matière première, Semi-fini, ou Produit fini'),

                    Select::make('form_type')
                        ->label('Forme Physique')
                        ->options(Product::formTypeOptions())
                        ->default(Product::FORM_OTHER)
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                            // Auto-populate roll-specific fields visibility
                            if ($state === Product::FORM_ROLL && !$get('grammage')) {
                                // Could set default grammage or trigger validation
                            }
                        })
                        ->helperText('Bobine, Feuille, Consommable, ou Autre'),

                    Select::make('unit_id')
                        ->label('Unité de Mesure')
                        ->relationship('unit', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Toggle::make('is_active')
                        ->label('Actif')
                        ->default(true),

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
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get, ?array $state) {
                            $primary = $get('primary_category_id');

                            if ($primary && ! in_array((int) $primary, array_map('intval', $state ?? []), true)) {
                                $set('primary_category_id', null);
                            }
                        })
                        ->columnSpanFull(),

                    Select::make('primary_category_id')
                        ->label('Catégorie Principale')
                        ->options(fn (Get $get) => Category::query()
                            ->whereIn('id', array_map('intval', $get('categories') ?? []))
                            ->orderBy('name')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                            if (! $state) {
                                return;
                            }

                            $categories = array_map('intval', $get('categories') ?? []);

                            if (! in_array($state, $categories, true)) {
                                $categories[] = $state;
                                $set('categories', array_values(array_unique($categories)));
                            }
                        })
                        ->helperText('Utilisée comme catégorie par défaut dans les rapports et listes.')
                        ->columnSpan(1),
                ]),

            Section::make('Attributs Papier')
                ->columns(2)
                ->schema([
                    TextInput::make('grammage')
                        ->label('Grammage (g/m²)')
                        ->numeric()
                        ->minValue(0)
                        ->helperText('GSM - uniquement pour les papiers en bobine.'),

                    TextInput::make('laize')
                        ->label('Laize (mm)')
                        ->numeric()
                        ->minValue(0)
                        ->helperText('Largeur en millimètres.'),

                    TextInput::make('flute')
                        ->label('Type de Cannelure')
                        ->maxLength(10)
                        ->helperText('E, B, C, BC, etc.'),

                    TextInput::make('type_papier')
                        ->label('Type de Papier')
                        ->maxLength(50)
                        ->helperText('Kraftliner, Test, Recyclé, etc.'),
                ])
                ->collapsible()
                ->collapsed(fn (Get $get) => $get('form_type') !== Product::FORM_ROLL)
                ->visible(fn (Get $get) => $get('form_type') === Product::FORM_ROLL),

            Section::make('Dimensions Feuille / Palette')
                ->columns(2)
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
                ->collapsible()
                ->collapsed()
                ->visible(fn (Get $get) => $get('form_type') === Product::FORM_SHEET),

            Section::make('Attributs Supplémentaires (JSON)')
                ->schema([
                    KeyValue::make('extra_attributes')
                        ->label('Attributs Extra')
                        ->keyLabel('Attribut')
                        ->valueLabel('Valeur')
                        ->addActionLabel('Ajouter un attribut')
                        ->helperText('Exemple : thickness_mm, resistance, color, etc.'),
                ])
                ->collapsible()
                ->collapsed(),

            Section::make('Gestion Stock')
                ->columns(2)
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
                ]),
        ];
    }
}
