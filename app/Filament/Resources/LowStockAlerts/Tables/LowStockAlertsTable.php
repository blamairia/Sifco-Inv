<?php

namespace App\Filament\Resources\LowStockAlerts\Tables;

use App\Models\LowStockAlert;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Auth;

class LowStockAlertsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),
                    
                TextColumn::make('warehouse.name')
                    ->label('Entrepôt')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('current_qty')
                    ->label('Qté Actuelle')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' unités')
                    ->sortable()
                    ->color(fn($record) => $record->current_qty <= 0 ? 'danger' : 'gray'),
                    
                TextColumn::make('min_stock')
                    ->label('Stock Min')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' unités')
                    ->sortable(),
                    
                TextColumn::make('safety_stock')
                    ->label('Stock Sécurité')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' unités')
                    ->sortable(),
                    
                TextColumn::make('severity')
                    ->label('Sévérité')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'CRITICAL' => 'danger',
                        'HIGH' => 'warning',
                        'MEDIUM' => 'info',
                        'LOW' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'CRITICAL' => 'Critique',
                        'HIGH' => 'Élevée',
                        'MEDIUM' => 'Moyenne',
                        'LOW' => 'Faible',
                        default => $state,
                    })
                    ->sortable(),
                    
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ACTIVE' => 'danger',
                        'RESOLVED' => 'success',
                        'IGNORED' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'ACTIVE' => 'Active',
                        'RESOLVED' => 'Résolu',
                        'IGNORED' => 'Ignoré',
                        default => $state,
                    })
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->description(fn($record) => $record->created_at->format('d/m/Y H:i')),
                    
                TextColumn::make('resolvedBy.name')
                    ->label('Résolu par')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('resolved_at')
                    ->label('Résolu le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('warehouse_id')
                    ->label('Entrepôt')
                    ->relationship('warehouse', 'name')
                    ->multiple()
                    ->preload(),
                    
                SelectFilter::make('severity')
                    ->label('Sévérité')
                    ->options([
                        'CRITICAL' => 'Critique',
                        'HIGH' => 'Élevée',
                        'MEDIUM' => 'Moyenne',
                        'LOW' => 'Faible',
                    ])
                    ->multiple(),
                    
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'ACTIVE' => 'Active',
                        'RESOLVED' => 'Résolu',
                        'IGNORED' => 'Ignoré',
                    ])
                    ->default('ACTIVE'),
            ])
            ->recordActions([
                Action::make('resolve')
                    ->label('Résoudre')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (LowStockAlert $record): bool => $record->status === 'ACTIVE')
                    ->action(function (LowStockAlert $record): void {
                        $record->update([
                            'status' => 'RESOLVED',
                            'resolved_by' => Auth::id(),
                            'resolved_at' => now(),
                        ]);
                    }),
                    
                Action::make('ignore')
                    ->label('Ignorer')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (LowStockAlert $record): bool => $record->status === 'ACTIVE')
                    ->action(function (LowStockAlert $record): void {
                        $record->update([
                            'status' => 'IGNORED',
                            'resolved_by' => Auth::id(),
                            'resolved_at' => now(),
                        ]);
                    }),
                    
                Action::make('reactivate')
                    ->label('Réactiver')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (LowStockAlert $record): bool => in_array($record->status, ['RESOLVED', 'IGNORED']))
                    ->action(function (LowStockAlert $record): void {
                        $record->update([
                            'status' => 'ACTIVE',
                            'resolved_by' => null,
                            'resolved_at' => null,
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
