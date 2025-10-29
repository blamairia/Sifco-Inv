<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'papier_roll' => 'info',
                        'consommable' => 'warning',
                        'fini' => 'success',
                    }),
                TextColumn::make('gsm')
                    ->label('GSM')
                    ->numeric(),
                TextColumn::make('min_stock')
                    ->label('Stock Min')
                    ->numeric(),
                TextColumn::make('avg_cost')
                    ->label('Coût Moyen (DZD)')
                    ->money('DZD'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
