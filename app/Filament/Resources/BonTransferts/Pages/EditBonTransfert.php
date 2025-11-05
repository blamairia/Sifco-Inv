<?php

namespace App\Filament\Resources\BonTransferts\Pages;

use App\Filament\Resources\BonTransferts\BonTransfertResource;
use App\Models\BonTransfert;
use App\Services\BonTransfertService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditBonTransfert extends EditRecord
{
    protected static string $resource = BonTransfertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('transfer')
                ->label('Transférer')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn (BonTransfert $record): bool => $record->status === 'draft')
                ->requiresConfirmation()
                ->modalHeading('Transférer le bon de transfert')
                ->modalDescription('Cette action va transférer le stock entre les entrepôts. Continuer ?')
                ->action(function (BonTransfertService $bonTransfertService) {
                    if ($this->record->bonTransfertItems()->count() === 0) {
                        Notification::make()
                            ->title('Action impossible')
                            ->body('Vous ne pouvez pas transférer un bon de transfert vide.')
                            ->warning()
                            ->send();
                        return;
                    }

                    try {
                        $bonTransfertService->transfer($this->record);
                        Notification::make()
                            ->title('Bon de transfert effectué avec succès')
                            ->success()
                            ->send();
                        
                        $this->redirect($this->getResource()::getUrl('index'));

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erreur lors du transfert')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('receive')
                ->label('Recevoir')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('primary')
                ->visible(fn () => $this->record->status === 'in_transit')
                ->requiresConfirmation()
                ->modalHeading('Recevoir le transfert')
                ->modalDescription('Confirmer la réception du transfert')
                ->action(function () {
                    $this->record->update([
                        'status' => 'received',
                        'received_at' => now(),
                        'received_by_id' => \Illuminate\Support\Facades\Auth::id() ?? 1,
                    ]);
                    
                    Notification::make()
                        ->title('Transfert reçu')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('confirm')
                ->label('Confirmer')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === 'received')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'confirmed']);
                    
                    Notification::make()
                        ->title('Bon confirmé')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('archive')
                ->label('Archiver')
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->visible(fn () => in_array($this->record->status, ['confirmed']))
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'archived']);
                    
                    Notification::make()
                        ->title('Bon archivé')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reopen')
                ->label('Réouvrir')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn () => $this->record->status === 'archived')
                ->requiresConfirmation()
                ->modalHeading('Réouvrir le bon')
                ->modalDescription('Le bon reviendra au statut "Confirmé"')
                ->action(function () {
                    $this->record->update(['status' => 'confirmed']);
                    
                    Notification::make()
                        ->title('Bon réouvert')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('cancel')
                ->label('Annuler')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Annuler le bon de transfert')
                ->modalDescription('Êtes-vous sûr de vouloir annuler ce bon ?')
                ->visible(fn ($record) => in_array($record->status, ['draft', 'in_transit']))
                ->action(function ($record) {
                    $record->update(['status' => 'cancelled']);
                    
                    Notification::make()
                        ->title('Bon annulé')
                        ->warning()
                        ->send();
                }),

            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status === 'draft'),
        ];
    }
}
