<?php

namespace App\Filament\Resources\Rolls\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Table;

class RollsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produit')
                    ->searchable(),
                TextColumn::make('warehouse.name')
                    ->label('Entrepôt'),
                TextColumn::make('ean_13')
                    ->label('EAN-13')
                    ->searchable(),
                TextColumn::make('qty')
                    ->label('Quantité')
                    ->numeric(),
                BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'success' => 'in_stock',
                        'danger' => 'consumed',
                    ])
                    ->formatStateUsing(fn (string $state): string => $state === 'in_stock' ? 'En Stock' : 'Consommé'),
            ])
            ->filters([])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
