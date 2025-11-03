<?php

namespace App\Filament\Resources\BonEntrees\Pages;

use App\Filament\Resources\BonEntrees\BonEntreeResource;
use App\Models\StockMovement;
use App\Models\StockQuantity;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class EditBonEntree extends EditRecord
{
    protected static string $resource = BonEntreeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Validate Action
            Actions\Action::make('validate')
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
            
            // Receive Action
            Actions\Action::make('receive')
                ->label('Recevoir')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Recevoir le Bon d\'Entrée')
                ->modalDescription('Cette action va créer les bobines, mettre à jour les stocks et calculer le CUMP. Cette opération ne peut pas être annulée.')
                ->visible(fn ($record) => $record->status === 'pending')
                ->action(function ($record) {
                    \Illuminate\Support\Facades\Log::channel('stderr')->info('!!!!!! RECEIVE ACTION CLICKED IN EDIT PAGE !!!!!!');
                    try {
                        $service = new \App\Services\BonEntreeService();
                        $service->receive($record);
                        
                        $bobinesCount = $record->bonEntreeItems()->where('item_type', 'bobine')->count();
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
            
            // Cancel Action
            Actions\Action::make('cancel')
                ->label('Annuler')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Annuler le Bon d\'Entrée')
                ->modalDescription('Êtes-vous sûr de vouloir annuler ce bon ?')
                ->visible(fn ($record) => in_array($record->status, ['draft', 'pending']))
                ->action(function ($record) {
                    $record->update(['status' => 'cancelled']);
                    
                    Notification::make()
                        ->title('Bon annulé')
                        ->warning()
                        ->send();
                }),
            
            // Reopen Action
            Actions\Action::make('reopen')
                ->label('Réouvrir')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Réouvrir le Bon')
                ->modalDescription('Réouvrir ce bon et le remettre en brouillon ?')
                ->visible(fn ($record) => $record->status === 'cancelled')
                ->action(function ($record) {
                    $record->update(['status' => 'draft']);
                    
                    Notification::make()
                        ->title('Bon réouvert')
                        ->info()
                        ->body('Le bon a été réouvert en mode brouillon.')
                        ->send();
                }),
            
            Actions\DeleteAction::make()
                ->visible(fn ($record) => in_array($record->status, ['draft', 'cancelled'])),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Validate business rules
        $this->validateBusinessRules($data);
        
        // Calculate totals from line items
        $items = $data['bonEntreeItems'] ?? [];
        
        $data['total_amount_ht'] = collect($items)->sum(function ($item) {
            return ($item['qty_entered'] ?? 0) * ($item['price_ht'] ?? 0);
        });
        
        $data['total_amount_ttc'] = $data['total_amount_ht'] + ($data['frais_approche'] ?? 0);
        
        return $data;
    }

    protected function validateBusinessRules(array $data): void
    {
        $status = $data['status'] ?? 'draft';
        $items = $data['bonEntreeItems'] ?? [];
        
        // Can't validate or receive without items
        if (in_array($status, ['validated', 'received']) && empty($items)) {
            Notification::make()
                ->title('Validation échouée')
                ->danger()
                ->body('Impossible de valider/recevoir un bon sans articles.')
                ->send();
            
            $this->halt();
        }
        
        // Warehouse required for validated/received
        if (in_array($status, ['validated', 'received']) && empty($data['warehouse_id'])) {
            Notification::make()
                ->title('Validation échouée')
                ->danger()
                ->body('Un entrepôt de destination est requis pour valider ou recevoir.')
                ->send();
            
            $this->halt();
        }
        
        // Can't change status from received
        if ($this->record->status === 'received' && $status !== 'received') {
            Notification::make()
                ->title('Modification interdite')
                ->danger()
                ->body('Impossible de modifier le statut d\'un bon déjà reçu.')
                ->send();
            
            $this->halt();
        }
        
        // Validate status transitions
        $this->validateStatusTransition($this->record->status, $status);
    }

    protected function validateStatusTransition(string $oldStatus, string $newStatus): void
    {
        $allowedTransitions = [
            'draft' => ['pending', 'cancelled'],
            'pending' => ['validated', 'cancelled', 'draft'],
            'validated' => ['received', 'cancelled', 'pending'],
            'received' => ['received'], // No changes allowed
            'cancelled' => ['draft'], // Can reopen
        ];
        
        if (!in_array($newStatus, $allowedTransitions[$oldStatus] ?? [])) {
            Notification::make()
                ->title('Transition invalide')
                ->danger()
                ->body("Impossible de passer de '{$oldStatus}' à '{$newStatus}'.")
                ->send();
            
            $this->halt();
        }
    }

    protected function afterSave(): void
    {
        $bonEntree = $this->record;
        
        // Recalculate totals after saving items
        $bonEntree->refresh();
        $bonEntree->recalculateTotals();
        
        // No automatic stock processing here - use the table actions instead
        // (Valider button for draft->pending, Recevoir button for pending->received)
    }
}
