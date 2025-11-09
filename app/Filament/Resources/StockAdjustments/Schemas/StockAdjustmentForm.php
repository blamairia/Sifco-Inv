<?php

namespace App\Filament\Resources\StockAdjustments\Schemas;

use App\Models\StockQuantity;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations d\'ajustement')
                    ->schema([
                        Hidden::make('adjustment_number')
                            ->default(fn() => \App\Models\StockAdjustment::generateAdjustmentNumber()),
                        
                        Grid::make(2)
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produit')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn($state, callable $set) => $set('warehouse_id', null)),
                                
                                Select::make('warehouse_id')
                                    ->label('Entrepôt')
                                    ->relationship('warehouse', 'name')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Get $get, callable $set) {
                                        $productId = $get('product_id');
                                        if ($productId && $state) {
                                            $stockQty = StockQuantity::where('product_id', $productId)
                                                ->where('warehouse_id', $state)
                                                ->first();
                                            
                                            if ($stockQty) {
                                                $set('qty_before', $stockQty->total_qty);
                                                $set('weight_before_kg', $stockQty->total_weight_kg ?? 0);
                                                $set('weight_after_kg', $stockQty->total_weight_kg ?? 0);
                                            } else {
                                                $set('qty_before', 0);
                                                $set('weight_before_kg', 0);
                                                $set('weight_after_kg', 0);
                                            }

                                            $set('weight_change_kg', 0);
                                        }
                                    }),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextInput::make('qty_before')
                                    ->label('Quantité actuelle')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->suffix('unités'),
                                
                                TextInput::make('qty_after')
                                    ->label('Nouvelle quantité')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->suffix('unités')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Get $get, callable $set) {
                                        $qtyBefore = $get('qty_before') ?? 0;
                                        $qtyChange = $state - $qtyBefore;
                                        $set('qty_change', $qtyChange);
                                        
                                        if ($qtyChange > 0) {
                                            $set('adjustment_type', 'INCREASE');
                                        } elseif ($qtyChange < 0) {
                                            $set('adjustment_type', 'DECREASE');
                                        } else {
                                            $set('adjustment_type', 'CORRECTION');
                                        }
                                    }),
                                
                                Placeholder::make('qty_change_display')
                                    ->label('Différence')
                                    ->content(function (Get $get) {
                                        $qtyBefore = floatval($get('qty_before') ?? 0);
                                        $qtyAfter = floatval($get('qty_after') ?? 0);
                                        $qtyChange = $qtyAfter - $qtyBefore;
                                        
                                        $sign = $qtyChange > 0 ? '+' : '';
                                        return $sign . number_format($qtyChange, 2) . ' unités';
                                    })
                                    ->extraAttributes(function (Get $get) {
                                        $qtyBefore = floatval($get('qty_before') ?? 0);
                                        $qtyAfter = floatval($get('qty_after') ?? 0);
                                        $qtyChange = $qtyAfter - $qtyBefore;
                                        
                                        $class = 'font-semibold ';
                                        if ($qtyChange > 0) {
                                            $class .= 'text-success-600';
                                        } elseif ($qtyChange < 0) {
                                            $class .= 'text-danger-600';
                                        } else {
                                            $class .= 'text-gray-600';
                                        }
                                        
                                        return ['class' => $class];
                                    }),
                            ]),
                        
                        Hidden::make('qty_change')
                            ->default(0),
                        
                        Hidden::make('adjustment_type')
                            ->default('CORRECTION'),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('weight_before_kg')
                                    ->label('Poids actuel')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->suffix('kg'),

                                TextInput::make('weight_after_kg')
                                    ->label('Nouveau poids')
                                    ->numeric()
                                    ->minValue(0)
                                    ->step(0.001)
                                    ->suffix('kg')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Get $get, callable $set) {
                                        if ($state === null || $state === '') {
                                            return;
                                        }

                                        $weightBefore = (float) ($get('weight_before_kg') ?? 0);
                                        $weightChange = (float) $state - $weightBefore;
                                        $set('weight_change_kg', $weightChange);
                                    }),

                                Placeholder::make('weight_change_display')
                                    ->label('Variation de poids')
                                    ->content(function (Get $get) {
                                        $weightBefore = floatval($get('weight_before_kg') ?? 0);
                                        $weightAfter = floatval($get('weight_after_kg') ?? 0);
                                        $weightChange = $weightAfter - $weightBefore;

                                        $sign = $weightChange > 0 ? '+' : ($weightChange < 0 ? '-' : '');
                                        return $sign . number_format(abs($weightChange), 3) . ' kg';
                                    })
                                    ->extraAttributes(function (Get $get) {
                                        $weightBefore = floatval($get('weight_before_kg') ?? 0);
                                        $weightAfter = floatval($get('weight_after_kg') ?? 0);
                                        $weightChange = $weightAfter - $weightBefore;

                                        $class = 'font-semibold ';
                                        if ($weightChange > 0) {
                                            $class .= 'text-success-600';
                                        } elseif ($weightChange < 0) {
                                            $class .= 'text-danger-600';
                                        } else {
                                            $class .= 'text-gray-600';
                                        }

                                        return ['class' => $class];
                                    }),
                            ]),

                        Hidden::make('weight_change_kg')
                            ->default(0),
                        
                        Textarea::make('reason')
                            ->label('Raison d\'ajustement')
                            ->required()
                            ->rows(3)
                            ->placeholder('Ex: Inventaire physique, correction d\'erreur, perte, dommage...')
                            ->columnSpanFull(),
                        
                        Textarea::make('notes')
                            ->label('Notes supplémentaires')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
                
                Section::make('Approbation')
                    ->schema([
                        Select::make('approved_by')
                            ->label('Approuvé par')
                            ->relationship('approvedBy', 'name')
                            ->disabled()
                            ->dehydrated(false),
                        
                        DateTimePicker::make('approved_at')
                            ->label('Date d\'approbation')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->hidden(fn($record) => !$record || !$record->approved_by),
                
                Hidden::make('adjusted_by')
                    ->default(fn() => Auth::id()),
            ]);
    }
}
