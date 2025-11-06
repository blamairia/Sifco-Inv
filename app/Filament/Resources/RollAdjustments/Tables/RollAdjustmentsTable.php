<?php

namespace App\Filament\Resources\RollAdjustments\Tables;

use App\Models\RollAdjustment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RollAdjustmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('adjustment_number')
                    ->label('Référence')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('adjustment_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        RollAdjustment::TYPE_ADD => 'success',
                        RollAdjustment::TYPE_REMOVE => 'danger',
                        RollAdjustment::TYPE_DAMAGE => 'warning',
                        RollAdjustment::TYPE_RESTORE => 'info',
                        RollAdjustment::TYPE_WEIGHT_ADJUST => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        RollAdjustment::TYPE_ADD => 'Ajout',
                        RollAdjustment::TYPE_REMOVE => 'Retrait',
                        RollAdjustment::TYPE_DAMAGE => 'Endommagé',
                        RollAdjustment::TYPE_RESTORE => 'Restauré',
                        RollAdjustment::TYPE_WEIGHT_ADJUST => 'Poids ajusté',
                        default => $state,
                    }),

                TextColumn::make('roll.ean_13')
                    ->label('EAN-13')
                    ->searchable(),

                TextColumn::make('product.name')
                    ->label('Produit')
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('warehouse.name')
                    ->label('Entrepôt')
                    ->toggleable(),

                TextColumn::make('previous_weight_kg')
                    ->label('Poids avant (kg)')
                    ->numeric(decimalPlaces: 3)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('new_weight_kg')
                    ->label('Poids après (kg)')
                    ->numeric(decimalPlaces: 3)
                    ->toggleable(),

                TextColumn::make('weight_delta_kg')
                    ->label('Variation (kg)')
                    ->numeric(decimalPlaces: 3)
                    ->color(fn (?string $state) => match (true) {
                        is_null($state) || (float) $state === 0.0 => 'gray',
                        (float) $state > 0 => 'success',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn (?string $state) => is_null($state)
                        ? '—'
                        : ((float) $state > 0 ? '+' : '') . number_format((float) $state, 3) . ' kg'),

                TextColumn::make('reason')
                    ->label('Raison')
                    ->limit(50)
                    ->tooltip(fn ($state) => $state)
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('adjustment_type')
                    ->label('Type d\'ajustement')
                    ->options([
                        RollAdjustment::TYPE_ADD => 'Ajout',
                        RollAdjustment::TYPE_REMOVE => 'Retrait',
                        RollAdjustment::TYPE_DAMAGE => 'Endommagé',
                        RollAdjustment::TYPE_RESTORE => 'Restauré',
                        RollAdjustment::TYPE_WEIGHT_ADJUST => 'Poids ajusté',
                    ]),

                SelectFilter::make('warehouse_id')
                    ->label('Entrepôt')
                    ->relationship('warehouse', 'name'),

                SelectFilter::make('product_id')
                    ->label('Produit')
                    ->relationship('product', 'name'),
            ])
            ->recordActions([])
            ->bulkActions([]);
    }
}
