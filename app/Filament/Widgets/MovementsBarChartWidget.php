<?php

namespace App\Filament\Widgets;

use App\Models\StockMovement;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MovementsBarChartWidget extends ChartWidget
{
    protected ?string $heading = 'Mouvements par Mois';
    
    protected ?string $description = 'Entrées vs Sorties sur les 6 derniers mois';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 4;
    
    protected ?string $maxHeight = '300px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $months = collect();
        $receptions = collect();
        $issues = collect();
        
        // Get last 6 months of data
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthLabel = $date->translatedFormat('M Y');
            $months->push($monthLabel);
            
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            
            // Count receptions (entries)
            $receptionCount = StockMovement::query()
                ->where('movement_type', 'RECEPTION')
                ->whereBetween('performed_at', [$startOfMonth, $endOfMonth])
                ->count();
            $receptions->push($receptionCount);
            
            // Count issues (exits)
            $issueCount = StockMovement::query()
                ->where('movement_type', 'ISSUE')
                ->whereBetween('performed_at', [$startOfMonth, $endOfMonth])
                ->count();
            $issues->push($issueCount);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Entrées (Réceptions)',
                    'data' => $receptions->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Sorties',
                    'data' => $issues->toArray(),
                    'backgroundColor' => 'rgba(239, 68, 68, 0.7)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
