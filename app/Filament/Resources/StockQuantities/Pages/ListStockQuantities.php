<?php

namespace App\Filament\Resources\StockQuantities\Pages;

use App\Filament\Resources\StockQuantities\StockQuantityResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListStockQuantities extends ListRecords
{
    protected static string $resource = StockQuantityResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
