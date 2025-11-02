<?php

namespace App\Filament\Resources\StockQuantities\Pages;

use App\Filament\Resources\StockQuantities\StockQuantityResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewStockQuantity extends ViewRecord
{
    protected static string $resource = StockQuantityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_movements')
                ->label('Voir Mouvements')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->url(fn () => route('filament.admin.resources.stock-movements.index', [
                    'tableFilters' => [
                        'product_id' => ['value' => $this->record->product_id],
                        'warehouse_id' => ['value' => $this->record->warehouse_id],
                    ]
                ])),
        ];
    }
}
