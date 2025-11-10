<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Roll;
use App\Models\Warehouse;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use BackedEnum;
use UnitEnum;

class BobineDashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.pages.bobine-dashboard';

    protected static ?string $navigationLabel = 'Tableau de Bord Bobines';

    protected static ?string $title = 'Tableau de Bord Bobines';

    protected static ?int $navigationSort = 2;

    protected static UnitEnum|string|null $navigationGroup = 'Rapports';

    public $warehouseFilter = null;
    public $categoryFilter = null;
    public $groupBy = 'laize';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->getTableColumns())
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse_id')
                    ->label('Entrepôt')
                    ->options(fn () => Warehouse::query()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->query(fn (Builder $query, array $data): Builder =>
                        $query->when(
                            $data['value'] ?? null,
                            fn (Builder $innerQuery, $value) => $innerQuery->where('warehouse_id', $value)
                        )
                    ),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Catégorie')
                    ->options(fn () => Category::query()->orderBy('name')->pluck('name', 'id')->toArray())
                    ->query(fn (Builder $query, array $data): Builder =>
                        $query->when(
                            $data['value'] ?? null,
                            fn (Builder $innerQuery, $value) => $innerQuery->where('categories.id', $value)
                        )
                    ),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        Roll::STATUS_IN_STOCK => 'En stock',
                        Roll::STATUS_RESERVED => 'Réservé',
                        Roll::STATUS_DAMAGED => 'Endommagé',
                    ])
                    ->query(fn (Builder $query, array $data): Builder =>
                        $query->when(
                            $data['value'] ?? null,
                            fn (Builder $innerQuery, $value) => $innerQuery->where('rolls.status', $value),
                            fn (Builder $innerQuery) => $innerQuery->whereNotIn('rolls.status', [
                                Roll::STATUS_CONSUMED,
                                Roll::STATUS_ARCHIVED,
                            ])
                        )
                    ),
            ])
            ->defaultSort('roll_count', 'desc')
            ->defaultKeySort(false)
            ->poll('30s');
    }

    protected function getTableQuery(): Builder
    {
        return Roll::query()
            ->whereNotIn('rolls.status', [
                Roll::STATUS_CONSUMED,
                Roll::STATUS_ARCHIVED,
            ])
            ->select([
                DB::raw('MIN(rolls.id) as id'),
                'rolls.warehouse_id',
                'rolls.product_id',
                DB::raw('warehouses.name as warehouse_name'),
                DB::raw('products.name as product_name'),
                DB::raw('products.laize as product_laize'),
                DB::raw('products.grammage as product_grammage'),
                DB::raw('products.type_papier as product_paper_type'),
                DB::raw('products.flute as product_flute'),
                DB::raw('categories.name as category_name'),
                DB::raw('COUNT(DISTINCT rolls.id) as roll_count'),
                DB::raw('SUM(COALESCE(rolls.weight_kg, 0)) as total_weight_kg'),
                DB::raw('SUM(COALESCE(rolls.length_m, 0)) as total_length_m'),
            ])
            ->leftJoin('products', 'rolls.product_id', '=', 'products.id')
            ->leftJoin('product_category as primary_categories', function ($join) {
                $join->on('primary_categories.product_id', '=', 'products.id')
                    ->where('primary_categories.is_primary', true);
            })
            ->leftJoin('categories', 'categories.id', '=', 'primary_categories.category_id')
            ->leftJoin('warehouses', 'warehouses.id', '=', 'rolls.warehouse_id')
            ->groupBy([
                'rolls.warehouse_id',
                'rolls.product_id',
                'warehouses.name',
                'products.name',
                'products.laize',
                'products.grammage',
                'products.type_papier',
                'products.flute',
                'categories.name',
            ]);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('product_name')
                ->label('Produit')
                ->searchable()
                ->sortable(),
            TextColumn::make('category_name')
                ->label('Catégorie')
                ->searchable()
                ->sortable(),
            TextColumn::make('warehouse_name')
                ->label('Entrepôt')
                ->sortable(),
            TextColumn::make('product_laize')
                ->label('Laize (mm)')
                ->numeric(0)
                ->sortable()
                ->summarize([
                    Tables\Columns\Summarizers\Average::make()
                        ->label('Moyenne')
                        ->numeric(0),
                ]),
            TextColumn::make('product_grammage')
                ->label('Grammage (g/m²)')
                ->numeric(0)
                ->sortable()
                ->summarize([
                    Tables\Columns\Summarizers\Average::make()
                        ->label('Moyenne')
                        ->numeric(0),
                ]),
            TextColumn::make('product_paper_type')
                ->label('Type de papier')
                ->sortable()
                ->formatStateUsing(fn ($state) => $state ?? 'N/A'),
            TextColumn::make('product_flute')
                ->label('Cannelure')
                ->sortable()
                ->formatStateUsing(fn ($state) => $state ?? 'N/A'),
            TextColumn::make('roll_count')
                ->label('Nombre de bobines')
                ->numeric(0)
                ->sortable()
                ->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->label('Total')
                        ->numeric(0),
                ]),
            TextColumn::make('total_weight_kg')
                ->label('Poids total (kg)')
                ->numeric(2)
                ->sortable()
                ->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->label('Total global')
                        ->numeric(2),
                ]),
            TextColumn::make('total_length_m')
                ->label('Métrage total (m)')
                ->numeric(2)
                ->sortable()
                ->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->label('Total global')
                        ->numeric(2),
                ]),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\BobineStatsWidget::class,
        ];
    }
}
