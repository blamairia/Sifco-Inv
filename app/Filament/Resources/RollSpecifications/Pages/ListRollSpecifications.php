<?php

namespace App\Filament\Resources\RollSpecifications\Pages;

use App\Filament\Resources\RollSpecifications\RollSpecificationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRollSpecifications extends ListRecords
{
    protected static string $resource = RollSpecificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
