<?php

namespace App\Filament\Resources\BonTransferts\Schemas;

use App\Models\Product;
use App\Models\Roll;
use App\Models\StockQuantity;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BonTransfertForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations du Bon')
                    ->schema([
                        TextInput::make('bon_number')
                            ->label('N° Bon de Transfert')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn() => \App\Models\BonTransfert::generateBonNumber())
                            ->disabled()
                            ->dehydrated()
                            ->maxLength(50),
                        
                        Select::make('warehouse_from_id')
                            ->label('Entrepôt Source')
                            ->relationship('warehouseFrom', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                // Clear items when source warehouse changes
                                $set('rollItems', []);
                                $set('productItems', []);
                            })
                            ->helperText('Entrepôt d\'où proviennent les produits')
                            ->disabled(fn ($record) => $record && $record->status !== 'draft'),
                        
                        Select::make('warehouse_to_id')
                            ->label('Entrepôt Destination')
                            ->relationship('warehouseTo', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->different('warehouse_from_id')
                            ->helperText('Entrepôt de destination')
                            ->disabled(fn ($record) => $record && $record->status !== 'draft'),
                        
                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'draft' => 'Brouillon',
                                'transferred' => 'Transféré',
                                'confirmed' => 'Confirmé',
                                'cancelled' => 'Annulé',
                            ])
                            ->required()
                            ->default('draft')
                            ->disabled(),
                    ])
                    ->columns(2),
                
                Section::make('Date')
                    ->schema([
                        DatePicker::make('transfer_date')
                            ->label('Date de Transfert')
                            ->required()
                            ->default(now())
                            ->disabled(fn ($record) => $record && $record->status !== 'draft'),
                    ]),

                Section::make('Bobines')
                    ->schema([
                        Repeater::make('rollItems')
                            ->label('Bobines à transférer')
                            ->relationship(
                                name: 'bonTransfertItems',
                                modifyQueryUsing: fn ($query) => $query->where('item_type', 'roll')
                            )
                            ->schema([
                                Hidden::make('item_type')->default('roll'),
                                Select::make('roll_id')
                                    ->label('Bobine')
                                    ->options(function ($get, $livewire) {
                                        $warehouseFromId = $livewire->data['warehouse_from_id'] ?? null;
                                        
                                        if (!$warehouseFromId) {
                                            return [];
                                        }
                                        
                                        // Get all already selected roll IDs
                                        $selectedRollIds = collect($get('../../rollItems') ?? [])
                                            ->pluck('roll_id')
                                            ->filter()
                                            ->toArray();
                                        
                                        $currentRollId = $get('roll_id');
                                        
                                        return Roll::with('bonEntreeItem')
                                            ->where('status', 'in_stock')
                                            ->where('warehouse_id', $warehouseFromId)
                                            ->when(count($selectedRollIds) > 0, function ($query) use ($selectedRollIds, $currentRollId) {
                                                $query->where(function ($q) use ($selectedRollIds, $currentRollId) {
                                                    $q->whereNotIn('id', $selectedRollIds)
                                                      ->orWhere('id', $currentRollId);
                                                });
                                            })
                                            ->get()
                                            ->mapWithKeys(fn ($roll) => [
                                                $roll->id => "{$roll->ean_13} | {$roll->batch_number} | {$roll->weight} kg"
                                            ])
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state) {
                                            $roll = Roll::with('bonEntreeItem')->find($state);
                                            if ($roll) {
                                                $set('product_id', $roll->product_id);
                                                $set('cump_at_transfer', $roll->cump);
                                                $set('qty_transferred', $roll->weight);
                                            }
                                        }
                                    })
                                    ->helperText('Seulement les bobines en stock dans l\'entrepôt source')
                                    ->columnSpan(4),

                                TextInput::make('qty_transferred')
                                    ->label('Poids (kg)')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),

                                TextInput::make('cump_at_transfer')
                                    ->label('CUMP')
                                    ->numeric()
                                    ->prefix('DH')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),
                                
                                Placeholder::make('value_transferred')
                                    ->label('Valeur Totale')
                                    ->content(function ($get) {
                                        $qty = $get('qty_transferred') ?? 0;
                                        $cump = $get('cump_at_transfer') ?? 0;
                                        return number_format($qty * $cump, 2) . ' DH';
                                    })
                                    ->columnSpan(2),

                                Hidden::make('product_id')->dehydrated(),
                            ])
                            ->columns(10)
                            ->addActionLabel('Ajouter Bobine')
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                $state['roll_id'] ? Roll::find($state['roll_id'])?->ean_13 : 'Nouvelle bobine'
                            )
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $data['item_type'] = 'roll';
                                $roll = Roll::with('bonEntreeItem')->find($data['roll_id']);
                                if ($roll) {
                                    $data['product_id'] = $roll->product_id;
                                    $data['qty_transferred'] = $roll->weight;
                                    $data['cump_at_transfer'] = $roll->cump;
                                }
                                return $data;
                            })
                            ->disabled(fn ($record) => $record && $record->status !== 'draft'),
                    ])
                    ->collapsible()
                    ->columnSpanFull(),

                Section::make('Autres Produits')
                    ->schema([
                        Repeater::make('productItems')
                            ->label('Produits à transférer')
                            ->relationship(
                                name: 'bonTransfertItems',
                                modifyQueryUsing: fn ($query) => $query->where('item_type', 'product')
                            )
                            ->schema([
                                Hidden::make('item_type')->default('product'),
                                Select::make('product_id')
                                    ->label('Produit')
                                    ->options(function ($get, $livewire) {
                                        $warehouseFromId = $livewire->data['warehouse_from_id'] ?? null;
                                        
                                        if (!$warehouseFromId) {
                                            return [];
                                        }
                                        
                                        return Product::where('is_roll', false)
                                            ->where('is_active', true)
                                            ->whereHas('stockQuantities', function ($query) use ($warehouseFromId) {
                                                $query->where('warehouse_id', $warehouseFromId)
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
                                            $warehouseFromId = $livewire->data['warehouse_from_id'] ?? null;
                                            
                                            $stockQty = StockQuantity::where('product_id', $state)
                                                ->where('warehouse_id', $warehouseFromId)
                                                ->where('available_qty', '>', 0)
                                                ->first();
                                            
                                            if ($stockQty) {
                                                $set('cump_at_transfer', $stockQty->cump_snapshot);
                                            }
                                        }
                                    })
                                    ->helperText('Seulement les produits en stock dans l\'entrepôt source')
                                    ->columnSpan(4),
                                
                                TextInput::make('qty_transferred')
                                    ->label('Quantité')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(0.01)
                                    ->columnSpan(2),
                                
                                TextInput::make('cump_at_transfer')
                                    ->label('CUMP')
                                    ->helperText('Coût unitaire moyen')
                                    ->numeric()
                                    ->prefix('DH')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(2),
                                
                                Placeholder::make('value_transferred')
                                    ->label('Valeur Totale')
                                    ->content(function ($get) {
                                        $qty = $get('qty_transferred') ?? 0;
                                        $cump = $get('cump_at_transfer') ?? 0;
                                        return number_format($qty * $cump, 2) . ' DH';
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
                            })
                            ->disabled(fn ($record) => $record && $record->status !== 'draft'),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
                
                Section::make('Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->maxLength(500)
                            ->disabled(fn ($record) => $record && $record->status !== 'draft'),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),
            ]);
    }
}
