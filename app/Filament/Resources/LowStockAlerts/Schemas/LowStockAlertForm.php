<?php

namespace App\Filament\Resources\LowStockAlerts\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LowStockAlertForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations de l\'alerte')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('product.name')
                                    ->label('Produit')
                                    ->content(fn($record) => $record->product->name),
                                    
                                Placeholder::make('warehouse.name')
                                    ->label('Entrepôt')
                                    ->content(fn($record) => $record->warehouse->name),
                            ]),
                            
                        Grid::make(3)
                            ->schema([
                                Placeholder::make('current_qty')
                                    ->label('Quantité Actuelle')
                                    ->content(fn($record) => number_format($record->current_qty, 2) . ' unités'),
                                    
                                Placeholder::make('min_stock')
                                    ->label('Stock Minimum')
                                    ->content(fn($record) => number_format($record->min_stock, 2) . ' unités'),
                                    
                                Placeholder::make('safety_stock')
                                    ->label('Stock de Sécurité')
                                    ->content(fn($record) => $record->safety_stock ? number_format($record->safety_stock, 2) . ' unités' : '—'),
                            ]),
                            
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('severity')
                                    ->label('Sévérité')
                                    ->content(fn($record) => match ($record->severity) {
                                        'CRITICAL' => '🔴 Critique',
                                        'HIGH' => '🟠 Élevée',
                                        'MEDIUM' => '🟡 Moyenne',
                                        'LOW' => '🔵 Faible',
                                        default => $record->severity,
                                    }),
                                    
                                Placeholder::make('status')
                                    ->label('Statut')
                                    ->content(fn($record) => match ($record->status) {
                                        'ACTIVE' => '⚠️ Active',
                                        'RESOLVED' => '✅ Résolu',
                                        'IGNORED' => '⏸️ Ignoré',
                                        default => $record->status,
                                    }),
                            ]),
                            
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                            
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('created_at')
                                    ->label('Créé le')
                                    ->content(fn($record) => $record->created_at->format('d/m/Y à H:i')),
                                    
                                Placeholder::make('resolved_info')
                                    ->label('Résolution')
                                    ->content(fn($record) => $record->resolved_at 
                                        ? "Résolu le {$record->resolved_at->format('d/m/Y à H:i')} par {$record->resolvedBy->name}"
                                        : '—')
                                    ->visible(fn($record) => $record->resolved_at !== null),
                            ]),
                    ]),
            ]);
    }
}
