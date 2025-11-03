<?php

namespace App\Filament\Resources\BonEntrees\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkAction;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
            ->actions([
                EditAction::make(),
                ViewAction::make(),
                
                Action::make('validate')
                    ->label('Valider')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Valider le Bon d\'Entrée')
                    ->modalDescription('Cette action va calculer les frais d\'approche et passer le bon en statut "En Attente".')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->action(function ($record) {
                        try {
                            $service = new \App\Services\BonEntreeService();
                            $service->validate($record);
                            
                            Notification::make()
                                ->title('Bon validé avec succès')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur de validation')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                Action::make('receive')
                    ->label('Recevoir')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Recevoir le Bon d\'Entrée')
                    ->modalDescription('Cette action va créer les bobines, mettre à jour les stocks et calculer le CUMP. Cette opération ne peut pas être annulée.')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function ($record) {
                        \Illuminate\Support\Facades\Log::channel('stderr')->info('!!!!!! RECEIVE ACTION CLICKED IN TABLE !!!!!!');
                        try {
                            $service = new \App\Services\BonEntreeService();
                            $service->receive($record);
                            
                            $bobinesCount = $record->bonEntreeItems()->bobines()->count();
                            $message = $bobinesCount > 0 
                                ? "Bon reçu avec succès. {$bobinesCount} bobine(s) créée(s)."
                                : "Bon reçu avec succès.";
                            
                            Notification::make()
                                ->title($message)
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur de réception')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                Action::make('cancel')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status, ['draft', 'pending']))
                    ->action(function ($record) {
                        $record->update(['status' => 'cancelled']);
                        
                        Notification::make()
                            ->title('Bon annulé')
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkAction::make('cancel')
                    ->label('Annuler les bons sélectionnés')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $count = 0;
                        foreach ($records as $record) {
                            if (in_array($record->status, ['draft', 'pending'])) {
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
