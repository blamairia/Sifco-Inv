<?php

namespace App\Filament\Resources\StockQuantities;

use App\Filament\Resources\StockQuantities\Pages\ListStockQuantities;
use App\Filament\Resources\StockQuantities\Pages\ViewStockQuantity;
use App\Models\StockQuantity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class StockQuantityResource extends Resource
{
    protected static ?string $model = StockQuantity::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Stock Actuel';

    protected static ?string $modelLabel = 'Stock';

    protected static ?string $pluralModelLabel = 'Stocks';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationGroup = 'Inventaire';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations Stock')
                    ->schema([
                        Forms\Components\TextInput::make('product.name')
                            ->label('Produit')
                            ->disabled(),
                        Forms\Components\TextInput::make('warehouse.name')
                            ->label('Entrepôt')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_qty')
                            ->label('Quantité Totale')
                            ->disabled()
                            ->suffix(fn ($record) => $record->product->unit->code ?? ''),
                        Forms\Components\TextInput::make('reserved_qty')
                            ->label('Quantité Réservée')
                            ->disabled()
                            ->suffix(fn ($record) => $record->product->unit->code ?? ''),
                        Forms\Components\TextInput::make('available_qty')
                            ->label('Quantité Disponible')
                            ->disabled()
                            ->suffix(fn ($record) => $record->product->unit->code ?? '')
                            ->extraAttributes(['class' => 'font-bold']),
                        Forms\Components\TextInput::make('cump')
                            ->label('CUMP')
                            ->disabled()
                            ->prefix('MAD')
                            ->formatStateUsing(fn ($state) => number_format($state, 2)),
                        Forms\Components\Placeholder::make('total_value')
                            ->label('Valeur Totale')
                            ->content(fn ($record) => number_format($record->total_qty * $record->cump, 2) . ' MAD')
                            ->extraAttributes(['class' => 'font-bold text-lg']),
                        Forms\Components\TextInput::make('min_stock')
                            ->label('Stock Minimum')
                            ->disabled()
                            ->suffix(fn ($record) => $record->product->unit->code ?? ''),
                        Forms\Components\TextInput::make('cump_snapshot')
                            ->label('CUMP Snapshot')
                            ->disabled()
                            ->prefix('MAD')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) : 'N/A'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.code')
                    ->label('Code Produit')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Entrepôt')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('total_qty')
                    ->label('Qty Total')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn ($state, $record) => number_format($state, 2) . ' ' . ($record->product->unit->code ?? '')),
                Tables\Columns\TextColumn::make('reserved_qty')
                    ->label('Réservé')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn ($state, $record) => number_format($state, 2) . ' ' . ($record->product->unit->code ?? ''))
                    ->color('warning'),
                Tables\Columns\TextColumn::make('available_qty')
                    ->label('Disponible')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn ($state, $record) => number_format($state, 2) . ' ' . ($record->product->unit->code ?? ''))
                    ->color(fn ($state, $record) => $state <= ($record->min_stock ?? 0) ? 'danger' : 'success')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('cump')
                    ->label('CUMP')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' MAD')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_value')
                    ->label('Valeur Totale')
                    ->state(fn ($record) => $record->total_qty * $record->cump)
                    ->sortable(query: fn (Builder $query, string $direction): Builder => 
                        $query->orderByRaw("total_qty * cump {$direction}"))
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' MAD')
                    ->weight('bold')
                    ->color('success'),
                Tables\Columns\IconColumn::make('low_stock')
                    ->label('Alerte')
                    ->state(fn ($record) => $record->available_qty <= ($record->min_stock ?? 0))
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('warehouse_id')
                    ->label('Entrepôt')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('product_id')
                    ->label('Produit')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('category')
                    ->label('Catégorie')
                    ->relationship('product.categories', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stock Faible')
                    ->query(fn (Builder $query) => 
                        $query->whereColumn('available_qty', '<=', 'min_stock')
                    )
                    ->toggle(),
                Tables\Filters\Filter::make('out_of_stock')
                    ->label('Rupture de Stock')
                    ->query(fn (Builder $query) => 
                        $query->where('available_qty', '<=', 0)
                    )
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('view_movements')
                    ->label('Mouvements')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->url(fn ($record) => route('filament.admin.resources.stock-movements.index', [
                        'tableFilters' => [
                            'product_id' => ['value' => $record->product_id],
                            'warehouse_id' => ['value' => $record->warehouse_id],
                        ]
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export')
                    ->label('Exporter')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($records) {
                        // Export logic will be implemented later
                        \Filament\Notifications\Notification::make()
                            ->title('Export en cours...')
                            ->info()
                            ->send();
                    }),
            ])
            ->defaultSort('product.name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockQuantities::route('/'),
            'view' => ViewStockQuantity::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
