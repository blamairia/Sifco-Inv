<?php

namespace App\Filament\Resources\BonEntrees\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class BonEntreesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bon_number')
                    ->label('N° Bon')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('supplier.name')
                    ->label('Fournisseur')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('document_number')
                    ->label('N° Document')
                    ->searchable()
                    ->toggleable(),
                
                TextColumn::make('warehouse.name')
                    ->label('Entrepôt')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'validated' => 'info',
                        'received' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'pending' => 'En Attente',
                        'validated' => 'Validé',
                        'received' => 'Reçu',
                        'cancelled' => 'Annulé',
                    }),
                
                TextColumn::make('expected_date')
                    ->label('Date Attendue')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('received_date')
                    ->label('Date Réception')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('total_amount_ttc')
                    ->label('Montant TTC')
                    ->money('MAD')
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'pending' => 'En Attente',
                        'validated' => 'Validé',
                        'received' => 'Reçu',
                        'cancelled' => 'Annulé',
                    ]),
                
                SelectFilter::make('supplier')
                    ->label('Fournisseur')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('warehouse')
                    ->label('Entrepôt')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
