<?php

namespace App\Filament\Resources\Rolls\Schemas;

use App\Models\Roll;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Produit (Bobines uniquement)')
                    ->relationship('product', 'name', fn ($query) => $query->where('is_roll', true))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Seuls les produits marqués comme bobines sont disponibles'),
                
                Select::make('warehouse_id')
                    ->label('Magasin')
                    ->relationship('warehouse', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                
                TextInput::make('ean_13')
                    ->label('Code EAN-13')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Le code EAN-13 est généré automatiquement lors de la réception du Bon d\'Entrée'),
                
                TextInput::make('batch_number')
                    ->label('Numéro de Lot')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Le numéro de lot provient du Bon d\'Entrée'),
                
                \Filament\Forms\Components\DatePicker::make('received_date')
                    ->label('Date de Réception')
                    ->required()
                    ->default(now()),
                
                Placeholder::make('status_display')
                    ->label('Statut')
                    ->content(fn (?Roll $record) => match ($record?->status) {
                        Roll::STATUS_IN_STOCK => 'En stock',
                        Roll::STATUS_RESERVED => 'Réservé',
                        Roll::STATUS_CONSUMED => 'Consommé',
                        Roll::STATUS_DAMAGED => 'Endommagé',
                        Roll::STATUS_ARCHIVED => 'Archivé',
                        default => 'En stock',
                    })
                    ->helperText('Les statuts ne se changent que via les ajustements et bons.'),
                
                \Filament\Forms\Components\Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->maxLength(65535),
            ]);
    }
}
