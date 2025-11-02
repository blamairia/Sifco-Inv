<?php

namespace App\Filament\Resources\StockMovements;

use App\Filament\Resources\StockMovements\Pages\ListStockMovements;
use App\Filament\Resources\StockMovements\Pages\ViewStockMovement;
use App\Models\StockMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class StockMovementResource extends Resource
{
    protected static ?string $model = StockMovement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Mouvements Stock';

    protected static ?string $modelLabel = 'Mouvement';

    protected static ?string $pluralModelLabel = 'Mouvements Stock';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationGroup = 'Inventaire';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations Mouvement')
                    ->schema([
                        Forms\Components\TextInput::make('movement_date')
                            ->label('Date')
                            ->disabled(),
                        Forms\Components\TextInput::make('type')
                            ->label('Type')
                            ->disabled()
                            ->formatStateUsing(fn ($state) => match($state) {
                                'ENTRY' => '📥 Entrée',
                                'ISSUE' => '📤 Sortie',
                                'TRANSFER_OUT' => '🔄 Transfert (Sortant)',
                                'TRANSFER_IN' => '🔄 Transfert (Entrant)',
                                'REINTEGRATION' => '↩️ Réintégration',
                                'ADJUSTMENT' => '⚙️ Ajustement',
                                default => $state
                            }),
                        Forms\Components\TextInput::make('product.name')
                            ->label('Produit')
                            ->disabled(),
                        Forms\Components\TextInput::make('warehouse.name')
                            ->label('Entrepôt')
                            ->disabled(),
                        Forms\Components\TextInput::make('quantity_change')
                            ->label('Changement Quantité')
                            ->disabled()
                            ->prefix(fn ($state) => $state > 0 ? '+' : '')
                            ->formatStateUsing(fn ($state, $record) => number_format($state, 2) . ' ' . ($record->product->unit->code ?? ''))
                            ->extraAttributes(fn ($state) => [
                                'class' => $state > 0 ? 'text-green-600 font-bold' : 'text-red-600 font-bold'
                            ]),
                        Forms\Components\TextInput::make('unit_cost')
                            ->label('Coût Unitaire')
                            ->disabled()
                            ->prefix('MAD')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) : 'N/A'),
                        Forms\Components\TextInput::make('cump_before')
                            ->label('CUMP Avant')
                            ->disabled()
                            ->prefix('MAD')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) : 'N/A'),
                        Forms\Components\TextInput::make('cump_after')
                            ->label('CUMP Après')
                            ->disabled()
                            ->prefix('MAD')
                            ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) : 'N/A'),
                        Forms\Components\Placeholder::make('reference')
                            ->label('Référence Document')
                            ->content(fn ($record) => $record->reference_type ? 
                                class_basename($record->reference_type) . ' #' . $record->reference_id : 
                                'N/A'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->disabled()
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('movement_date')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'ENTRY' => 'Entrée',
                        'ISSUE' => 'Sortie',
                        'TRANSFER_OUT' => 'Transfert Out',
                        'TRANSFER_IN' => 'Transfert In',
                        'REINTEGRATION' => 'Réintégration',
                        'ADJUSTMENT' => 'Ajustement',
                        default => $state
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'ENTRY' => 'success',
                        'ISSUE' => 'danger',
                        'TRANSFER_OUT' => 'warning',
                        'TRANSFER_IN' => 'info',
                        'REINTEGRATION' => 'success',
                        'ADJUSTMENT' => 'gray',
                        default => 'gray'
                    })
                    ->icon(fn ($state) => match($state) {
                        'ENTRY' => 'heroicon-o-arrow-down-circle',
                        'ISSUE' => 'heroicon-o-arrow-up-circle',
                        'TRANSFER_OUT' => 'heroicon-o-arrow-right-circle',
                        'TRANSFER_IN' => 'heroicon-o-arrow-left-circle',
                        'REINTEGRATION' => 'heroicon-o-arrow-path',
                        'ADJUSTMENT' => 'heroicon-o-wrench',
                        default => null
                    }),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(30),
                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Entrepôt')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('quantity_change')
                    ->label('Quantité')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn ($state, $record) => 
                        ($state > 0 ? '+' : '') . number_format($state, 2) . ' ' . ($record->product->unit->code ?? '')
                    )
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('unit_cost')
                    ->label('Coût')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) . ' MAD' : 'N/A')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('cump_before')
                    ->label('CUMP Avant')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) : 'N/A')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('cump_after')
                    ->label('CUMP Après')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2) : 'N/A')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reference')
                    ->label('Référence')
                    ->state(fn ($record) => $record->reference_type ? 
                        class_basename($record->reference_type) . ' #' . $record->reference_id : 
                        'N/A')
                    ->searchable(query: fn (Builder $query, string $search) => 
                        $query->where('reference_id', 'like', "%{$search}%")
                    )
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'ENTRY' => 'Entrée',
                        'ISSUE' => 'Sortie',
                        'TRANSFER_OUT' => 'Transfert Sortant',
                        'TRANSFER_IN' => 'Transfert Entrant',
                        'REINTEGRATION' => 'Réintégration',
                        'ADJUSTMENT' => 'Ajustement',
                    ])
                    ->multiple(),
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
                Filter::make('movement_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Du'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('movement_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('movement_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('view_reference')
                    ->label('Voir Document')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->visible(fn ($record) => $record->reference_type && $record->reference_id)
                    ->url(fn ($record) => match(class_basename($record->reference_type)) {
                        'BonEntree' => route('filament.admin.resources.bon-entrees.edit', $record->reference_id),
                        'BonSortie' => route('filament.admin.resources.bon-sorties.edit', $record->reference_id),
                        'BonTransfert' => route('filament.admin.resources.bon-transferts.edit', $record->reference_id),
                        'BonReintegration' => route('filament.admin.resources.bon-reintegrations.edit', $record->reference_id),
                        default => null
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export')
                    ->label('Exporter')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($records) {
                        \Filament\Notifications\Notification::make()
                            ->title('Export en cours...')
                            ->info()
                            ->send();
                    }),
            ])
            ->defaultSort('movement_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockMovements::route('/'),
            'view' => ViewStockMovement::route('/{record}'),
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
