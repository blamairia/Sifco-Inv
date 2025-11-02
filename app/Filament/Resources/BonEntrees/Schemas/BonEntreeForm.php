<?php

namespace App\Filament\Resources\BonEntrees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BonEntreeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations Générales')
                    ->schema([
                        TextInput::make('bon_number')
                            ->label('N° Bon d\'Entrée')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn() => \App\Models\BonEntree::generateBonNumber())
                            ->disabled()
                            ->dehydrated()
                            ->maxLength(50),
                        
                        TextInput::make('document_number')
                            ->label('N° Document Fournisseur')
                            ->helperText('N° de facture ou bon de livraison')
                            ->maxLength(100),
                        
                        Select::make('supplier_id')
                            ->label('Fournisseur')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                        
                        Select::make('warehouse_id')
                            ->label('Entrepôt de Destination')
                            ->relationship('warehouse', 'name')
                            ->searchable()
                            ->preload()
                            ->required(fn ($get) => in_array($get('status'), ['validated', 'received']))
                            ->helperText('Requis pour valider ou recevoir'),
                        
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'draft' => 'Brouillon',
                                'pending' => 'En Attente',
                                'validated' => 'Validé',
                                'received' => 'Reçu',
                                'cancelled' => 'Annulé',
                            ])
                            ->required()
                            ->default('draft')
                            ->reactive()
                            ->disabled(fn ($record) => $record && $record->status === 'received'),
                    ])
                    ->columns(2),
                
                Section::make('Dates')
                    ->schema([
                        DatePicker::make('expected_date')
                            ->label('Date Attendue')
                            ->helperText('Date prévue d\'arrivée'),
                        
                        DatePicker::make('received_date')
                            ->label('Date de Réception')
                            ->helperText('Date réelle de réception'),
                    ])
                    ->columns(2),
                
                Section::make('Montants')
                    ->schema([
                        Placeholder::make('total_amount_ht_display')
                            ->label('Montant Total HT')
                            ->content(function ($get) {
                                $items = $get('bonEntreeItems') ?? [];
                                $total = collect($items)->sum(function ($item) {
                                    return ($item['qty_entered'] ?? 0) * ($item['price_ht'] ?? 0);
                                });
                                return number_format($total, 2) . ' DH';
                            }),
                        
                        TextInput::make('frais_approche')
                            ->label('Frais d\'Approche')
                            ->helperText('Transport, D3, transitaire, etc.')
                            ->numeric()
                            ->prefix('DH')
                            ->default(0)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $items = $get('bonEntreeItems') ?? [];
                                $totalQty = collect($items)->sum('qty_entered');
                                
                                if ($totalQty > 0) {
                                    $fraisPerUnit = $state / $totalQty;
                                    $updatedItems = collect($items)->map(function ($item) use ($fraisPerUnit) {
                                        $item['price_ttc'] = ($item['price_ht'] ?? 0) + $fraisPerUnit;
                                        $item['line_total_ttc'] = ($item['qty_entered'] ?? 0) * $item['price_ttc'];
                                        return $item;
                                    })->toArray();
                                    
                                    $set('bonEntreeItems', $updatedItems);
                                }
                            }),
                        
                        Placeholder::make('total_amount_ttc_display')
                            ->label('Montant Total TTC')
                            ->helperText('HT + Frais d\'approche')
                            ->content(function ($get) {
                                $items = $get('bonEntreeItems') ?? [];
                                $totalHT = collect($items)->sum(function ($item) {
                                    return ($item['qty_entered'] ?? 0) * ($item['price_ht'] ?? 0);
                                });
                                $frais = $get('frais_approche') ?? 0;
                                return number_format($totalHT + $frais, 2) . ' DH';
                            }),
                    ])
                    ->columns(3),
                
                Section::make('Articles / Produits')
                    ->schema([
                        Repeater::make('bonEntreeItems')
                            ->relationship()
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produit')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disabled(fn ($record) => $record && $record->bonEntree && $record->bonEntree->status === 'received')
                                    ->columnSpan(4),
                                
                                TextInput::make('qty_entered')
                                    ->label('Quantité')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(0.01)
                                    ->reactive()
                                    ->disabled(fn ($record) => $record && $record->bonEntree && $record->bonEntree->status === 'received')
                                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => 
                                        $set('line_total_ttc', $state * ($get('price_ttc') ?? 0))
                                    )
                                    ->columnSpan(2),
                                
                                TextInput::make('price_ht')
                                    ->label('Prix HT')
                                    ->numeric()
                                    ->required()
                                    ->prefix('DH')
                                    ->default(0)
                                    ->reactive()
                                    ->disabled(fn ($record) => $record && $record->bonEntree && $record->bonEntree->status === 'received')
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $set('price_ttc', $state);
                                        $set('line_total_ttc', ($get('qty_entered') ?? 0) * $state);
                                    })
                                    ->columnSpan(2),
                                
                                TextInput::make('price_ttc')
                                    ->label('Prix TTC')
                                    ->helperText('Après frais')
                                    ->numeric()
                                    ->required()
                                    ->prefix('DH')
                                    ->default(0)
                                    ->reactive()
                                    ->disabled(fn ($record) => $record && $record->bonEntree && $record->bonEntree->status === 'received')
                                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => 
                                        $set('line_total_ttc', ($get('qty_entered') ?? 0) * $state)
                                    )
                                    ->columnSpan(2),
                                
                                Placeholder::make('line_total_ttc')
                                    ->label('Total Ligne')
                                    ->content(fn ($get) => number_format(($get('qty_entered') ?? 0) * ($get('price_ttc') ?? 0), 2) . ' DH')
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->defaultItems(1)
                            ->addActionLabel('Ajouter Produit')
                            ->reorderable(false)
                            ->collapsible()
                            ->disabled(fn ($record) => $record && $record->status === 'received')
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
