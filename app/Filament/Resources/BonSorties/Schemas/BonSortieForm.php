<?php

namespace App\Filament\Resources\BonSorties\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BonSortieForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations du Bon')
                    ->schema([
                        Placeholder::make('status_info')
                            ->label('Statut Actuel')
                            ->content(function ($record) {
                                if (!$record) return 'Nouveau';
                                
                                $badges = [
                                    'draft' => '🟡 Brouillon',
                                    'issued' => '🟠 Émis',
                                    'confirmed' => '🟢 Confirmé',
                                    'archived' => '⚫ Archivé',
                                ];
                                
                                return $badges[$record->status] ?? $record->status;
                            }),
                        
                        Placeholder::make('created_info')
                            ->label('Créé le')
                            ->content(fn ($record) => $record ? $record->created_at->format('d/m/Y H:i') : '-')
                            ->visible(fn ($record) => $record !== null),
                        
                        Placeholder::make('issued_info')
                            ->label('Émis le')
                            ->content(fn ($record) => $record && $record->issued_at 
                                ? $record->issued_at->format('d/m/Y H:i') 
                                : '-')
                            ->visible(fn ($record) => $record && in_array($record->status, ['issued', 'confirmed'])),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record !== null),
                
                Section::make('Informations Générales')
                    ->schema([
                        TextInput::make('bon_number')
                            ->label('N° Bon de Sortie')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn() => \App\Models\BonSortie::generateBonNumber())
                            ->disabled()
                            ->dehydrated()
                            ->maxLength(50),
                        
                        Select::make('warehouse_id')
                            ->label('Entrepôt Source')
                            ->relationship('warehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Entrepôt d\'où sortent les produits'),
                        
                        TextInput::make('destination')
                            ->label('Destination')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Ex: Production, Client XYZ, Service Maintenance'),
                        
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'draft' => 'Brouillon',
                                'issued' => 'Émis',
                                'confirmed' => 'Confirmé',
                                'archived' => 'Archivé',
                            ])
                            ->required()
                            ->default('draft')
                            ->reactive()
                            ->disabled(fn ($record) => $record && in_array($record->status, ['confirmed', 'archived'])),
                    ])
                    ->columns(2),
                
                Section::make('Date')
                    ->schema([
                        DatePicker::make('issued_date')
                            ->label('Date d\'Émission')
                            ->required()
                            ->default(now()),
                    ]),
                
                Section::make('Articles / Produits')
                    ->schema([
                        Repeater::make('bonSortieItems')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produit')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->disabled(fn ($record) => $record && $record->bonSortie && in_array($record->bonSortie->status, ['confirmed', 'archived']))
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Get current CUMP for the product from warehouse
                                        $warehouseId = $get('../../warehouse_id');
                                        if ($state && $warehouseId) {
                                            $stockQty = \App\Models\StockQuantity::where('product_id', $state)
                                                ->where('warehouse_id', $warehouseId)
                                                ->first();
                                            
                                            if ($stockQty) {
                                                $set('cump_at_issue', $stockQty->cump_snapshot);
                                            }
                                        }
                                    })
                                    ->columnSpan(4),
                                
                                TextInput::make('qty_issued')
                                    ->label('Quantité')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(0.01)
                                    ->reactive()
                                    ->disabled(fn ($record) => $record && $record->bonSortie && in_array($record->bonSortie->status, ['confirmed', 'archived']))
                                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => 
                                        $set('value_issued', $state * ($get('cump_at_issue') ?? 0))
                                    )
                                    ->columnSpan(2),
                                
                                TextInput::make('cump_at_issue')
                                    ->label('CUMP')
                                    ->helperText('Coût unitaire moyen')
                                    ->numeric()
                                    ->prefix('DH')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),
                                
                                Placeholder::make('value_issued')
                                    ->label('Valeur Totale')
                                    ->content(fn ($get) => number_format(($get('qty_issued') ?? 0) * ($get('cump_at_issue') ?? 0), 2) . ' DH')
                                    ->columnSpan(2),
                                
                                Placeholder::make('stock_available')
                                    ->label('Stock Disponible')
                                    ->content(function ($get) {
                                        $productId = $get('product_id');
                                        $warehouseId = $get('../../warehouse_id');
                                        
                                        if ($productId && $warehouseId) {
                                            $stockQty = \App\Models\StockQuantity::where('product_id', $productId)
                                                ->where('warehouse_id', $warehouseId)
                                                ->first();
                                            
                                            return $stockQty ? number_format($stockQty->available_qty, 2) : '0.00';
                                        }
                                        
                                        return '-';
                                    })
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->defaultItems(1)
                            ->addActionLabel('Ajouter Produit')
                            ->reorderable(false)
                            ->collapsible()
                            ->disabled(fn ($record) => $record && in_array($record->status, ['confirmed', 'archived']))
                            ->itemLabel(fn (array $state): ?string => 
                                $state['product_id'] ? \App\Models\Product::find($state['product_id'])?->name : 'Nouveau produit'
                            ),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
                
                Section::make('Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->maxLength(65535),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
