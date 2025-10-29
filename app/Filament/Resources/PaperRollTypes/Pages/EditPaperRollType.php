<?php

namespace App\Filament\Resources\PaperRollTypes\Pages;

use App\Filament\Resources\PaperRollTypes\PaperRollTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaperRollType extends EditRecord
{
    protected static string $resource = PaperRollTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
