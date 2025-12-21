<?php

namespace App\Filament\Widgets;

use App\Models\StockMovement;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DailyMovementsTrendWidget extends ChartWidget
{
    protected ?string $heading = 'Tendance des Mouvements';
    
    protected ?string $description = 'Activité quotidienne sur les 14 derniers jours';
    
    protected int | string | array $columnSpan = 1;
    
    protected static ?int $sort = 2;
    
    protected ?string $maxHeight = '280px';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = collect();
        $counts = collect();
        
        // Get last 14 days of data
        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayLabel = $date->format('d/m');
            $days->push($dayLabel);
            
            $count = StockMovement::query()
                ->whereDate('performed_at', $date->toDateString())
                ->count();
            $counts->push($count);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Mouvements',
                    'data' => $counts->toArray(),
                    'fill' => true,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'pointBackgroundColor' => 'rgb(59, 130, 246)',
                    'pointBorderColor' => '#ffffff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ],
            ],
            'labels' => $days->toArray(),
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
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
