<?php

namespace App\Filament\Widgets;

use App\Models\Roll;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BobineStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = Roll::query()
            ->select(
                DB::raw('COUNT(*) as total_count'),
                DB::raw("SUM(CASE WHEN status = 'in_stock' THEN 1 ELSE 0 END) as in_stock_count"),
                DB::raw('SUM(weight_kg) as total_weight'),
                DB::raw('SUM(length_m) as total_length'),
                DB::raw("SUM(CASE WHEN status = 'in_stock' THEN weight_kg ELSE 0 END) as in_stock_weight"),
                DB::raw("SUM(CASE WHEN status = 'in_stock' THEN length_m ELSE 0 END) as in_stock_length")
            )
            ->first();

        return [
            Stat::make('Total Bobines', $stats->total_count ?? 0)
                ->description('En stock: ' . ($stats->in_stock_count ?? 0))
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'),

            Stat::make('Poids Total', number_format($stats->total_weight ?? 0, 2, ',', ' ') . ' kg')
                ->description('En stock: ' . number_format($stats->in_stock_weight ?? 0, 2, ',', ' ') . ' kg')
                ->descriptionIcon('heroicon-m-scale')
                ->color('success'),

            Stat::make('Métrage Total', number_format($stats->total_length ?? 0, 2, ',', ' ') . ' m')
                ->description('En stock: ' . number_format($stats->in_stock_length ?? 0, 2, ',', ' ') . ' m')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('info'),
        ];
    }
}
