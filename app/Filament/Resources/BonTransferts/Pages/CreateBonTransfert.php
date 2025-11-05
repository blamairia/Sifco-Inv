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
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('BonTransfert - Form data before create:', $data);
        return $data;
    }

    protected function afterCreate(): void
    {
        $data = $this->form->getState();
        
        Log::info('BonTransfert - After create - Form state:', [
            'id' => $this->record->id,
            'has_rollItems' => isset($data['rollItems']),
            'rollItems_count' => count($data['rollItems'] ?? []),
            'has_productItems' => isset($data['productItems']),
            'productItems_count' => count($data['productItems'] ?? []),
            'items_in_db' => $this->record->bonTransfertItems()->count(),
        ]);

        // Manually save items if they weren't saved by the relationship
        $rollItems = $data['rollItems'] ?? [];
        $productItems = $data['productItems'] ?? [];

        foreach ($rollItems as $item) {
            $this->record->bonTransfertItems()->create([
                'item_type' => 'roll',
                'product_id' => $item['product_id'],
                'roll_id' => $item['roll_id'],
                'qty_transferred' => $item['qty_transferred'] ?? 1,
                'cump_at_transfer' => $item['cump_at_transfer'],
            ]);
        }

        foreach ($productItems as $item) {
            $this->record->bonTransfertItems()->create([
                'item_type' => 'product',
                'product_id' => $item['product_id'],
                'roll_id' => null,
                'qty_transferred' => $item['qty_transferred'],
                'cump_at_transfer' => $item['cump_at_transfer'],
            ]);
        }

        Log::info('BonTransfert - After manual save:', [
            'items_in_db_after' => $this->record->bonTransfertItems()->count(),
        ]);
    }
}
