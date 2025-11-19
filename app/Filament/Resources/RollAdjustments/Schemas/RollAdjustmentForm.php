<?php

namespace App\Filament\Resources\RollAdjustments\Schemas;

use App\Models\Product;
use App\Models\Roll;
use App\Models\Warehouse;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class RollAdjustmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::schema());
    }

    public static function schema(): array
    {
        return [
            Repeater::make('entries')
                ->label("Lignes d'ajustement")
                ->minItems(1)
                ->columns(12)
                ->columnSpanFull()
                ->addActionLabel('Ajouter une ligne')
                ->schema([
                    Select::make('operation')
                        ->label('Opération')
                        ->options([
                            'add' => 'Créer une nouvelle bobine',
                            'remove' => 'Retirer définitivement',
                            'damage' => 'Marquer comme endommagée',
                            'restore' => 'Restaurer en stock',
                            'weight_adjust' => 'Ajuster le poids restant',
                        ])
                        ->required()
                        ->live()
                        ->columnSpan(3),

                    Select::make('warehouse_id')
                        ->label('Entrepôt')
                        ->options(fn () => Warehouse::query()->orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->required(fn (Get $get) => $get('operation') === 'add')
                        ->disabled(fn (Get $get) => $get('operation') !== 'add' && filled($get('roll_id')))
                        ->live()
                        ->columnSpan(3),

                    Select::make('product_id')
                        ->label('Produit (bobine)')
                        ->options(fn () => Product::query()->where('form_type', Product::FORM_ROLL)->orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->required(fn (Get $get) => $get('operation') === 'add')
                        ->disabled(fn (Get $get) => $get('operation') !== 'add' && filled($get('roll_id')))
                        ->live()
                        ->columnSpan(3),

                    Select::make('roll_id')
                        ->label('Bobine existante')
                        ->visible(fn (Get $get) => in_array($get('operation'), ['remove', 'damage', 'restore', 'weight_adjust'], true))
                        ->preload(false)
                        ->searchable()
                        ->getSearchResultsUsing(function ($search, $livewire) {
                            $operation = $livewire->data['operation'] ?? null;
                            $warehouseId = $livewire->data['warehouse_id'] ?? null;
                            $productId = $livewire->data['product_id'] ?? null;

                            $query = Roll::query()->with('product')->orderBy('ean_13');

                            if ($warehouseId) {
                                $query->where('warehouse_id', $warehouseId);
                            }

                            if ($productId) {
                                $query->where('product_id', $productId);
                            }

                            $statuses = match ($operation) {
                                'remove', 'damage', 'weight_adjust' => [Roll::STATUS_IN_STOCK],
                                'restore' => [Roll::STATUS_DAMAGED, Roll::STATUS_CONSUMED, Roll::STATUS_ARCHIVED],
                                default => null,
                            };

                            if ($statuses) {
                                $query->whereIn('status', $statuses);
                            }

                            if ($search) {
                                $query->where('ean_13', 'like', "%{$search}%");
                            }

                            return $query->limit(200)->get()->mapWithKeys(function (Roll $roll) {
                                $productName = $roll->product?->name ?? 'Produit';
                                return [
                                    $roll->id => sprintf('%s - %.3f kg - %.3f m', $productName, $roll->weight, $roll->length),
                                ];
                            })->toArray();
                        })
                        ->getOptionLabelUsing(function ($value) {
                            $roll = Roll::with('product')->find($value);
                            if (! $roll) {
                                return null;
                            }
                            $productName = $roll->product?->name ?? 'Produit';
                            return sprintf('%s - %.3f kg - %.3f m', $productName, $roll->weight, $roll->length);
                        })
                        ->live()
                        ->required(fn (Get $get) => in_array($get('operation'), ['remove', 'damage', 'restore', 'weight_adjust'], true))
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (! $state) {
                                return;
                            }

                            $roll = Roll::find($state);

                            if (! $roll) {
                                return;
                            }

                            $set('warehouse_id', $roll->warehouse_id);
                            $set('product_id', $roll->product_id);
                            $set('current_weight_kg', $roll->weight);
                            $set('current_length_m', $roll->length);
                        })
                        ->columnSpan(6),

                    Hidden::make('current_weight_kg'),
                    Hidden::make('current_length_m'),

                    Placeholder::make('current_weight_display')
                        ->label('Poids actuel (kg)')
                        ->content(fn (Get $get) => filled($get('current_weight_kg'))
                            ? number_format((float) $get('current_weight_kg'), 3) . ' kg'
                            : '—')
                        ->visible(fn (Get $get) => in_array($get('operation'), ['remove', 'damage', 'restore', 'weight_adjust'], true) && filled($get('roll_id')))
                        ->columnSpan(3),

                    Placeholder::make('current_length_display')
                        ->label('Longueur actuelle (m)')
                        ->content(fn (Get $get) => filled($get('current_length_m'))
                            ? number_format((float) $get('current_length_m'), 3) . ' m'
                            : '—')
                        ->visible(fn (Get $get) => in_array($get('operation'), ['remove', 'damage', 'restore', 'weight_adjust'], true) && filled($get('roll_id')))
                        ->columnSpan(3),

                    TextInput::make('new_weight_kg')
                        ->label('Poids (kg)')
                        ->numeric()
                        ->minValue(0.001)
                        ->step(0.001)
                        ->visible(fn (Get $get) => in_array($get('operation'), ['add', 'weight_adjust'], true))
                        ->required(fn (Get $get) => in_array($get('operation'), ['add', 'weight_adjust'], true))
                        ->helperText(fn (Get $get) => $get('operation') === 'add'
                            ? 'Poids initial de la bobine créée.'
                            : 'Poids restant après ajustement.')
                        ->columnSpan(3),

                    TextInput::make('new_length_m')
                        ->label('Longueur (m)')
                        ->numeric()
                        ->minValue(0.01)
                        ->step(0.01)
                        ->visible(fn (Get $get) => in_array($get('operation'), ['add', 'restore'], true))
                        ->required(fn (Get $get) => in_array($get('operation'), ['add', 'restore'], true))
                        ->helperText(fn (Get $get) => $get('operation') === 'add'
                            ? 'Longueur initiale de la bobine créée.'
                            : 'Longueur estimée au retour en stock.')
                        ->columnSpan(3),

                    Placeholder::make('weight_delta_preview')
                        ->label('Variation de poids (kg)')
                        ->visible(fn (Get $get) => $get('operation') === 'weight_adjust' && filled($get('roll_id')) && filled($get('new_weight_kg')))
                        ->content(function (Get $get) {
                            $current = (float) ($get('current_weight_kg') ?? 0);
                            $new = (float) ($get('new_weight_kg') ?? 0);
                            $delta = round($new - $current, 3);
                            $sign = $delta > 0 ? '+' : ($delta < 0 ? '-' : '');

                            return ($sign !== '' ? $sign : '') . number_format(abs($delta), 3) . ' kg';
                        })
                        ->columnSpan(3),

                    Placeholder::make('length_delta_preview')
                        ->label('Variation de longueur (m)')
                        ->visible(fn (Get $get) => $get('operation') === 'restore' && filled($get('roll_id')) && filled($get('new_length_m')))
                        ->content(function (Get $get) {
                            $current = (float) ($get('current_length_m') ?? 0);
                            $new = (float) ($get('new_length_m') ?? 0);
                            $delta = round($new - $current, 3);
                            $sign = $delta > 0 ? '+' : ($delta < 0 ? '-' : '');

                            return ($sign !== '' ? $sign : '') . number_format(abs($delta), 3) . ' m';
                        })
                        ->columnSpan(3),

                    TextInput::make('ean_13')
                        ->label('Code EAN')
                        ->visible(fn (Get $get) => $get('operation') === 'add')
                        ->required(fn (Get $get) => $get('operation') === 'add')
                        ->maxLength(64)
                        ->rules(fn (Get $get) => $get('operation') === 'add'
                            ? ['string', 'max:64', Rule::unique('rolls', 'ean_13')]
                            : [])
                        ->columnSpan(4),

                    TextInput::make('batch_number')
                        ->label('Numéro de lot')
                        ->visible(fn (Get $get) => $get('operation') === 'add')
                        ->columnSpan(4),

                    DatePicker::make('received_date')
                        ->label('Date de réception')
                        ->default(fn () => now())
                        ->visible(fn (Get $get) => $get('operation') === 'add')
                        ->required(fn (Get $get) => $get('operation') === 'add')
                        ->columnSpan(4),

                    TextInput::make('cump_value')
                        ->label('CUMP (€)')
                        ->numeric()
                        ->step(0.0001)
                        ->visible(fn (Get $get) => $get('operation') === 'add')
                        ->columnSpan(4),

                    Select::make('removal_status')
                        ->label('Statut final')
                        ->options([
                            Roll::STATUS_CONSUMED => 'Consommé',
                            Roll::STATUS_ARCHIVED => 'Archivé',
                        ])
                        ->default(Roll::STATUS_CONSUMED)
                        ->visible(fn (Get $get) => $get('operation') === 'remove')
                        ->required(fn (Get $get) => $get('operation') === 'remove')
                        ->columnSpan(4),

                    Textarea::make('reason')
                        ->label('Raison')
                        ->required()
                        ->rows(3)
                        ->columnSpan(12),

                    Textarea::make('notes')
                        ->label('Notes internes')
                        ->rows(2)
                        ->columnSpan(12),
                ]),
        ];
    }

    protected static function rollOptions(Get $get): array
    {
        $operation = $get('operation');
        $warehouseId = $get('warehouse_id');
        $productId = $get('product_id');

        $query = Roll::query()->with('product')->orderBy('ean_13');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $statuses = match ($operation) {
            'remove', 'damage', 'weight_adjust' => [Roll::STATUS_IN_STOCK],
            'restore' => [Roll::STATUS_DAMAGED, Roll::STATUS_CONSUMED, Roll::STATUS_ARCHIVED],
            default => null,
        };

        if ($statuses) {
            $query->whereIn('status', $statuses);
        }

        return $query
            ->limit(200)
            ->get()
            ->mapWithKeys(function (Roll $roll) {
                $productName = $roll->product?->name ?? 'Produit';

                return [
                    $roll->id => sprintf('%s - %.3f kg - %.3f m', $productName, $roll->weight, $roll->length),
                ];
            })
            ->toArray();
    }
}
