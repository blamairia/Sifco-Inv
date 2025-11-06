<?php

namespace App\Filament\Resources\RollAdjustments\Pages;

use App\Filament\Resources\RollAdjustments\RollAdjustmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRollAdjustment extends EditRecord
{
    protected static string $resource = RollAdjustmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
