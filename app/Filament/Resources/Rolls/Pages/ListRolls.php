<?php

namespace App\Filament\Resources\Rolls\Pages;

use App\Filament\Resources\Rolls\RollResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRolls extends ListRecords
{
    protected static string $resource = RollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
