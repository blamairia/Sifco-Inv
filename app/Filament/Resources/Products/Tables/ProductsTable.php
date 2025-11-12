<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('categories'))
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Famille')
                    ->formatStateUsing(fn (?string $state) => Product::labelForType($state))
                    ->badge()
                    ->sortable(),
                TextColumn::make('product_type')
                    ->label('Type logique')
                    ->formatStateUsing(fn (?string $state) => Product::labelForProductType($state))
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sheet_width_mm')
                    ->label('Largeur (mm)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sheet_length_mm')
                    ->label('Longueur (mm)')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('grammage')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('laize')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('flute')
                    ->searchable(),
                TextColumn::make('type_papier')
                    ->searchable(),
                TextColumn::make('unit.name')
                    ->label('Unité')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                IconColumn::make('is_roll')
                    ->label('Bobine')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('min_stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('safety_stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
