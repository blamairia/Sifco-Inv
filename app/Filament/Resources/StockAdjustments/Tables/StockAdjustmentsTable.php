<?php

namespace App\Filament\Resources\StockAdjustments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockAdjustmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('adjustment_number')
                    ->label('N° Ajustement')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('product.name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('warehouse.name')
                    ->label('Entrepôt')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('qty_before')
                    ->label('Qté Avant')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd(),
                
                TextColumn::make('qty_after')
                    ->label('Qté Après')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd(),
                
                TextColumn::make('qty_change')
                    ->label('Différence')
                    ->numeric(decimalPlaces: 2, decimalSeparator: '.', thousandsSeparator: ',')
                    ->sortable()
                    ->alignEnd()
                    ->color(fn ($state) => $state > 0 ? 'success' : ($state < 0 ? 'danger' : 'gray'))
                    ->formatStateUsing(fn ($state) => ($state > 0 ? '+' : '') . number_format($state, 2)),
                
                BadgeColumn::make('adjustment_type')
                    ->label('Type')
                    ->colors([
                        'success' => 'INCREASE',
                        'danger' => 'DECREASE',
                        'warning' => 'CORRECTION',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'INCREASE' => 'Augmentation',
                        'DECREASE' => 'Diminution',
                        'CORRECTION' => 'Correction',
                        default => $state,
                    }),
                
                TextColumn::make('reason')
                    ->label('Raison')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->reason)
                    ->searchable(),
                
                TextColumn::make('adjustedBy.name')
                    ->label('Ajusté par')
                    ->sortable()
                    ->toggleable(),
                
                IconColumn::make('approved_by')
                    ->label('Approuvé')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('warehouse_id')
                    ->label('Entrepôt')
                    ->relationship('warehouse', 'name')
                    ->preload(),
                
                SelectFilter::make('adjustment_type')
                    ->label('Type d\'ajustement')
                    ->options([
                        'INCREASE' => 'Augmentation',
                        'DECREASE' => 'Diminution',
                        'CORRECTION' => 'Correction',
                    ]),
                
                SelectFilter::make('approved')
                    ->label('Statut approbation')
                    ->options([
                        'approved' => 'Approuvé',
                        'pending' => 'En attente',
                    ])
                    ->query(function ($query, $state) {
                        if ($state['value'] === 'approved') {
                            $query->whereNotNull('approved_by');
                        } elseif ($state['value'] === 'pending') {
                            $query->whereNull('approved_by');
                        }
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
