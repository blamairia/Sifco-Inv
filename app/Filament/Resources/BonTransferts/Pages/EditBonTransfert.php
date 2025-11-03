<?php

namespace App\Filament\Resources\BonTransferts\Pages;

use App\Filament\Resources\BonTransferts\BonTransfertResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBonTransfert extends EditRecord
{
    protected static string $resource = BonTransfertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
