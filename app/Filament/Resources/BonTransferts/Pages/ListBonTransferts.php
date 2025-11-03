<?php

namespace App\Filament\Resources\BonTransferts\Pages;

use App\Filament\Resources\BonTransferts\BonTransfertResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBonTransferts extends ListRecords
{
    protected static string $resource = BonTransfertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
