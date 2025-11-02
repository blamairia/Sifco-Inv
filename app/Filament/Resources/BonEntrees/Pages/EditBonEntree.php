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
