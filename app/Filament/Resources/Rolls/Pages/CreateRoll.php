<?php

namespace App\Filament\Resources\Rolls\Pages;

use App\Filament\Resources\Rolls\RollResource;
use App\Models\Roll;
use Filament\Resources\Pages\CreateRecord;

class CreateRoll extends CreateRecord
{
    protected static string $resource = RollResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = Roll::STATUS_IN_STOCK;

        return $data;
    }
}
