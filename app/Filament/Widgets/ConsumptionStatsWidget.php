<?php

namespace App\Filament\Widgets;

use App\Models\RollLifecycleEvent;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ConsumptionStatsWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int $periodDays = 30;

    protected function getStats(): array
    {
        $since = Carbon::now()->subDays($this->periodDays);

        $stats = RollLifecycleEvent::query()
            ->where('event_type', RollLifecycleEvent::TYPE_SORTIE)
            ->where('created_at', '>=', $since)
            ->select([
                DB::raw('COUNT(DISTINCT roll_id) as consumed_rolls'),
                DB::raw('SUM(ABS(weight_delta_kg)) as consumed_weight_kg'),
                DB::raw('SUM(ABS(length_delta_m)) as consumed_length_m'),
                DB::raw('SUM(COALESCE(waste_weight_kg, 0)) as waste_weight_kg'),
                DB::raw('SUM(COALESCE(waste_length_m, 0)) as waste_length_m'),
            ])
            ->first();

        $consumedRolls = (int) ($stats->consumed_rolls ?? 0);
        $consumedWeight = (float) ($stats->consumed_weight_kg ?? 0.0);
        $consumedLength = (float) ($stats->consumed_length_m ?? 0.0);
        $wasteWeight = (float) ($stats->waste_weight_kg ?? 0.0);
        $weightPerDay = $this->periodDays > 0 ? $consumedWeight / $this->periodDays : 0.0;
        $wasteRate = $consumedWeight > 0 ? ($wasteWeight / $consumedWeight) * 100 : 0.0;

        return [
            Stat::make('Bobines consommées (30 j)', number_format($consumedRolls, 0, ',', ' '))
                ->description('Poids total: ' . number_format($consumedWeight, 2, ',', ' ') . ' kg')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),

            Stat::make('Poids consommé / jour', number_format($weightPerDay, 2, ',', ' ') . ' kg')
                ->description('Métrage total: ' . number_format($consumedLength, 2, ',', ' ') . ' m')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),

            Stat::make('Taux de gaspillage', number_format($wasteRate, 2, ',', ' ') . ' %')
                ->description('Déchets: ' . number_format($wasteWeight, 2, ',', ' ') . ' kg')
                ->descriptionIcon('heroicon-m-fire')
                ->color($wasteRate > 5 ? 'danger' : 'success'),
        ];
    }
}
