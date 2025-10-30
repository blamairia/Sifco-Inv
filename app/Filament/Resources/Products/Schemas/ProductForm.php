<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Section::make('Informations Générales')
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
                            ->label('Type')
                            ->options([
                                'papier_roll' => 'Papier en Bobine',
                                'consommable' => 'Consommable',
                                'fini' => 'Produit Fini',
                            ])
                            ->required()
                            ->reactive(),
                        
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(65535),
                        
                        Select::make('unit_id')
                            ->label('Unité de Mesure')
                            ->relationship('unit', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        
                        Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                    ])
                    ->columns(2),
                
                \Filament\Forms\Components\Section::make('Attributs Papier')
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
                    ->collapsible(),
                
                \Filament\Forms\Components\Section::make('Attributs Supplémentaires (JSON)')
                    ->schema([
                        \Filament\Forms\Components\KeyValue::make('extra_attributes')
                            ->label('Attributs Extra')
                            ->keyLabel('Attribut')
                            ->valueLabel('Valeur')
                            ->addActionLabel('Ajouter Attribut')
                            ->helperText('Ex: thickness_mm, resistance, color, etc.'),
                    ])
                    ->collapsible()
                    ->collapsed(),
                
                \Filament\Forms\Components\Section::make('Gestion Stock')
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
