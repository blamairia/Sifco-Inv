<?php

namespace App\Filament\Resources\StockQuantities\Pages;

use App\Filament\Resources\StockQuantities\StockQuantityResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;

class ViewStockQuantity extends ViewRecord
{
    protected static string $resource = StockQuantityResource::class;

    public function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Section::make('Informations Produit')
                    ->schema([
                        TextEntry::make('product.code')->label('Code'),
                        TextEntry::make('product.name')->label('Nom'),
                        TextEntry::make('product.category.name')->label('Catégorie'),
                        TextEntry::make('product.unit.name')->label('Unité'),
                    ])
                    ->columns(2),
                
                Section::make('Informations Stock')
                    ->schema([
                        TextEntry::make('warehouse.name')->label('Entrepôt')->badge()->color('info'),
                        TextEntry::make('total_qty')->label('Quantité Totale')->numeric(decimalPlaces: 2),
                        TextEntry::make('reserved_qty')->label('Quantité Réservée')->numeric(decimalPlaces: 2),
                        TextEntry::make('available_qty')
                            ->label('Quantité Disponible')
                            ->getStateUsing(fn ($record) => $record->total_qty - $record->reserved_qty)
                            ->badge()
                            ->color(fn ($state) => match (true) {
                                $state == 0 => 'danger',
                                $state < 10 => 'warning',
                                default => 'success',
                            }),
                    ])
                    ->columns(2),
                
                Section::make('Valorisation')
                    ->schema([
                        TextEntry::make('cump_snapshot')
                            ->label('CUMP')
                            ->money('DZD'),
                        TextEntry::make('total_value')
                            ->label('Valeur Totale')
                            ->money('DZD')
                            ->getStateUsing(fn ($record) => $record->total_qty * $record->cump_snapshot)
                            ->weight('bold'),
                    ])
                    ->columns(2),
                
                Section::make('Métadonnées')
                    ->schema([
                        TextEntry::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
                        TextEntry::make('updated_at')->label('Modifié le')->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_movements')
                ->label('Voir les Mouvements')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->url(fn ($record) => route('filament.admin.resources.stock-movements.index', [
                    'tableFilters' => [
                        'product_id' => ['value' => $record->product_id],
                        'warehouse_id' => ['value' => $record->warehouse_id],
                    ],
                ])),
        ];
    }
}
