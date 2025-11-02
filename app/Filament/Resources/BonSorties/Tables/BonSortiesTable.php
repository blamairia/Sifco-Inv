<?php

namespace App\Filament\Resources\BonSorties\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class BonSortiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bon_number')
                    ->label('N° Bon')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('warehouse.name')
                    ->label('Entrepôt')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('destination')
                    ->label('Destination')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'issued' => 'warning',
                        'confirmed' => 'success',
                        'archived' => 'info',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'draft' => 'heroicon-o-pencil',
                        'issued' => 'heroicon-o-arrow-up-circle',
                        'confirmed' => 'heroicon-o-check-circle',
                        'archived' => 'heroicon-o-archive-box',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'issued' => 'Émis',
                        'confirmed' => 'Confirmé',
                        'archived' => 'Archivé',
                    })
                    ->sortable(),
                
                TextColumn::make('issued_date')
                    ->label('Date Émission')
                    ->date('d/m/Y')
                    ->sortable(),
                
                TextColumn::make('bonSortieItems_sum_value_issued')
                    ->label('Valeur Totale')
                    ->sum('bonSortieItems', 'value_issued')
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
                        'issued' => 'Émis',
                        'confirmed' => 'Confirmé',
                        'archived' => 'Archivé',
                    ]),
                
                SelectFilter::make('warehouse')
                    ->label('Entrepôt')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc')
            ->toolbarActions([
                BulkAction::make('confirm')
                    ->label('Confirmer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $count = 0;
                        foreach ($records as $record) {
                            if ($record->status === 'issued' && $record->bonSortieItems->isNotEmpty()) {
                                $record->update(['status' => 'confirmed']);
                                $count++;
                            }
                        }
                        
                        Notification::make()
                            ->title("$count bon(s) confirmé(s)")
                            ->success()
                            ->send();
                    }),
                
                BulkAction::make('archive')
                    ->label('Archiver')
                    ->icon('heroicon-o-archive-box')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $count = 0;
                        foreach ($records as $record) {
                            if ($record->status === 'confirmed') {
                                $record->update(['status' => 'archived']);
                                $count++;
                            }
                        }
                        
                        Notification::make()
                            ->title("$count bon(s) archivé(s)")
                            ->info()
                            ->send();
                    }),
            ]);
    }
}
