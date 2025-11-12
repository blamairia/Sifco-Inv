<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // The primary_category_id is a virtual field used only in the form.
        // We unset it here to prevent errors when creating the product model,
        // as it does not exist in the 'products' table.
        unset($data['primary_category_id']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncPrimaryCategory();
    }

    private function syncPrimaryCategory(): void
    {
        $product = $this->getRecord();
        $primaryCategoryId = $this->data['primary_category_id'] ?? null;

        if ($primaryCategoryId) {
            // Sync all categories first
            $product->categories()->sync($this->data['categories']);

            // Detach all, then re-attach the primary one with the pivot data.
            // This ensures only one primary category is set.
            $product->categories()->updateExistingPivot($primaryCategoryId, ['is_primary' => true]);

            // Ensure other categories are not primary
            $otherCategoryIds = array_diff($this->data['categories'], [$primaryCategoryId]);
            if (!empty($otherCategoryIds)) {
                $product->categories()->updateExistingPivot($otherCategoryIds, ['is_primary' => false]);
            }
        }
    }
}
