<?php

namespace App\Filament\Resources\BonEntrees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Split;
use Filament\Schemas\Schema;

class BonEntreeForm
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
                                    'pending' => '🟠 En Attente',
                                    'validated' => '🔵 Validé',
                                    'received' => '🟢 Reçu',
                                    'cancelled' => '🔴 Annulé',
                                ];
                                
                                return $badges[$record->status] ?? $record->status;
                            }),
                        
                        Placeholder::make('created_info')
                            ->label('Créé le')
                            ->content(fn ($record) => $record ? $record->created_at->format('d/m/Y H:i') : '-')
                            ->visible(fn ($record) => $record !== null),
                        
                        Placeholder::make('received_info')
                            ->label('Reçu le')
                            ->content(fn ($record) => $record && $record->received_date 
                                ? $record->received_date->format('d/m/Y H:i') 
                                : '-')
                            ->visible(fn ($record) => $record && $record->status === 'received'),
                    ])
                    ->columns(3)
                    ->visible(fn ($record) => $record !== null),
                
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
                            ->required()
                            ->helperText('Requis pour valider ou recevoir'),
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
                                $bobines = $get('bobineItems') ?? [];
                                $products = $get('productItems') ?? [];
                                
                                $bobinesTotal = collect($bobines)->sum(fn($item) => $item['price_ht'] ?? 0);
                                $productsTotal = collect($products)->sum(function ($item) {
                                    return ($item['qty_entered'] ?? 0) * ($item['price_ht'] ?? 0);
                                });
                                
                                return number_format($bobinesTotal + $productsTotal, 2) . ' DH';
                            }),
                        
                        TextInput::make('frais_approche')
                            ->label('Frais d\'Approche')
                            ->helperText('Transport, D3, transitaire, etc. (distribués sur validation)')
                            ->numeric()
                            ->prefix('DH')
                            ->default(0)
                            ->required()
                            ->disabled(fn ($record) => $record && $record->status !== 'draft'),
                        
                        Placeholder::make('total_amount_ttc_display')
                            ->label('Montant Total TTC')
                            ->helperText('HT + Frais d\'approche')
                            ->content(function ($get) {
                                $bobines = $get('bobineItems') ?? [];
                                $products = $get('productItems') ?? [];
                                
                                $bobinesTotal = collect($bobines)->sum(fn($item) => $item['price_ht'] ?? 0);
                                $productsTotal = collect($products)->sum(function ($item) {
                                    return ($item['qty_entered'] ?? 0) * ($item['price_ht'] ?? 0);
                                });
                                
                                $frais = $get('frais_approche') ?? 0;
                                return number_format($bobinesTotal + $productsTotal + $frais, 2) . ' DH';
                            }),
                    ])
                    ->columns(3),
                
                Section::make('Bobines')
                    ->description('Ajoutez chaque bobine individuellement avec son code EAN-13')
                    ->schema([
                        Repeater::make('bobineItems')
                            ->relationship(
                                name: 'bonEntreeItems',
                                modifyQueryUsing: fn ($query) => $query->where('item_type', 'bobine')
                            )
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produit Bobine')
                                    ->relationship(
                                        'product',
                                        'name',
                                        fn ($query) => $query->where('is_roll', true)->where('is_active', true)
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disabled(fn ($record) => $record && $record->bonEntree && $record->bonEntree->status === 'received')
                                    ->columnSpan(3),
                                
                                TextInput::make('ean_13')
                                    ->label('Code EAN-13')
                                    ->required()
                                    ->length(13)
                                    ->numeric()
                                    ->placeholder('1234567890123')
                                    ->unique(table: 'bon_entree_items', ignorable: fn ($record) => $record)
                                    ->disabled(fn ($record) => $record && $record->bonEntree && $record->bonEntree->status === 'received')
                                    ->columnSpan(2),
                                
                                TextInput::make('batch_number')
                                    ->label('N° Lot Fournisseur')
                                    ->maxLength(100)
                                    ->disabled(fn ($record) => $record && $record->bonEntree && $record->bonEntree->status === 'received')
                                    ->columnSpan(2),
                                
                                TextInput::make('weight_kg')
                                    ->label('Poids (kg)')
                                    ->helperText('Poids réel de la bobine en kilogrammes')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(0.01)
                                    ->step(0.001)
                                    ->suffix('kg')
                                    ->disabled(fn ($record) => $record && $record->bonEntree && $record->bonEntree->status === 'received')
                                    ->columnSpan(2),

                                Hidden::make('qty_entered')
                                    ->default(1)
                                    ->dehydrated()
                                    ->columnSpan(1),
                                
                                TextInput::make('price_ht')
                                    ->label('Prix HT')
                                    ->numeric()
                                    ->required()
                                    ->prefix('DH')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->disabled(fn ($record) => $record && $record->bonEntree && $record->bonEntree->status === 'received')
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('price_ttc', $state);
                                    })
                                    ->columnSpan(2),
                                
                                TextInput::make('price_ttc')
                                    ->label('Prix TTC')
                                    ->helperText('Après frais')
                                    ->numeric()
                                    ->prefix('DH')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),
                                
                                Placeholder::make('line_total')
                                    ->label('Total')
                                    ->content(fn ($get) => number_format($get('price_ttc') ?? 0, 2) . ' DH')
                                    ->columnSpan(1),
                            ])
                            ->columns(12)
                            ->defaultItems(0)
                            ->addActionLabel('➕ Ajouter Bobine')
                            ->reorderable(false)
                            ->collapsible()
                            ->disabled(fn ($record) => $record && $record->status === 'received')
                            ->itemLabel(fn (array $state): ?string => 
                                $state['ean_13'] ? "Bobine EAN: {$state['ean_13']}" : 'Nouvelle bobine'
                            )
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $data['item_type'] = 'bobine';
                                $data['qty_entered'] = 1;
                                return $data;
                            })
                            ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                                $data['item_type'] = 'bobine';
                                $data['qty_entered'] = $data['qty_entered'] ?? 1;
                                return $data;
                            }),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed(fn ($record) => $record && $record->bonEntreeItems()->bobines()->count() === 0),
                
                Section::make('Produits (Non-Bobines)')
                    ->description('Ajoutez les produits standards avec quantité')
                    ->schema([
                        Repeater::make('productItems')
                            ->relationship(
                                name: 'bonEntreeItems',
                                modifyQueryUsing: fn ($query) => $query->where('item_type', 'product')
                            )
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produit')
                                    ->relationship(
                                        'product',
                                        'name',
                                        fn ($query) => $query->where('is_roll', false)->where('is_active', true)
                                    )
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
                                    ->disabled(fn ($record) => $record && $record->bonEntree && $record->bonEntree->status === 'received')
                                    ->columnSpan(2),
                                
                                TextInput::make('price_ht')
                                    ->label('Prix HT')
                                    ->numeric()
                                    ->required()
                                    ->prefix('DH')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->disabled(fn ($record) => $record && $record->bonEntree && $record->bonEntree->status === 'received')
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('price_ttc', $state);
                                    })
                                    ->columnSpan(2),
                                
                                TextInput::make('price_ttc')
                                    ->label('Prix TTC')
                                    ->helperText('Après frais')
                                    ->numeric()
                                    ->prefix('DH')
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),
                                
                                Placeholder::make('line_total_ttc')
                                    ->label('Total Ligne')
                                    ->content(fn ($get) => number_format(($get('qty_entered') ?? 0) * ($get('price_ttc') ?? 0), 2) . ' DH')
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->defaultItems(0)
                            ->addActionLabel('➕ Ajouter Produit')
                            ->reorderable(false)
                            ->collapsible()
                            ->disabled(fn ($record) => $record && $record->status === 'received')
                            ->itemLabel(fn (array $state): ?string => 
                                $state['product_id'] ? \App\Models\Product::find($state['product_id'])?->name : 'Nouveau produit'
                            )
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $data['item_type'] = 'product';
                                return $data;
                            })
                            ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                                $data['item_type'] = 'product';
                                return $data;
                            }),
                    ])
                    ->columnSpanFull()
                    ->collapsible()
                    ->collapsed(fn ($record) => $record && $record->bonEntreeItems()->products()->count() === 0),
                
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
