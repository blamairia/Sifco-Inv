<?php

namespace App\Filament\Resources\BonSorties\Tables;

use App\Models\BonSortie;
use App\Services\BonSortieService;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
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
                
                TextColumn::make('destination_label')
                    ->label('Destination')
                    ->state(fn ($record) => $record->destinationable?->name ?? $record->destination)
                    ->searchable(false),

                TextColumn::make('destination_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn ($record) => $record->destinationable_type === \App\Models\ProductionLine::class ? 'info' : 'gray')
                    ->state(fn ($record) => $record->destinationable_type === \App\Models\ProductionLine::class ? 'Production' : 'Libre'),
                
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
                
                TextColumn::make('total_value')
                    ->label('Valeur Totale')
                    ->state(function ($record) {
                        return $record->bonSortieItems->sum(function ($item) {
                            return $item->qty_issued * $item->cump_at_issue;
                        });
                    })
                    ->money('DZD')
                    ->sortable(false),
                
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

                SelectFilter::make('destination_type')
                    ->label('Type de destination')
                    ->options([
                        'production' => 'Ligne de production',
                        'custom' => 'Libre',
                    ])
                    ->query(function ($query, $value) {
                        if (empty($value)) {
                            return $query;
                        }

                        return $value === 'production'
                            ? $query->where('destinationable_type', \App\Models\ProductionLine::class)
                            : $query->whereNull('destinationable_type');
                    }),

                SelectFilter::make('destinationable_id')
                    ->label('Ligne de production')
                    ->options(fn () => \App\Models\ProductionLine::query()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->query(function ($query, $value) {
                        if (empty($value)) return $query;

                        return $query
                            ->where('destinationable_type', \App\Models\ProductionLine::class)
                            ->where('destinationable_id', $value);
                    }),
            ])
            ->actions([
                Action::make('export_pdf')
                    ->label('Exporter PDF')
                    ->icon('heroicon-o-printer')
                    ->url(fn (BonSortie $record) => route('bonSortie.pdf', [$record]))
                    ->openUrlInNewTab(true),
                EditAction::make(),
                ViewAction::make(),
                Action::make('issue')
                        ->label('Émettre')
                        ->icon('heroicon-o-arrow-up-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Émettre le bon de sortie')
                        ->modalDescription('Êtes-vous sûr de vouloir émettre ce bon? Cette action mettra à jour les niveaux de stock et ne pourra pas être annulée.')
                        ->visible(fn (BonSortie $record): bool => $record->status === 'draft')
                        ->action(function (BonSortie $record, BonSortieService $bonSortieService) {
                            if ($record->bonSortieItems()->count() === 0) {
                                Notification::make()
                                    ->title('Action impossible')
                                    ->body('Vous ne pouvez pas émettre un bon de sortie vide.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            try {
                                $bonSortieService->issue($record);
                                Notification::make()
                                    ->title('Bon de sortie émis avec succès')
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Erreur lors de l\'émission')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                DeleteAction::make()
                    ->visible(fn (BonSortie $record): bool => $record->status === 'draft'),
            ])
            ->bulkActions([
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
                            ->title($count . ' bon(s) confirmé(s)')
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
                            ->title($count . ' bon(s) archivé(s)')
                            ->info()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
