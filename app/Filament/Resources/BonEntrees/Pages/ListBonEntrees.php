<?php

namespace App\Filament\Resources\BonEntrees\Pages;

use App\Filament\Resources\BonEntrees\BonEntreeResource;
use Filament\Resources\Pages\ListRecords;

class ListBonEntrees extends ListRecords
{
    protected static string $resource = BonEntreeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
