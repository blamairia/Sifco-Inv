<?php

namespace App\Filament\Resources\BonReintegrations\Pages;

use App\Filament\Resources\BonReintegrations\BonReintegrationResource;
use App\Services\BonReintegrationService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBonReintegration extends EditRecord
{
    protected static string $resource = BonReintegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('receive')
                ->label('Réceptionner')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('success')
                ->visible(fn () => $this->record->status === 'draft')
                ->requiresConfirmation()
                ->modalHeading('Réceptionner la réintégration')
                ->modalDescription('Cette action remettra en stock les articles sélectionnés.')
                ->action(function (BonReintegrationService $service) {
                    try {
                        $service->receive($this->record);

                        Notification::make()
                            ->title('Bon réintégré avec succès')
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                    } catch (\Throwable $throwable) {
                        Notification::make()
                            ->title('Réception impossible')
                            ->body($throwable->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('verify')
                ->label('Vérifier')
                ->icon('heroicon-o-check-circle')
                ->color('primary')
                ->visible(fn () => $this->record->status === 'received')
                ->requiresConfirmation()
                ->modalHeading('Confirmer la vérification')
                ->action(function () {
                    $this->record->update([
                        'status' => 'verified',
                        'verified_by_id' => auth()->id() ?? 1,
                        'verified_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Bon vérifié')
                        ->success()
                        ->send();

                    $this->record->refresh();
                }),

            Actions\Action::make('archive')
                ->label('Archiver')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->visible(fn () => $this->record->status === 'verified')
                ->requiresConfirmation()
                ->modalHeading('Archiver ce bon')
                ->modalDescription('Le bon sera conservé en lecture seule.')
                ->action(function () {
                    $this->record->update(['status' => 'archived']);

                    Notification::make()
                        ->title('Bon archivé')
                        ->warning()
                        ->send();

                    $this->record->refresh();
                }),

            Actions\Action::make('cancel')
                ->label('Annuler')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->visible(fn () => in_array($this->record->status, ['draft', 'received']))
                ->requiresConfirmation()
                ->modalHeading('Annuler la réintégration')
                ->modalDescription('Les opérations de stock effectuées resteront inchangées.')
                ->action(function () {
                    $this->record->update(['status' => 'cancelled']);

                    Notification::make()
                        ->title('Bon annulé')
                        ->warning()
                        ->send();

                    $this->record->refresh();
                }),

            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status === 'draft'),
        ];
    }
}
