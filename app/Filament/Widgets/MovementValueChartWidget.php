<?php

namespace App\Filament\Widgets;

use App\Models\StockMovement;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MovementValueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Valeur des Mouvements';
    
    protected ?string $description = 'Valeur totale transférée par mois (DZD)';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 6;
    
    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $months = collect();
        $values = collect();
        
        // Get last 6 months of value data
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthLabel = $date->translatedFormat('M Y');
            $months->push($monthLabel);
            
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            
            // Sum of value_moved
            $totalValue = StockMovement::query()
                ->whereBetween('performed_at', [$startOfMonth, $endOfMonth])
                ->sum('value_moved') ?? 0;
            
            $values->push(round(abs($totalValue), 2));
        }

        return [
            'datasets' => [
                [
                    'label' => 'Valeur (DZD)',
                    'data' => $values->toArray(),
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(168, 85, 247, 0.7)',
                        'rgba(251, 191, 36, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(20, 184, 166, 0.7)',
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(59, 130, 246)',
                        'rgb(168, 85, 247)',
                        'rgb(251, 191, 36)',
                        'rgb(239, 68, 68)',
                        'rgb(20, 184, 166)',
                    ],
                    'borderWidth' => 2,
                    'borderRadius' => 6,
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
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
