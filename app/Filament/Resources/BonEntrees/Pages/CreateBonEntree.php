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
        
        // Calculate totals from line items (bobines count as 1 unit)
        $bobines = $data['bobineItems'] ?? [];
        $products = $data['productItems'] ?? [];

        $bobinesHt = collect($bobines)->sum(fn ($item) => $item['price_ht'] ?? 0);
        $productsHt = collect($products)->sum(fn ($item) => ($item['qty_entered'] ?? 0) * ($item['price_ht'] ?? 0));

        $data['total_amount_ht'] = $bobinesHt + $productsHt;
        $data['total_amount_ttc'] = $data['total_amount_ht'] + ($data['frais_approche'] ?? 0);
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Recalculate totals after saving items
        $this->record->refresh();
        $this->record->recalculateTotals();
    }

    protected function validateBusinessRules(array $data): void
    {
        $status = $data['status'] ?? 'draft';
        $bobines = $data['bobineItems'] ?? [];
        $products = $data['productItems'] ?? [];
        $hasItems = !empty($bobines) || !empty($products);

        // Can't validate or receive without items
        if (in_array($status, ['validated', 'received']) && ! $hasItems) {
            Notification::make()
                ->title('Validation échouée')
                ->danger()
                ->body('Impossible de valider/recevoir un bon sans articles.')
                ->send();
            
            $this->halt();
        }
        
        foreach ($bobines as $index => $bobine) {
            $weight = (float) ($bobine['weight_kg'] ?? 0);

            if ($weight <= 0) {
                Notification::make()
                    ->title('Poids manquant pour bobine')
                    ->danger()
                    ->body("La bobine #" . ($index + 1) . " doit avoir un poids strictement positif.")
                    ->send();

                $this->halt();
            }
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
