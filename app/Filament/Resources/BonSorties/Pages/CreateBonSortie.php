<?php

namespace App\Filament\Resources\BonSorties\Pages;

use App\Filament\Resources\BonSorties\BonSortieResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateBonSortie extends CreateRecord
{
    protected static string $resource = BonSortieResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate bon_number if not set
        if (empty($data['bon_number'])) {
            $data['bon_number'] = \App\Models\BonSortie::generateBonNumber();
        }
        
        // Validate stock availability
        $this->validateStockAvailability($data);
        
        return $data;
    }
    
    protected function afterCreate(): void
    {
        // Ensure items were created
        if ($this->record->bonSortieItems()->count() === 0) {
            Notification::make()
                ->title('Attention')
                ->warning()
                ->body('Le bon a été créé mais aucun article n\'a été ajouté.')
                ->send();
        }
    }

    protected function validateStockAvailability(array $data): void
    {
        $items = $data['bonSortieItems'] ?? [];
        $warehouseId = $data['warehouse_id'];
        
        foreach ($items as $item) {
            $stockQty = \App\Models\StockQuantity::where('product_id', $item['product_id'])
                ->where('warehouse_id', $warehouseId)
                ->first();
            
            if (!$stockQty || $stockQty->available_qty < $item['qty_issued']) {
                $product = \App\Models\Product::find($item['product_id']);
                $available = $stockQty ? $stockQty->available_qty : 0;
                
                Notification::make()
                    ->title('Stock insuffisant')
                    ->danger()
                    ->body("Produit '{$product->name}': Disponible = {$available}, Demandé = {$item['qty_issued']}")
                    ->send();
                
                $this->halt();
            }
        }
    }
}
