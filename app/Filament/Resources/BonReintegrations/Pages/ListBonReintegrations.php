<?php

namespace App\Filament\Resources\BonReintegrations\Pages;

use App\Filament\Resources\BonReintegrations\BonReintegrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBonReintegrations extends ListRecords
{
    protected static string $resource = BonReintegrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
