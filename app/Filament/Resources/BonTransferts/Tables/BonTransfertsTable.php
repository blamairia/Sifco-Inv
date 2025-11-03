<?php

namespace App\Filament\Resources\BonTransferts\Tables;

use App\Services\BonTransfertService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class BonTransfertsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('bon_number')
                    ->label('N° Bon')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('warehouseFrom.name')
                    ->label('Entrepôt Source')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('warehouseTo.name')
                    ->label('Entrepôt Destination')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('transfer_date')
                    ->label('Date de Transfert')
                    ->date('d/m/Y')
                    ->sortable(),
                
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'transferred' => 'success',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'transferred' => 'Transféré',
                        'confirmed' => 'Confirmé',
                        'cancelled' => 'Annulé',
                        default => $state,
                    }),
                
                TextColumn::make('bonTransfertItems_count')
                    ->label('Nb Articles')
                    ->counts('bonTransfertItems')
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('transferred_at')
                    ->label('Transféré le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'draft' => 'Brouillon',
                        'transferred' => 'Transféré',
                        'confirmed' => 'Confirmé',
                        'cancelled' => 'Annulé',
                    ]),
                
                SelectFilter::make('warehouse_from_id')
                    ->label('Entrepôt Source')
                    ->relationship('warehouseFrom', 'name'),
                
                SelectFilter::make('warehouse_to_id')
                    ->label('Entrepôt Destination')
                    ->relationship('warehouseTo', 'name'),
            ])
            ->recordActions([
                ViewAction::make(),
                
                EditAction::make()
                    ->visible(fn ($record) => $record->status === 'draft'),
                
                Action::make('transfer')
                    ->label('Transférer')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmer le Transfert')
                    ->modalDescription('Voulez-vous vraiment transférer ce bon ? Les stocks seront déplacés entre les entrepôts.')
                    ->modalSubmitActionLabel('Oui, Transférer')
                    ->visible(fn ($record) => $record->status === 'draft')
                    ->action(function ($record) {
                        try {
                            $service = app(BonTransfertService::class);
                            $service->transfer($record);
                            
                            Notification::make()
                                ->title('Transfert effectué avec succès')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Erreur lors du transfert')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn ($record) => $record->status === 'draft'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
