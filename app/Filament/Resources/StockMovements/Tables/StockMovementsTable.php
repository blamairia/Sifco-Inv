<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Models\StockMovement;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('performed_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('movement_number')
                    ->label('N° Mouvement')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                TextColumn::make('movement_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'RECEPTION' => 'success',
                        'ISSUE' => 'danger',
                        'TRANSFER' => 'info',
                        'RETURN' => 'warning',
                        'ADJUSTMENT' => 'gray',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'RECEPTION' => 'Réception',
                        'ISSUE' => 'Sortie',
                        'TRANSFER' => 'Transfert',
                        'RETURN' => 'Retour',
                        'ADJUSTMENT' => 'Ajustement',
                        default => $state,
                    }),
                
                TextColumn::make('product.code')
                    ->label('Code Produit')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('product.name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
                TextColumn::make('warehouseFrom.name')
                    ->label('Depuis')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-')
                    ->toggleable(),
                
                TextColumn::make('warehouseTo.name')
                    ->label('Vers')
                    ->badge()
                    ->color('info')
                    ->placeholder('-')
                    ->toggleable(),
                
                TextColumn::make('qty_moved')
                    ->label('Quantité')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger'),
                
                TextColumn::make('cump_at_movement')
                    ->label('CUMP')
                    ->money('DZD')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('value_moved')
                    ->label('Valeur')
                    ->money('DZD')
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold'),
                
                TextColumn::make('reference_number')
                    ->label('Référence')
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'confirmed' => 'Confirmé',
                        'cancelled' => 'Annulé',
                        default => ucfirst($state),
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('user.name')
                    ->label('Par')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('movement_type')
                    ->label('Type')
                    ->options([
                        'RECEPTION' => 'Réception',
                        'ISSUE' => 'Sortie',
                        'TRANSFER' => 'Transfert',
                        'RETURN' => 'Retour',
                        'ADJUSTMENT' => 'Ajustement',
                    ])
                    ->multiple()
                    ->preload(),
                
                SelectFilter::make('product_id')
                    ->label('Produit')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('warehouse_id')
                    ->label('Entrepôt')
                    ->options(function () {
                        return \App\Models\Warehouse::pluck('name', 'id')->toArray();
                    })
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        if (empty($value)) return $query;
                        
                        return $query->where(function (Builder $q) use ($value) {
                            $q->where('warehouse_from_id', $value)
                              ->orWhere('warehouse_to_id', $value);
                        });
                    })
                    ->preload(),
                
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'confirmed' => 'Confirmé',
                        'cancelled' => 'Annulé',
                    ])
                    ->multiple()
                    ->preload(),
                
                Filter::make('date_range')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Du'),
                        \Filament\Forms\Components\DatePicker::make('to')
                            ->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('performed_at', '>=', $date),
                            )
                            ->when(
                                $data['to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('performed_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                Action::make('export_pdf')
                    ->label('Exporter PDF')
                    ->icon('heroicon-o-printer')
                    ->url(fn (StockMovement $record) => route('stockMovement.pdf', [$record]))
                    ->openUrlInNewTab(true),
                // View only - no edit/delete
            ])
            ->defaultSort('performed_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
