<?php

namespace App\Filament\Resources\BonEntrees\Pages;

use App\Filament\Resources\BonEntrees\BonEntreeResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateBonEntree extends CreateRecord
{
    protected static string $resource = BonEntreeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
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
    }
}
