<?php

namespace App\Filament\Resources\BonEntrees\Pages;

use App\Filament\Resources\BonEntrees\BonEntreeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBonEntree extends CreateRecord
{
    protected static string $resource = BonEntreeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate totals from line items
        $items = $data['bonEntreeItems'] ?? [];
        
        $data['total_amount_ht'] = collect($items)->sum(function ($item) {
            return ($item['qty_entered'] ?? 0) * ($item['price_ht'] ?? 0);
        });
        
        $data['total_amount_ttc'] = $data['total_amount_ht'] + ($data['frais_approche'] ?? 0);
        
        return $data;
    }
}
