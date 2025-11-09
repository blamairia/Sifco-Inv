<?php

namespace App\Filament\Resources\BonReintegrations\Schemas;

use App\Models\BonReintegration;
use App\Models\Product;
use App\Models\Roll;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BonReintegrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make("Statut")
                ->schema([
                    Placeholder::make('status_display')
                        ->label('Statut actuel')
                        ->content(fn (?BonReintegration $record) => match ($record?->status) {
                            'draft' => '🟡 Brouillon',
                            'received' => '🟢 Réceptionné',
                            'verified' => '🔵 Vérifié',
                            'archived' => '⚪ Archivé',
                            'cancelled' => '🔴 Annulé',
                            default => 'Nouveau',
                        }),
                    Placeholder::make('verified_info')
                        ->label('Vérifié le')
                        ->content(fn (?BonReintegration $record) => $record?->verified_at?->format('d/m/Y H:i') ?? '-')
                        ->visible(fn (?BonReintegration $record) => $record?->status === 'verified'),
                ])
                ->columns(2)
                ->visible(fn (?BonReintegration $record) => $record !== null),

            Section::make('Informations générales')
                ->schema([
                    TextInput::make('bon_number')
                        ->label('N° Bon de Réintégration')
                        ->default(fn () => BonReintegration::generateBonNumber())
                        ->maxLength(50)
                        ->required()
                        ->disabled()
                        ->dehydrated(),

                    Select::make('bon_sortie_id')
                        ->label('Bon de Sortie d\'origine')
                        ->relationship('bonSortie', 'bon_number')
                        ->searchable()
                        ->preload()
                        ->helperText('Optionnel : lie ce retour à un bon de sortie existant.')
                        ->columnSpan(2),

                    Select::make('warehouse_id')
                        ->label('Entrepôt de retour')
                        ->relationship('warehouse', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpan(2),

                    DatePicker::make('return_date')
                        ->label('Date de retour')
                        ->displayFormat('d/m/Y')
                        ->default(fn () => now()),

                    TextInput::make('physical_condition')
                        ->label('État physique')
                        ->helperText('Notes rapides sur l\'état des marchandises retournées.')
                        ->maxLength(255),

                    TextInput::make('cump_at_return')
                        ->label('CUMP de référence')
                        ->numeric()
                        ->prefix('DH')
                        ->helperText('Valeur utilisée pour les produits standards si aucune autre n\'est fournie.'),

                    Hidden::make('status')
                        ->default('draft'),
                ])
                ->columns(2),

            Section::make('Bobines à réintégrer')
                ->description('Sélectionnez les bobines à remettre en stock et ajustez le poids retourné si besoin.')
                ->schema([
                    Repeater::make('rollItems')
                        ->relationship(
                            name: 'bonReintegrationItems',
                            modifyQueryUsing: fn ($query) => $query->where('item_type', 'roll')
                        )
                        ->schema([
                            Select::make('roll_id')
                                ->label('Bobine')
                                ->relationship(
                                    name: 'roll',
                                    titleAttribute: 'ean_13',
                                    modifyQueryUsing: fn ($query) => $query->whereIn('status', [Roll::STATUS_CONSUMED, Roll::STATUS_DAMAGED])
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->reactive()
                                ->disabled(fn ($record) => $record && $record->bonReintegration && $record->bonReintegration->status !== 'draft')
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $roll = $state ? Roll::with('product')->find($state) : null;

                                    $set('product_id', $roll?->product_id);
                                    $set('previous_weight_kg', $roll?->weight ?? null);
                                    $set('returned_weight_kg', $roll?->weight ?? null);
                                    $set('previous_length_m', $roll?->length ?? null);
                                    $set('returned_length_m', $roll?->length ?? null);
                                    $set('cump_at_return', $roll?->cump ?? null);
                                })
                                ->columnSpan(3),

                            Placeholder::make('roll_product_name')
                                ->label('Produit')
                                ->content(function (callable $get) {
                                    $productId = $get('product_id');
                                    return $productId ? Product::find($productId)?->name ?? '-' : '-';
                                })
                                ->columnSpan(3),

                            Hidden::make('product_id'),

                            TextInput::make('previous_weight_kg')
                                ->label('Poids avant (kg)')
                                ->numeric()
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(2),

                            TextInput::make('returned_weight_kg')
                                ->label('Poids retourné (kg)')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->step(0.01)
                                ->suffix('kg')
                                ->columnSpan(2)
                                ->disabled(fn ($record) => $record && $record->bonReintegration && $record->bonReintegration->status !== 'draft'),

                            TextInput::make('previous_length_m')
                                ->label('Longueur avant (m)')
                                ->numeric()
                                ->disabled()
                                ->dehydrated()
                                ->columnSpan(2),

                            TextInput::make('returned_length_m')
                                ->label('Longueur retournée (m)')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->step(0.01)
                                ->suffix('m')
                                ->columnSpan(2)
                                ->disabled(fn ($record) => $record && $record->bonReintegration && $record->bonReintegration->status !== 'draft'),

                            TextInput::make('cump_at_return')
                                ->label('CUMP appliqué')
                                ->numeric()
                                ->prefix('DH')
                                ->columnSpan(2)
                                ->disabled(fn ($record) => $record && $record->bonReintegration && $record->bonReintegration->status !== 'draft'),

                            TextInput::make('qty_returned')
                                ->default(1)
                                ->numeric()
                                ->hidden()
                                ->dehydrated(),
                        ])
                        ->columns(16)
                        ->defaultItems(0)
                        ->addActionLabel('➕ Ajouter une bobine')
                        ->reorderable(false)
                        ->collapsible()
                        ->disabled(fn (?BonReintegration $record) => $record?->status !== 'draft')
                        ->itemLabel(fn (array $state): ?string => match (true) {
                            isset($state['roll_id']) => 'Bobine ' . (Roll::find($state['roll_id'])?->ean_13 ?? '#?'),
                            default => 'Nouvelle bobine',
                        })
                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                            $data['item_type'] = 'roll';
                            $data['qty_returned'] = $data['qty_returned'] ?? 1;
                            return $data;
                        })
                        ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                            $data['item_type'] = 'roll';
                            return $data;
                        }),
                ])
                ->columnSpanFull()
                ->collapsible(),

            Section::make('Produits standards')
                ->description('Pour les produits non bobines, indiquez la quantité retournée et la valeur de référence.')
                ->schema([
                    Repeater::make('productItems')
                        ->relationship(
                            name: 'bonReintegrationItems',
                            modifyQueryUsing: fn ($query) => $query->where('item_type', 'product')
                        )
                        ->schema([
                            Select::make('product_id')
                                ->label('Produit')
                                ->relationship(
                                    name: 'product',
                                    titleAttribute: 'name',
                                    modifyQueryUsing: fn ($query) => $query->where('is_roll', false)
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->disabled(fn ($record) => $record && $record->bonReintegration && $record->bonReintegration->status !== 'draft')
                                ->columnSpan(5),

                            TextInput::make('qty_returned')
                                ->label('Quantité retournée')
                                ->numeric()
                                ->required()
                                ->minValue(0.01)
                                ->step(0.01)
                                ->columnSpan(3)
                                ->disabled(fn ($record) => $record && $record->bonReintegration && $record->bonReintegration->status !== 'draft'),

                            TextInput::make('cump_at_return')
                                ->label('CUMP appliqué')
                                ->numeric()
                                ->prefix('DH')
                                ->columnSpan(2)
                                ->disabled(fn ($record) => $record && $record->bonReintegration && $record->bonReintegration->status !== 'draft')
                                ->helperText('Laisser vide pour utiliser la valeur par défaut du bon.'),

                            Placeholder::make('line_value')
                                ->label('Valeur estimée')
                                ->content(function (callable $get) {
                                    $qty = (float) ($get('qty_returned') ?? 0);
                                    $cump = (float) ($get('cump_at_return') ?? 0);
                                    return $qty > 0 && $cump > 0
                                        ? number_format($qty * $cump, 2) . ' DH'
                                        : '-';
                                })
                                ->columnSpan(2),
                        ])
                        ->columns(12)
                        ->defaultItems(0)
                        ->addActionLabel('➕ Ajouter un produit')
                        ->reorderable(false)
                        ->collapsible()
                        ->disabled(fn (?BonReintegration $record) => $record?->status !== 'draft')
                        ->itemLabel(fn (array $state): ?string => $state['product_id'] ? (Product::find($state['product_id'])?->name ?? 'Produit') : 'Nouvelle ligne')
                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                            $data['item_type'] = 'product';
                            $data['roll_id'] = null;
                            return $data;
                        })
                        ->mutateRelationshipDataBeforeFillUsing(function (array $data): array {
                            $data['item_type'] = 'product';
                            return $data;
                        }),
                ])
                ->columnSpanFull()
                ->collapsible(),

            Section::make('Notes')
                ->schema([
                    Textarea::make('notes')
                        ->label('Notes internes')
                        ->rows(4)
                        ->maxLength(2000),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }
}
