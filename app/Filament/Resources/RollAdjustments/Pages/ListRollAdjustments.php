<?php

namespace App\Filament\Resources\RollAdjustments\Pages;

use App\Filament\Resources\RollAdjustments\RollAdjustmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRollAdjustments extends ListRecords
{
    protected static string $resource = RollAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
