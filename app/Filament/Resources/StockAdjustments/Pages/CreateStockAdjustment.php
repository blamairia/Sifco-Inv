<?php

namespace App\Filament\Resources\StockAdjustments\Pages;

use App\Filament\Resources\StockAdjustments\StockAdjustmentResource;
use App\Services\StockAdjustmentService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateStockAdjustment extends CreateRecord
{
    protected static string $resource = StockAdjustmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure qty_change is calculated
        $data['qty_change'] = ($data['qty_after'] ?? 0) - ($data['qty_before'] ?? 0);
        
        // Determine adjustment type
        if ($data['qty_change'] > 0) {
            $data['adjustment_type'] = 'INCREASE';
        } elseif ($data['qty_change'] < 0) {
            $data['adjustment_type'] = 'DECREASE';
        } else {
            $data['adjustment_type'] = 'CORRECTION';
        }
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // The record is already created, now we need to:
        // 1. Create stock movement
        // 2. Update stock quantity
        
        try {
            $service = new StockAdjustmentService();
            
            // Update stock quantity based on the adjustment
            $stockQty = \App\Models\StockQuantity::where('product_id', $this->record->product_id)
                ->where('warehouse_id', $this->record->warehouse_id)
                ->first();
            
            if ($stockQty) {
                // Create stock movement
                \App\Models\StockMovement::create([
                    'movement_number' => 'MOV-' . now()->format('Ymd') . '-' . str_pad(\App\Models\StockMovement::whereDate('created_at', now()->toDateString())->count() + 1, 4, '0', STR_PAD_LEFT),
                    'product_id' => $this->record->product_id,
                    'warehouse_to_id' => $this->record->qty_change > 0 ? $this->record->warehouse_id : null,
                    'warehouse_from_id' => $this->record->qty_change < 0 ? $this->record->warehouse_id : null,
                    'movement_type' => 'ADJUSTMENT',
                    'qty_moved' => abs($this->record->qty_change),
                    'cump_at_movement' => $stockQty->cump_snapshot,
                    'status' => 'confirmed',
                    'reference_number' => $this->record->adjustment_number,
                    'user_id' => auth()->id() ?? 1,
                    'performed_at' => now(),
                    'notes' => "Ajustement: {$this->record->reason}",
                ]);
                
                // Update stock quantity
                $stockQty->update([
                    'total_qty' => $this->record->qty_after,
                ]);
                
                Notification::make()
                    ->success()
                    ->title('Ajustement créé avec succès')
                    ->body("Stock ajusté de {$this->record->qty_before} à {$this->record->qty_after} unités.")
                    ->send();
            }
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Erreur lors de la mise à jour du stock')
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
