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
}
