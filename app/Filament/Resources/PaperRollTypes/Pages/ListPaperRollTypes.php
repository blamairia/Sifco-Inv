<?php

namespace App\Filament\Resources\PaperRollTypes\Pages;

use App\Filament\Resources\PaperRollTypes\PaperRollTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaperRollTypes extends ListRecords
{
    protected static string $resource = PaperRollTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
