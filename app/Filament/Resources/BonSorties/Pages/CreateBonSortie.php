<?php

namespace App\Filament\Resources\BonSorties\Pages;

use App\Filament\Resources\BonSorties\BonSortieResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateBonSortie extends CreateRecord
{
    protected static string $resource = BonSortieResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
