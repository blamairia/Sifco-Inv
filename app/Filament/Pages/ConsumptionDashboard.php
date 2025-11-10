<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\RollLifecycleEvent;
use App\Models\Warehouse;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use UnitEnum;

class ConsumptionDashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationLabel = 'Tableau de Consommation';

    protected static ?string $title = 'Tableau de Consommation';

    protected static ?int $navigationSort = 3;

    protected static UnitEnum|string|null $navigationGroup = 'Rapports';

    protected string $view = 'filament.pages.consumption-dashboard';

    protected int $defaultPeriodDays = 30;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->defaultSort('consumed_weight_kg', 'desc')
            ->defaultKeySort(false)
            ->poll('60s');
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('period')
                ->label('Période')
                ->options([
                    '7' => '7 jours',
                    '30' => '30 jours',
                    '90' => '90 jours',
                    '180' => '180 jours',
                    '365' => '12 mois',
                ])
                ->default('30')
                ->query(function (Builder $query, array $data): Builder {
                    $days = (int) ($data['value'] ?? $this->defaultPeriodDays);

                    return $query->where('roll_lifecycle_events.created_at', '>=', Carbon::now()->subDays($days));
                }),
            SelectFilter::make('warehouse_id')
                ->label('Entrepôt')
                ->options(fn () => Warehouse::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->query(function (Builder $query, array $data): Builder {
                    $value = $data['value'] ?? null;

                    return $query->when($value, fn (Builder $innerQuery) => $innerQuery
                        ->where(function (Builder $subQuery) use ($value) {
                            $subQuery->where('roll_lifecycle_events.warehouse_from_id', $value)
                                ->orWhere('rolls.warehouse_id', $value);
                        })
                    );
                }),
            SelectFilter::make('category_id')
                ->label('Catégorie')
                ->options(fn () => Category::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->query(fn (Builder $query, array $data): Builder =>
                    $query->when(
                        $data['value'] ?? null,
                        fn (Builder $innerQuery, $value) => $innerQuery->where('categories.id', $value)
                    )
                ),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return RollLifecycleEvent::query()
            ->where('roll_lifecycle_events.event_type', RollLifecycleEvent::TYPE_SORTIE)
            ->join('rolls', 'roll_lifecycle_events.roll_id', '=', 'rolls.id')
            ->join('products', 'rolls.product_id', '=', 'products.id')
            ->leftJoin('product_category as primary_categories', function ($join) {
                $join->on('primary_categories.product_id', '=', 'products.id')
                    ->where('primary_categories.is_primary', true);
            })
            ->leftJoin('categories', 'categories.id', '=', 'primary_categories.category_id')
            ->leftJoin('warehouses as source_warehouses', 'source_warehouses.id', '=', 'roll_lifecycle_events.warehouse_from_id')
            ->leftJoin('warehouses as current_warehouses', 'current_warehouses.id', '=', 'rolls.warehouse_id')
            ->select([
                DB::raw('MIN(roll_lifecycle_events.id) as id'),
                DB::raw('COALESCE(roll_lifecycle_events.warehouse_from_id, rolls.warehouse_id) as warehouse_id'),
                DB::raw('COALESCE(source_warehouses.name, current_warehouses.name) as warehouse_name'),
                'products.id as product_id',
                DB::raw('products.name as product_name'),
                DB::raw('categories.id as category_id'),
                DB::raw('categories.name as category_name'),
                DB::raw('COUNT(DISTINCT roll_lifecycle_events.roll_id) as consumed_rolls'),
                DB::raw('SUM(ABS(roll_lifecycle_events.weight_delta_kg)) as consumed_weight_kg'),
                DB::raw('SUM(ABS(roll_lifecycle_events.length_delta_m)) as consumed_length_m'),
                DB::raw('CASE WHEN COUNT(DISTINCT roll_lifecycle_events.roll_id) > 0 THEN SUM(ABS(roll_lifecycle_events.weight_delta_kg)) / COUNT(DISTINCT roll_lifecycle_events.roll_id) ELSE 0 END as avg_weight_per_roll'),
                DB::raw('CASE WHEN COUNT(DISTINCT roll_lifecycle_events.roll_id) > 0 THEN SUM(ABS(roll_lifecycle_events.length_delta_m)) / COUNT(DISTINCT roll_lifecycle_events.roll_id) ELSE 0 END as avg_length_per_roll'),
                DB::raw('SUM(COALESCE(roll_lifecycle_events.waste_weight_kg, 0)) as waste_weight_kg'),
                DB::raw('SUM(COALESCE(roll_lifecycle_events.waste_length_m, 0)) as waste_length_m'),
                DB::raw('CASE WHEN SUM(ABS(roll_lifecycle_events.weight_delta_kg)) > 0 THEN (SUM(COALESCE(roll_lifecycle_events.waste_weight_kg, 0)) / SUM(ABS(roll_lifecycle_events.weight_delta_kg))) * 100 ELSE 0 END as waste_rate_percent'),
            ])
            ->groupBy([
                DB::raw('COALESCE(roll_lifecycle_events.warehouse_from_id, rolls.warehouse_id)'),
                DB::raw('COALESCE(source_warehouses.name, current_warehouses.name)'),
                'products.id',
                'products.name',
                'categories.id',
                'categories.name',
            ]);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('warehouse_name')
                ->label('Entrepôt')
                ->sortable(),
            TextColumn::make('product_name')
                ->label('Produit')
                ->searchable()
                ->sortable(),
            TextColumn::make('category_name')
                ->label('Catégorie')
                ->sortable(),
            TextColumn::make('consumed_rolls')
                ->label('Bobines consommées')
                ->numeric(0)
                ->sortable()
                ->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->label('Total')
                        ->numeric(0),
                ]),
            TextColumn::make('consumed_weight_kg')
                ->label('Poids consommé (kg)')
                ->numeric(2)
                ->sortable()
                ->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->label('Total (kg)')
                        ->numeric(2),
                ]),
            TextColumn::make('consumed_length_m')
                ->label('Métrage consommé (m)')
                ->numeric(2)
                ->sortable()
                ->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->label('Total (m)')
                        ->numeric(2),
                ]),
            TextColumn::make('avg_weight_per_roll')
                ->label('Poids moyen / bobine (kg)')
                ->numeric(2)
                ->sortable(),
            TextColumn::make('avg_length_per_roll')
                ->label('Métrage moyen / bobine (m)')
                ->numeric(2)
                ->sortable(),
            TextColumn::make('weight_per_day')
                ->label('Poids / jour (kg)')
                ->getStateUsing(fn ($record) => $this->formatPerDay($record->consumed_weight_kg ?? 0.0))
                ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' '))
                ->sortable(false),
            TextColumn::make('waste_weight_kg')
                ->label('Déchets (kg)')
                ->numeric(2)
                ->sortable()
                ->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->label('Total déchets (kg)')
                        ->numeric(2),
                ]),
            TextColumn::make('waste_rate_percent')
                ->label('Taux de gaspillage (%)')
                ->formatStateUsing(fn ($state) => number_format((float) $state, 2, ',', ' ') . ' %')
                ->sortable(),
        ];
    }

    protected function formatPerDay(float $weight): float
    {
        $days = $this->getActivePeriodDays();

        if ($days <= 0) {
            return 0.0;
        }

        return $weight / $days;
    }

    protected function getActivePeriodDays(): int
    {
        $value = Arr::get($this->tableFilters ?? [], 'period.value', Arr::get($this->tableFilters ?? [], 'period'));
        $days = (int) ($value ?: $this->defaultPeriodDays);

        return in_array($days, [7, 30, 90, 180, 365], true) ? $days : $this->defaultPeriodDays;
    }

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\ConsumptionStatsWidget::class,
        ];
    }
}
