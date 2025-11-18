<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;

class ViewStockMovement extends ViewRecord
{
    protected static string $resource = StockMovementResource::class;

    public function infolist(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->schema([
                Section::make('Informations Mouvement')
                    ->schema([
                        TextEntry::make('movement_number')->label('N° Mouvement')->copyable(),
                        TextEntry::make('movement_type')
                            ->label('Type')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'RECEPTION' => 'Réception',
                                'ISSUE' => 'Sortie',
                                'TRANSFER' => 'Transfert',
                                'RETURN' => 'Retour',
                                'ADJUSTMENT' => 'Ajustement',
                                default => $state,
                            }),
                        TextEntry::make('performed_at')->label('Date')->dateTime('d/m/Y H:i'),
                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'draft' => 'Brouillon',
                                'confirmed' => 'Confirmé',
                                'cancelled' => 'Annulé',
                                default => ucfirst($state),
                            }),
                    ])
                    ->columns(2),
                
                Section::make('Produit')
                    ->schema([
                        TextEntry::make('product.code')->label('Code'),
                        TextEntry::make('product.name')->label('Nom'),
                    ])
                    ->columns(2),
                
                Section::make('Entrepôts')
                    ->schema([
                        TextEntry::make('warehouseFrom.name')->label('Depuis')->badge()->placeholder('-'),
                        TextEntry::make('warehouseTo.name')->label('Vers')->badge()->placeholder('-'),
                    ])
                    ->columns(2),
                
                Section::make('Quantités & Valorisation')
                    ->schema([
                        TextEntry::make('qty_moved')->label('Quantité')->numeric(decimalPlaces: 2),
                        TextEntry::make('cump_at_movement')->label('CUMP')->money('DZD'),
                        TextEntry::make('value_moved')->label('Valeur')->money('DZD')->weight('bold'),
                    ])
                    ->columns(3),
                
                Section::make('Référence & Traçabilité')
                    ->schema([
                        TextEntry::make('reference_number')->label('Référence')->copyable()->placeholder('-'),
                        TextEntry::make('user.name')->label('Créé par')->placeholder('-'),
                        TextEntry::make('approvedBy.name')->label('Approuvé par')->placeholder('-'),
                        TextEntry::make('approved_at')->label('Approuvé le')->dateTime('d/m/Y H:i')->placeholder('-'),
                    ])
                    ->columns(2),
                
                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')->label('')->placeholder('Aucune note'),
                    ])
                    ->collapsible(),
            ]);
    }
}
