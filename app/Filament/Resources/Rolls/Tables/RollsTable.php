<?php

namespace App\Filament\Resources\Rolls\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RollsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                
                TextColumn::make('product.name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('warehouse.name')
                    ->label('Magasin')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('ean_13')
                    ->label('Code EAN-13')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('batch_number')
                    ->label('Lot')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('received_date')
                    ->label('Date Réception')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_stock' => 'success',
                        'reserved' => 'warning',
                        'consumed' => 'gray',
                        'damaged' => 'danger',
                        'archived' => 'secondary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_stock' => 'En stock',
                        'reserved' => 'Réservé',
                        'consumed' => 'Consommé',
                        'damaged' => 'Endommagé',
                        'archived' => 'Archivé',
                    }),
                
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
