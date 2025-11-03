<?php

namespace App\Filament\Resources\BonTransferts\Pages;

use App\Filament\Resources\BonTransferts\BonTransfertResource;
use Filament\Resources\Pages\CreateRecord;

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
        
        return $data;
    }
}
