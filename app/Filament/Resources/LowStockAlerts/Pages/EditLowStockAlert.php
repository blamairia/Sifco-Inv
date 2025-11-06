<?php

namespace App\Filament\Resources\LowStockAlerts\Pages;

use App\Filament\Resources\LowStockAlerts\LowStockAlertResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLowStockAlert extends EditRecord
{
    protected static string $resource = LowStockAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
