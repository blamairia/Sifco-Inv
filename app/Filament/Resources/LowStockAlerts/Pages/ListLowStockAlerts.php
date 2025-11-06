<?php

namespace App\Filament\Resources\LowStockAlerts\Pages;

use App\Filament\Resources\LowStockAlerts\LowStockAlertResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLowStockAlerts extends ListRecords
{
    protected static string $resource = LowStockAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
