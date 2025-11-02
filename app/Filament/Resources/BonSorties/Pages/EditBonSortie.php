<?php

namespace App\Filament\Resources\BonSorties\Pages;

use App\Filament\Resources\BonSorties\BonSortieResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class EditBonSortie extends EditRecord
{
    protected static string $resource = BonSortieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('issue')
                ->label('Émettre')
                ->icon('heroicon-o-arrow-up-circle')
                ->color('warning')
                ->visible(fn () => $this->record->status === 'draft')
                ->requiresConfirmation()
                ->modalHeading('Émettre le bon de sortie')
                ->modalDescription('Cette action va déduire le stock. Continuer ?')
                ->action(function () {
                    $this->validateStockAvailability();
                    $this->processStockExit();
                    
                    $this->record->update([
                        'status' => 'issued',
                        'issued_date' => now()
                    ]);
                    
                    Notification::make()
                        ->title('Bon émis avec succès')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('confirm')
                ->label('Confirmer')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === 'issued')
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'confirmed']);
                    
                    Notification::make()
                        ->title('Bon confirmé')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('archive')
                ->label('Archiver')
                ->icon('heroicon-o-archive-box')
                ->color('gray')
                ->visible(fn () => in_array($this->record->status, ['confirmed']))
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'archived']);
                    
                    Notification::make()
                        ->title('Bon archivé')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('reopen')
                ->label('Réouvrir')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(fn () => $this->record->status === 'archived')
                ->requiresConfirmation()
                ->modalHeading('Réouvrir le bon')
                ->modalDescription('Le bon reviendra au statut "Confirmé"')
                ->action(function () {
                    $this->record->update(['status' => 'confirmed']);
                    
                    Notification::make()
                        ->title('Bon réouvert')
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make()
                ->visible(fn () => $this->record->status === 'draft'),
        ];
    }

    protected function validateStockAvailability(): void
    {
        foreach ($this->record->bonSortieItems as $item) {
            $stockQty = \App\Models\StockQuantity::where('product_id', $item->product_id)
                ->where('warehouse_id', $this->record->warehouse_id)
                ->first();
            
            if (!$stockQty || $stockQty->available_qty < $item->qty_issued) {
                $available = $stockQty ? $stockQty->available_qty : 0;
                
                Notification::make()
                    ->title('Stock insuffisant')
                    ->danger()
                    ->body("Produit '{$item->product->name}': Disponible = {$available}, Demandé = {$item->qty_issued}")
                    ->send();
                
                $this->halt();
            }
        }
    }

    protected function processStockExit(): void
    {
        DB::transaction(function () {
            foreach ($this->record->bonSortieItems as $item) {
                $stockQty = \App\Models\StockQuantity::lockForUpdate()
                    ->where('product_id', $item->product_id)
                    ->where('warehouse_id', $this->record->warehouse_id)
                    ->first();
                
                if (!$stockQty) {
                    throw new \Exception("StockQuantity not found for product {$item->product_id}");
                }
                
                // Deduct stock (total_qty only, CUMP unchanged)
                $stockQty->decrement('total_qty', $item->qty_issued);
                
                // Create stock movement
                \App\Models\StockMovement::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $this->record->warehouse_id,
                    'type' => 'ISSUE',
                    'reference_type' => 'App\\Models\\BonSortie',
                    'reference_id' => $this->record->id,
                    'quantity_change' => -$item->qty_issued,
                    'unit_cost' => $item->cump_at_issue,
                    'cump_before' => $stockQty->cump,
                    'cump_after' => $stockQty->cump, // CUMP unchanged for exits
                    'movement_date' => now(),
                ]);
            }
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
