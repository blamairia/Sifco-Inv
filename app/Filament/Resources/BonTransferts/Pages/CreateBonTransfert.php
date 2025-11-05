<?php

namespace App\Filament\Resources\BonTransferts\Pages;

use App\Filament\Resources\BonTransferts\BonTransfertResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBonTransfert extends CreateRecord
{
    protected static string $resource = BonTransfertResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the requesting user
        $data['requested_by_id'] = auth()->id() ?? 1;
        
        // Log the data to see what's being sent
        \Log::info('BonTransfert Create Data:', [
            'rollItems_count' => count($data['rollItems'] ?? []),
            'productItems_count' => count($data['productItems'] ?? []),
            'has_rollItems' => isset($data['rollItems']),
            'has_productItems' => isset($data['productItems']),
        ]);
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Log after creation
        \Log::info('BonTransfert Created:', [
            'id' => $this->record->id,
            'items_count' => $this->record->bonTransfertItems()->count(),
        ]);
    }
}
