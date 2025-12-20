<?php

namespace App\Filament\Widgets;

use App\Models\StockMovement;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MovementTypePieChartWidget extends ChartWidget
{
    protected ?string $heading = 'Répartition des Mouvements';
    
    protected ?string $description = 'Types de mouvements (30 derniers jours)';
    
    protected int | string | array $columnSpan = 1;
    
    protected static ?int $sort = 4;
    
    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $since = Carbon::now()->subDays(30);
        
        $movements = StockMovement::query()
            ->where('performed_at', '>=', $since)
            ->select('movement_type', DB::raw('COUNT(*) as count'))
            ->groupBy('movement_type')
            ->get()
            ->pluck('count', 'movement_type');

        $typeLabels = [
            'RECEPTION' => 'Réceptions',
            'ISSUE' => 'Sorties',
            'TRANSFER' => 'Transferts',
            'RETURN' => 'Retours',
            'ADJUSTMENT' => 'Ajustements',
        ];

        $typeColors = [
            'RECEPTION' => 'rgba(34, 197, 94, 0.8)',
            'ISSUE' => 'rgba(239, 68, 68, 0.8)',
            'TRANSFER' => 'rgba(59, 130, 246, 0.8)',
            'RETURN' => 'rgba(168, 85, 247, 0.8)',
            'ADJUSTMENT' => 'rgba(251, 191, 36, 0.8)',
        ];

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($movements as $type => $count) {
            $labels[] = $typeLabels[$type] ?? $type;
            $data[] = $count;
            $colors[] = $typeColors[$type] ?? 'rgba(107, 114, 128, 0.8)';
        }

        // If no data, show placeholder
        if (empty($data)) {
            return [
                'datasets' => [
                    [
                        'data' => [1],
                        'backgroundColor' => ['rgba(107, 114, 128, 0.3)'],
                    ],
                ],
                'labels' => ['Aucun mouvement'],
            ];
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'cutout' => '60%',
        ];
    }
}
