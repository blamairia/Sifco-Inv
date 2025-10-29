<?php

namespace App\Filament\Resources\RollSpecifications\Pages;

use App\Filament\Resources\RollSpecifications\RollSpecificationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRollSpecification extends EditRecord
{
    protected static string $resource = RollSpecificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
