<?php

namespace App\Filament\Resources\BonReintegrations\Tables;

use App\Models\BonReintegration;
use App\Services\BonReintegrationService;
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

class BonReintegrationsTable
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

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable()
                    ->icon(fn (string $state) => match ($state) {
                        'draft' => 'heroicon-o-pencil',
                        'received' => 'heroicon-o-inbox-stack',
                        'verified' => 'heroicon-o-check-badge',
                        'archived' => 'heroicon-o-archive-box',
                        'cancelled' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state) => match ($state) {
                        'draft' => 'gray',
                        'received' => 'success',
                        'verified' => 'primary',
                        'archived' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'draft' => 'Brouillon',
                        'received' => 'Réceptionné',
                        'verified' => 'Vérifié',
                        'archived' => 'Archivé',
                        'cancelled' => 'Annulé',
                        default => $state,
                    }),

                TextColumn::make('return_date')
                    ->label('Date de retour')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('bonReintegrationItems_count')
                    ->counts('bonReintegrationItems')
                    ->label('Lignes')
                    ->sortable(),

                TextColumn::make('roll_weight_total')
                    ->label('Poids bobines (kg)')
                    ->formatStateUsing(fn (BonReintegration $record) => number_format(
                        (float) $record->bonReintegrationItems()
                            ->where('item_type', 'roll')
                            ->sum('returned_weight_kg'),
                        2
                    ))
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
                        'received' => 'Réceptionné',
                        'verified' => 'Vérifié',
                        'archived' => 'Archivé',
                        'cancelled' => 'Annulé',
                    ]),

                SelectFilter::make('warehouse')
                    ->label('Entrepôt')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make(),
                ViewAction::make(),

                Action::make('receive')
                    ->label('Réceptionner')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (BonReintegration $record) => $record->status === 'draft')
                    ->action(function (BonReintegration $record) {
                        $service = app(BonReintegrationService::class);

                        try {
                            $service->receive($record);

                            Notification::make()
                                ->title('Bon réintégré avec succès')
                                ->success()
                                ->send();
                        } catch (\Throwable $throwable) {
                            Notification::make()
                                ->title('Échec de la réintégration')
                                ->body($throwable->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('verify')
                    ->label('Vérifier')
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (BonReintegration $record) => $record->status === 'received')
                    ->action(function (BonReintegration $record) {
                        $record->update([
                            'status' => 'verified',
                            'verified_by_id' => auth()->id() ?? 1,
                            'verified_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Bon vérifié')
                            ->success()
                            ->send();
                    }),

                Action::make('archive')
                    ->label('Archiver')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (BonReintegration $record) => in_array($record->status, ['verified']))
                    ->action(function (BonReintegration $record) {
                        $record->update(['status' => 'archived']);

                        Notification::make()
                            ->title('Bon archivé')
                            ->warning()
                            ->send();
                    }),

                Action::make('cancel')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (BonReintegration $record) => in_array($record->status, ['draft', 'received']))
                    ->action(function (BonReintegration $record) {
                        $record->update(['status' => 'cancelled']);

                        Notification::make()
                            ->title('Bon annulé')
                            ->warning()
                            ->send();
                    }),

                DeleteAction::make()
                    ->visible(fn (BonReintegration $record) => $record->status === 'draft'),
            ])
            ->bulkActions([
                BulkAction::make('bulkArchive')
                    ->label('Archiver la sélection')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $count = 0;

                        foreach ($records as $record) {
                            if ($record->status === 'verified') {
                                $record->update(['status' => 'archived']);
                                $count++;
                            }
                        }

                        if ($count > 0) {
                            Notification::make()
                                ->title($count . ' bon(s) archivé(s)')
                                ->warning()
                                ->send();
                        }
                    }),
            ]);
    }
}
