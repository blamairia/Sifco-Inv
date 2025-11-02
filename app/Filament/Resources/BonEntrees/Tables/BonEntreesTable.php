<?php

namespace App\Filament\Resources\BonEntrees\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class BonEntreesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bon_number')
                    ->label('N° Bon')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('supplier.name')
                    ->label('Fournisseur')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('document_number')
                    ->label('N° Document')
                    ->searchable()
                    ->toggleable(),
                
                TextColumn::make('warehouse.name')
                    ->label('Entrepôt')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'validated' => 'info',
                        'received' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'draft' => 'heroicon-o-pencil',
                        'pending' => 'heroicon-o-clock',
                        'validated' => 'heroicon-o-check-circle',
                        'received' => 'heroicon-o-inbox-arrow-down',
                        'cancelled' => 'heroicon-o-x-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'pending' => 'En Attente',
                        'validated' => 'Validé',
                        'received' => 'Reçu',
                        'cancelled' => 'Annulé',
                    })
                    ->sortable(),
                
                TextColumn::make('expected_date')
                    ->label('Date Attendue')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('received_date')
                    ->label('Date Réception')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('total_amount_ttc')
                    ->label('Montant TTC')
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
                        'pending' => 'En Attente',
                        'validated' => 'Validé',
                        'received' => 'Reçu',
                        'cancelled' => 'Annulé',
                    ]),
                
                SelectFilter::make('supplier')
                    ->label('Fournisseur')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('warehouse')
                    ->label('Entrepôt')
                    ->relationship('warehouse', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('created_at', 'desc')
            ->toolbarActions([
                BulkAction::make('validate')
                    ->label('Valider')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $count = 0;
                        foreach ($records as $record) {
                            if (in_array($record->status, ['draft', 'pending']) && 
                                $record->warehouse_id && 
                                $record->bonEntreeItems->isNotEmpty()) {
                                $record->update(['status' => 'validated']);
                                $count++;
                            }
                        }
                        
                        Notification::make()
                            ->title("$count bon(s) validé(s)")
                            ->success()
                            ->send();
                    }),
                
                BulkAction::make('cancel')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $count = 0;
                        foreach ($records as $record) {
                            if (in_array($record->status, ['draft', 'pending', 'validated'])) {
                                $record->update(['status' => 'cancelled']);
                                $count++;
                            }
                        }
                        
                        Notification::make()
                            ->title("$count bon(s) annulé(s)")
                            ->warning()
                            ->send();
                    }),
            ]);
    }
}
