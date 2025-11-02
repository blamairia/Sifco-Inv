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
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Valider le Bon d\'Entrée')
                ->modalDescription('Êtes-vous sûr de vouloir valider ce bon d\'entrée ?')
                ->visible(fn ($record) => in_array($record->status, ['draft', 'pending']))
                ->action(function ($record) {
                    if (!$record->warehouse_id) {
                        Notification::make()
                            ->title('Validation impossible')
                            ->danger()
                            ->body('Un entrepôt doit être sélectionné.')
                            ->send();
                        return;
                    }
                    
                    if ($record->bonEntreeItems->isEmpty()) {
                        Notification::make()
                            ->title('Validation impossible')
                            ->danger()
                            ->body('Le bon doit contenir au moins un article.')
                            ->send();
                        return;
                    }
                    
                    $record->update(['status' => 'validated']);
                    
                    Notification::make()
                        ->title('Bon validé')
                        ->success()
                        ->body('Le bon d\'entrée a été validé avec succès.')
                        ->send();
                }),
            
            // Receive Action
            Actions\Action::make('receive')
                ->label('Recevoir')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Recevoir le Bon d\'Entrée')
                ->modalDescription('Cette action mettra à jour le stock. Êtes-vous sûr ?')
                ->visible(fn ($record) => $record->status === 'validated')
                ->action(function ($record) {
                    $record->update([
                        'status' => 'received',
                        'received_date' => now(),
                    ]);
                    
                    // Process stock entry
                    $this->processStockEntry($record);
                    
                    Notification::make()
                        ->title('Bon reçu')
                        ->success()
                        ->body('Le bon d\'entrée a été reçu et le stock a été mis à jour.')
                        ->send();
                }),
            
            // Cancel Action
            Actions\Action::make('cancel')
                ->label('Annuler')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Annuler le Bon d\'Entrée')
                ->modalDescription('Êtes-vous sûr de vouloir annuler ce bon ?')
                ->visible(fn ($record) => in_array($record->status, ['draft', 'pending', 'validated']))
                ->action(function ($record) {
                    $record->update(['status' => 'cancelled']);
                    
                    Notification::make()
                        ->title('Bon annulé')
                        ->warning()
                        ->body('Le bon d\'entrée a été annulé.')
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
        
        // Check if status changed to 'received' and warehouse is set
        if ($bonEntree->status === 'received' && $bonEntree->warehouse_id) {
            $this->processStockEntry($bonEntree);
        }
    }

    protected function processStockEntry($bonEntree): void
    {
        // Process each line item
        foreach ($bonEntree->bonEntreeItems as $item) {
            // Update or create stock quantity record
            $stockQty = StockQuantity::firstOrNew([
                'product_id' => $item->product_id,
                'warehouse_id' => $bonEntree->warehouse_id,
            ]);

            $oldQty = $stockQty->total_qty ?? 0;
            $oldCump = $stockQty->cump_snapshot ?? 0;
            
            // Calculate new CUMP
            $newCump = $item->calculateNewCUMP();
            
            // Update stock quantity
            $stockQty->total_qty = $oldQty + $item->qty_entered;
            $stockQty->cump_snapshot = $newCump;
            $stockQty->save();

            // Create stock movement record
            StockMovement::create([
                'movement_number' => $this->generateMovementNumber(),
                'product_id' => $item->product_id,
                'warehouse_to_id' => $bonEntree->warehouse_id,
                'movement_type' => 'RECEPTION',
                'qty_moved' => $item->qty_entered,
                'cump_at_movement' => $newCump,
                'status' => 'confirmed',
                'reference_number' => $bonEntree->bon_number,
                'user_id' => Auth::id(),
                'performed_at' => now(),
                'notes' => "Entrée depuis Bon d'Entrée #{$bonEntree->bon_number}",
            ]);
        }

        Notification::make()
            ->title('Stock mis à jour')
            ->success()
            ->body("Le stock a été mis à jour pour {$bonEntree->bonEntreeItems->count()} produit(s).")
            ->send();
    }

    protected function generateMovementNumber(): string
    {
        $date = now()->format('Ymd');
        $count = StockMovement::whereDate('created_at', now()->toDateString())->count() + 1;
        return 'MOV-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
