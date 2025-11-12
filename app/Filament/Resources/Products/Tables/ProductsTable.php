<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['categories', 'unit']))
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product_type')
                    ->label('Type Logique')
                    ->formatStateUsing(fn (?string $state) => Product::labelForProductType($state))
                    ->badge()
                    ->color('success')
                    ->sortable(),
                TextColumn::make('form_type')
                    ->label('Forme Physique')
                    ->formatStateUsing(fn (?string $state) => Product::labelForFormType($state))
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('unit.name')
                    ->label('Unité')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('grammage')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('laize')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('flute')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('type_papier')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sheet_width_mm')
                    ->label('Largeur (mm)')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sheet_length_mm')
                    ->label('Longueur (mm)')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('min_stock')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('safety_stock')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('product_type')
                    ->label('Type Logique (Stade)')
                    ->options(Product::productTypeOptions()),
                SelectFilter::make('form_type')
                    ->label('Forme Physique')
                    ->options(Product::formTypeOptions()),
                SelectFilter::make('is_active')
                    ->label('Statut')
                    ->options([
                        1 => 'Actif',
                        0 => 'Inactif',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
