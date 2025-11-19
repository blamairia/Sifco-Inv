<?php

namespace App\Filament\Resources\BonSorties\Schemas;

use App\Models\Client;
use App\Models\Product;
use App\Models\ProductionLine;
use App\Models\Roll;
use App\Models\StockQuantity;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Filament\Forms\Set;
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
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                // Clear the repeaters when warehouse changes
                                $set('rollItems', []);
                                $set('productItems', []);
                            })
                            ->helperText('Entrepôt d\'où sortent les produits'),
                        
                        Select::make('destinationable_type')
                            ->label('Type de destination')
                            ->options([
                                ProductionLine::class => 'Ligne de production',
                                Client::class => 'Client B2B',
                            ])
                            ->placeholder('Destination libre / externe')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (!$state) {
                                    $set('destinationable_id', null);
                                    $set('destination', null);
                                } elseif ($state === ProductionLine::class && $get('destinationable_id')) {
                                    $line = ProductionLine::find($get('destinationable_id'));
                                    $set('destination', $line?->name);
                                } elseif ($state === Client::class && $get('destinationable_id')) {
                                    $client = Client::find($get('destinationable_id'));
                                    $set('destination', $client?->name);
                                }
                            })
                            ->helperText('Choisissez une ligne de production, un client ou laissez vide pour une destination libre.'),

                        Select::make('destinationable_id')
                            ->label(fn (callable $get) => match ($get('destinationable_type')) {
                                ProductionLine::class => 'Ligne de production',
                                Client::class => 'Client',
                                default => 'Entité liée',
                            })
                            ->options(function (callable $get) {
                                return match ($get('destinationable_type')) {
                                    ProductionLine::class => ProductionLine::query()->orderBy('name')->pluck('name', 'id')->toArray(),
                                    Client::class => Client::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->toArray(),
                                    default => [],
                                };
                            })
                            ->searchable()
                            ->preload()
                            ->required(fn (callable $get) => filled($get('destinationable_type')))
                            ->hidden(fn (callable $get) => blank($get('destinationable_type')))
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $line = ProductionLine::find($state);
                                    if ($line) {
                                        $set('destination', $line->name);
                                        return;
                                    }

                                    $client = Client::find($state);
                                    if ($client) {
                                        $set('destination', $client->name);
                                    }
                                }
                            })
                            ->helperText('La destination est remplie automatiquement si une ligne ou un client est sélectionné.'),

                        TextInput::make('destination')
                            ->label('Destination')
                            ->required(fn (callable $get) => blank($get('destinationable_type')))
                            ->maxLength(255)
                            ->disabled(fn (callable $get) => filled($get('destinationable_type')))
                            ->helperText('Ex: Client XYZ, Service Maintenance, etc.'),
                    ])
                    ->columns(2),
                
                Section::make('Date')
                    ->schema([
                        DatePicker::make('issued_date')
                            ->label('Date d\'Émission')
                            ->required()
                            ->default(now()),
                    ]),

                Section::make('Bobines')
                    ->schema([
                        Repeater::make('rollItems')
                            ->label('Bobines à sortir')
                            ->relationship(
                                name: 'bonSortieItems',
                                modifyQueryUsing: fn ($query) => $query->where('item_type', 'roll')
                            )
                            ->schema([
                                Hidden::make('item_type')->default('roll'),
                                Select::make('roll_id')
                                    ->label('Bobine')
                                    ->preload(false)
                                    ->searchable()
                                    ->getSearchResultsUsing(function ($search, $livewire) {
                                        $warehouseId = $livewire->data['warehouse_id'] ?? null;
                                        if (!$warehouseId) {
                                            return [];
                                        }

                                        // Get all already selected roll IDs from all repeater items
                                        $selectedRollIds = collect($livewire->data['rollItems'] ?? [])
                                            ->pluck('roll_id')
                                            ->filter()
                                            ->toArray();

                                        $query = Roll::with('product')
                                            ->where('status', 'in_stock')
                                            ->where('warehouse_id', $warehouseId)
                                            ->when($search, function ($q) use ($search) {
                                                $q->where('ean_13', 'like', "%{$search}%");
                                            })
                                            ->when(count($selectedRollIds) > 0, function ($q) use ($selectedRollIds) {
                                                $q->whereNotIn('id', $selectedRollIds);
                                            })
                                            ->limit(50)
                                            ->get();

                                        return $query->mapWithKeys(fn ($roll) => [
                                            $roll->id => ($roll->product?->name ?? 'Bobine') . " | {$roll->weight} kg | {$roll->length} m",
                                        ])->toArray();
                                    })
                                    ->getOptionLabelUsing(function ($value) {
                                        $roll = Roll::with('product')->find($value);
                                        if (!$roll) return null;
                                        return ($roll->product?->name ?? 'Bobine') . " | {$roll->weight} kg | {$roll->length} m";
                                    })
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state) {
                                            $roll = Roll::with('bonEntreeItem')->find($state);
                                            if ($roll) {
                                                $set('product_id', $roll->product_id);
                                                $set('cump_at_issue', $roll->cump);
                                                $set('weight_kg', $roll->weight);
                                                $set('length_m', $roll->length);
                                                $set('qty_issued', 1);
                                            }
                                        }
                                    })
                                    ->helperText('Seulement les bobines en stock dans l\'entrepôt sélectionné')
                                    ->columnSpan(4),

                                TextInput::make('weight_kg')
                                    ->label('Poids (kg)')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),

                                TextInput::make('length_m')
                                    ->label('Longueur (m)')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),

                                Hidden::make('qty_issued')
                                    ->default(1)
                                    ->dehydrated(),

                                TextInput::make('cump_at_issue')
                                    ->label('CUMP')
                                    ->numeric()
                                    ->prefix('DZD')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),
                                
                                Placeholder::make('value_issued')
                                    ->label('Valeur Totale')
                                    ->content(function ($get) {
                                        $qty = $get('qty_issued') ?? 0;
                                        $cump = $get('cump_at_issue') ?? 0;
                                        return number_format($qty * $cump, 2) . ' DZD';
                                    })
                                    ->columnSpan(2),

                                Hidden::make('product_id')->dehydrated(),
                            ])
                            ->columns(12)
                            ->addActionLabel('Ajouter Bobine')
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                $state['roll_id'] ? (Roll::find($state['roll_id'])?->product?->name ?? 'Bobine') . " | " . (Roll::find($state['roll_id'])?->weight ?? '') . " kg | " . (Roll::find($state['roll_id'])?->length ?? '') . " m" : 'Nouvelle bobine'
                            )
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $data['item_type'] = 'roll';
                                $roll = Roll::with('bonEntreeItem')->find($data['roll_id']);
                                if ($roll) {
                                    $data['product_id'] = $roll->product_id;
                                    $data['qty_issued'] = 1;
                                    $data['weight_kg'] = $roll->weight;
                                    $data['length_m'] = $roll->length;
                                    $data['cump_at_issue'] = $roll->cump;
                                }
                                return $data;
                            }),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                Section::make('Autres Produits')
                    ->schema([
                        Repeater::make('productItems')
                            ->label('Produits à sortir')
                            ->relationship(
                                name: 'bonSortieItems',
                                modifyQueryUsing: fn ($query) => $query->where('item_type', 'product')
                            )
                            ->schema([
                                Hidden::make('item_type')->default('product'),
                                Select::make('product_id')
                                    ->label('Produit')
                                    ->options(function ($get, $livewire) {
                                        $warehouseId = $livewire->data['warehouse_id'] ?? null;
                                        
                                        if (!$warehouseId) {
                                            return [];
                                        }
                                        
                                        return Product::where('form_type', '!=', Product::FORM_ROLL)
                                            ->where('is_active', true)
                                            ->whereHas('stockQuantities', function ($query) use ($warehouseId) {
                                                $query->where('warehouse_id', $warehouseId)
                                                      ->where('available_qty', '>', 0);
                                            })
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $livewire) {
                                        if ($state) {
                                            $warehouseId = $livewire->data['warehouse_id'] ?? null;
                                            
                                            // Get CUMP from the specific warehouse stock quantity
                                            $stockQty = StockQuantity::where('product_id', $state)
                                                ->where('warehouse_id', $warehouseId)
                                                ->where('available_qty', '>', 0)
                                                ->first();
                                            
                                            if ($stockQty) {
                                                $set('cump_at_issue', $stockQty->cump_snapshot);
                                            }
                                        }
                                    })
                                    ->helperText('Seulement les produits en stock dans l\'entrepôt sélectionné')
                                    ->columnSpan(4),
                                
                                TextInput::make('qty_issued')
                                    ->label('Quantité')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(0.01)
                                    ->columnSpan(2),
                                
                                TextInput::make('cump_at_issue')
                                    ->label('CUMP')
                                    ->helperText('Coût unitaire moyen')
                                    ->numeric()
                                    ->prefix('DZD')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),
                                
                                Placeholder::make('value_issued')
                                    ->label('Valeur Totale')
                                    ->content(function ($get) {
                                        $qty = $get('qty_issued') ?? 0;
                                        $cump = $get('cump_at_issue') ?? 0;
                                        return number_format($qty * $cump, 2) . ' DZD';
                                    })
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->addActionLabel('Ajouter Produit')
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                $state['product_id'] ? Product::find($state['product_id'])?->name : 'Nouveau produit'
                            )
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $data['item_type'] = 'product';
                                return $data;
                            }),
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
