<?php

namespace App\Filament\Resources\BonSorties\Pages;

use App\Filament\Resources\BonSorties\BonSortieResource;
use App\Models\BonSortie;
use App\Services\BonSortieService;
use Filament\Actions;
use Barryvdh\DomPDF\Facade\Pdf as DomPdf;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditBonSortie extends EditRecord
{
    protected static string $resource = BonSortieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_pdf')
                ->label('Exporter en PDF')
                ->icon('heroicon-o-document-download')
                ->tooltip('Télécharger en PDF')
                ->action(function () {
                    $record = $this->record;
                    $fileName = 'BonSortie-' . ($record->bon_number ?? $record->id) . '.pdf';

                    if (class_exists(DomPdf::class)) {
                        $pdf = DomPdf::loadView('bon_sorties.pdf', ['bonSortie' => $record]);
                        return $pdf->download($fileName);
                    }

                    Notification::make()
                        ->title('PDF non disponible')
                        ->body('Veuillez installer le paquet barryvdh/laravel-dompdf: composer require barryvdh/laravel-dompdf')
                        ->warning()
                        ->send();
                }),

            Actions\Action::make('issue')
                ->label('Émettre')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('warning')
                ->visible(fn (BonSortie $record): bool => $record->status === 'draft')
                ->requiresConfirmation()
                ->modalHeading('Émettre le bon de sortie')
                ->modalDescription('Cette action va déduire le stock. Continuer ?')
                ->action(function (BonSortieService $bonSortieService) {
                    if ($this->record->bonSortieItems()->count() === 0) {
                        Notification::make()
                            ->title('Action impossible')
                            ->body('Vous ne pouvez pas émettre un bon de sortie vide.')
                            ->warning()
                            ->send();
                        return;
                    }

                    try {
                        $bonSortieService->issue($this->record);
                        Notification::make()
                            ->title('Bon de sortie émis avec succès')
                            ->success()
                            ->send();
                        
                        $this->redirect($this->getResource()::getUrl('index'));

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erreur lors de l\'émission')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('confirm')
                ->label('Confirmer')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === 'issued')
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

            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status === 'draft'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (array_key_exists('status', $data) && $data['status'] !== $this->record->status) {
            Notification::make()
                ->title('Transition interdite')
                ->danger()
                ->body('Utilisez les actions dédiées pour changer le statut du bon de sortie.')
                ->send();

            $this->halt();
        }

        // Ensure destination is set when destinationable info is present
        if (empty($data['destination']) && !empty($data['destinationable_type']) && !empty($data['destinationable_id'])) {
            $class = $data['destinationable_type'];
            try {
                $related = $class::find($data['destinationable_id']);
                if ($related) {
                    $data['destination'] = $related->name ?? ($related->title ?? (string) $related);
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        unset($data['status']);

        return $data;
    }
}
