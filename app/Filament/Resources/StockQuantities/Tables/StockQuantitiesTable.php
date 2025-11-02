<?php

namespace App\Filament\Resources\StockQuantities\Tables;

use App\Models\StockQuantity;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class StockQuantitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.code')
                    ->label('Code Produit')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('product.name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable()
                    ->description(fn (StockQuantity $record) => $record->product->category?->name),
                
                TextColumn::make('warehouse.name')
                    ->label('Entrepôt')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('total_qty')
                    ->label('Qté Totale')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd(),
                
                TextColumn::make('reserved_qty')
                    ->label('Qté Réservée')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('available_qty')
                    ->label('Qté Disponible')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->alignEnd()
                    ->getStateUsing(fn (StockQuantity $record) => $record->total_qty - $record->reserved_qty)
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state == 0 => 'danger',
                        $state < 10 => 'warning',
                        default => 'success',
                    }),
                
                TextColumn::make('cump_snapshot')
                    ->label('CUMP')
                    ->money('MAD', locale: 'fr')
                    ->sortable()
                    ->alignEnd(),
                
                TextColumn::make('total_value')
                    ->label('Valeur Totale')
                    ->money('MAD', locale: 'fr')
                    ->getStateUsing(fn (StockQuantity $record) => $record->total_qty * $record->cump_snapshot)
                    ->sortable()
                    ->alignEnd()
                    ->weight('bold'),
                
                TextColumn::make('updated_at')
                    ->label('Dernière MAJ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('status')
                    ->label('Statut')
                    ->getStateUsing(function (StockQuantity $record) {
                        $availableQty = $record->total_qty - $record->reserved_qty;
                        if ($availableQty == 0) return 'Rupture';
                        if ($record->product->min_stock && $availableQty <= $record->product->min_stock) return 'Stock Faible';
                        return 'Normal';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Rupture' => 'danger',
                        'Stock Faible' => 'warning',
                        'Normal' => 'success',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Rupture' => 'heroicon-o-x-circle',
                        'Stock Faible' => 'heroicon-o-exclamation-triangle',
                        'Normal' => 'heroicon-o-check-circle',
                    }),
            ])
            ->filters([
                SelectFilter::make('warehouse_id')
                    ->label('Entrepôt')
                    ->relationship('warehouse', 'name')
                    ->multiple()
                    ->preload(),
                
                SelectFilter::make('product.category_id')
                    ->label('Catégorie')
                    ->relationship('product.category', 'name')
                    ->multiple()
                    ->preload(),
                
                SelectFilter::make('status')
                    ->label('Statut Stock')
                    ->options([
                        'out_of_stock' => 'Rupture',
                        'low_stock' => 'Stock Faible',
                        'normal' => 'Normal',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!$data['value']) return $query;
                        
                        return match ($data['value']) {
                            'out_of_stock' => $query->whereRaw('(total_qty - reserved_qty) = 0'),
                            'low_stock' => $query->whereHas('product', function (Builder $q) {
                                $q->whereRaw('(stock_quantities.total_qty - stock_quantities.reserved_qty) <= products.min_stock')
                                  ->whereRaw('(stock_quantities.total_qty - stock_quantities.reserved_qty) > 0');
                            }),
                            'normal' => $query->whereHas('product', function (Builder $q) {
                                $q->whereRaw('(stock_quantities.total_qty - stock_quantities.reserved_qty) > products.min_stock');
                            }),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                Action::make('view_movements')
                    ->label('Mouvements')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->url(fn (StockQuantity $record) => route('filament.admin.resources.stock-movements.index', [
                        'tableFilters' => [
                            'product_id' => ['value' => $record->product_id],
                            'warehouse_id' => ['value' => $record->warehouse_id],
                        ],
                    ])),
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
