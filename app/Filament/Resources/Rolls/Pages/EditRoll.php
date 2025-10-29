<?php

namespace App\Filament\Resources\Rolls\Pages;

use App\Filament\Resources\Rolls\RollResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRoll extends EditRecord
{
    protected static string $resource = RollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
