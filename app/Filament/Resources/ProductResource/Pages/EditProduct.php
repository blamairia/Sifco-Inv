<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['primary_category_id']);
        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncPrimaryCategory();
    }

    protected function fillForm(): void
    {
        parent::fillForm();
        $product = $this->getRecord();
        $primaryCategory = $product->category; // Uses the accessor
        if ($primaryCategory) {
            $this->form->fill(['primary_category_id' => $primaryCategory->id]);
        }
    }

    private function syncPrimaryCategory(): void
    {
        $product = $this->getRecord();
        $primaryCategoryId = $this->data['primary_category_id'] ?? null;

        if ($primaryCategoryId) {
            // Sync all categories first
            $product->categories()->sync($this->data['categories']);

            // Set the new primary category
            $product->categories()->updateExistingPivot($primaryCategoryId, ['is_primary' => true]);

            // Ensure other associated categories are not primary
            $otherCategoryIds = array_diff($this->data['categories'], [$primaryCategoryId]);
            if (!empty($otherCategoryIds)) {
                $product->categories()->updateExistingPivot($otherCategoryIds, ['is_primary' => false]);
            }
        } else {
            // If no primary category is selected, sync categories and ensure none are primary
            $product->categories()->sync($this->data['categories']);
            $product->categories()->updateExistingPivot($this->data['categories'], ['is_primary' => false]);
        }
    }
}
