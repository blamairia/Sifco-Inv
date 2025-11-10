<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Roll;
use App\Models\Warehouse;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BobineDashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.bobine-dashboard';

    protected static ?string $navigationLabel = 'Tableau de Bord Bobines';

    protected static ?string $title = 'Tableau de Bord Bobines';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Rapports';

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
                    ->options(Warehouse::pluck('name', 'id'))
                    ->query(function (Builder $query, $state) {
                        if ($state['value']) {
                            $query->where('warehouse_id', $state['value']);
                        }
                    }),
                Tables\Filters\SelectFilter::make('product.category_id')
                    ->label('Catégorie')
                    ->relationship('product.category', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'in_stock' => 'En stock',
                        'reserved' => 'Réservé',
                        'in_transit' => 'En transit',
                        'consumed' => 'Consommé',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    protected function getTableQuery(): Builder
    {
        return Roll::query()
            ->with(['product.category', 'warehouse'])
            ->select(
                'rolls.*',
                DB::raw('products.name as product_name'),
                DB::raw('categories.name as category_name')
            )
            ->leftJoin('products', 'rolls.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('ean_13')
                ->label('EAN-13')
                ->searchable()
                ->sortable(),
            TextColumn::make('product_name')
                ->label('Produit')
                ->searchable()
                ->sortable(),
            TextColumn::make('category_name')
                ->label('Catégorie')
                ->searchable()
                ->sortable(),
            TextColumn::make('warehouse.name')
                ->label('Entrepôt')
                ->sortable(),
            TextColumn::make('laize')
                ->label('Laize (cm)')
                ->sortable()
                ->summarize([
                    Tables\Columns\Summarizers\Count::make(),
                ]),
            TextColumn::make('grammage')
                ->label('Grammage (g/m²)')
                ->sortable()
                ->summarize([
                    Tables\Columns\Summarizers\Average::make()
                        ->label('Moyenne')
                        ->numeric(decimals: 0),
                ]),
            TextColumn::make('paper_type')
                ->label('Type')
                ->sortable()
                ->formatStateUsing(fn($state) => $state ?? 'N/A'),
            TextColumn::make('weight_kg')
                ->label('Poids (kg)')
                ->numeric(decimals: 2)
                ->sortable()
                ->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->label('Total')
                        ->numeric(decimals: 2),
                ]),
            TextColumn::make('length_m')
                ->label('Métrage (m)')
                ->numeric(decimals: 2)
                ->sortable()
                ->summarize([
                    Tables\Columns\Summarizers\Sum::make()
                        ->label('Total')
                        ->numeric(decimals: 2),
                ]),
            TextColumn::make('status')
                ->label('Statut')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'in_stock' => 'success',
                    'reserved' => 'warning',
                    'in_transit' => 'info',
                    'consumed' => 'gray',
                    default => 'gray',
                })
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    'in_stock' => 'En stock',
                    'reserved' => 'Réservé',
                    'in_transit' => 'En transit',
                    'consumed' => 'Consommé',
                    default => $state,
                }),
            TextColumn::make('quality')
                ->label('Qualité')
                ->sortable(),
            TextColumn::make('received_date')
                ->label('Date réception')
                ->date('d/m/Y')
                ->sortable(),
        ];
    }

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\BobineStatsWidget::class,
        ];
    }
}
