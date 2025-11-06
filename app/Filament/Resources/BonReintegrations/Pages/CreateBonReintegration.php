<?php

namespace App\Filament\Resources\BonReintegrations\Pages;

use App\Filament\Resources\BonReintegrations\BonReintegrationResource;
use App\Models\BonReintegration;
use Filament\Resources\Pages\CreateRecord;

class CreateBonReintegration extends CreateRecord
{
    protected static string $resource = BonReintegrationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'draft';
        $data['bon_number'] = $data['bon_number'] ?? BonReintegration::generateBonNumber();

        return $data;
    }
}
