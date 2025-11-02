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
            Actions\DeleteAction::make(),
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
