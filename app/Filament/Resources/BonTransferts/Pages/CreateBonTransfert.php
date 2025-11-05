<?php

namespace App\Filament\Resources\BonTransferts\Pages;

use App\Filament\Resources\BonTransferts\BonTransfertResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateBonTransfert extends CreateRecord
{
    protected static string $resource = BonTransfertResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the requesting user
        $data['requested_by_id'] = auth()->id() ?? 1;
        
        // Log the data to see what's being passed
        Log::info('CreateBonTransfert - Data before create:', [
            'has_rollItems' => isset($data['rollItems']),
            'rollItems_count' => isset($data['rollItems']) ? count($data['rollItems']) : 0,
            'has_productItems' => isset($data['productItems']),
            'productItems_count' => isset($data['productItems']) ? count($data['productItems']) : 0,
            'has_bonTransfertItems' => isset($data['bonTransfertItems']),
            'bonTransfertItems_count' => isset($data['bonTransfertItems']) ? count($data['bonTransfertItems']) : 0,
        ]);
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Log after create to verify items were saved
        $this->record->refresh();
        Log::info('CreateBonTransfert - After create:', [
            'bon_id' => $this->record->id,
            'bon_number' => $this->record->bon_number,
            'items_count' => $this->record->bonTransfertItems()->count(),
        ]);
    }
}
