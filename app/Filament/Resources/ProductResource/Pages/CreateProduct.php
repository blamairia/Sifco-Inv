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
        // Preserve primary_category_id in the page data so it can be used in syncPrimaryCategory()
        if (isset($data['primary_category_id'])) {
            $this->data['primary_category_id'] = (int) $data['primary_category_id'];
        } else {
            $this->data['primary_category_id'] = null;
        }

        // Ensure the primary category is included in categories list when submitting.
        if ($this->data['primary_category_id']) {
            $categories = $data['categories'] ?? [];
            $categories = array_map('intval', $categories);
            if (! in_array($this->data['primary_category_id'], $categories, true)) {
                $categories[] = $this->data['primary_category_id'];
            }
            $data['categories'] = array_values(array_unique($categories));
        }

        // The primary_category_id is a virtual field used only in the form.
        // We unset it here to prevent errors when creating the product model,
        // as it does not exist in the 'products' table.
        unset($data['primary_category_id']);

        return $data;
    }

    public function afterCreate(): void
    {
        $this->syncPrimaryCategory();
    }

    private function syncPrimaryCategory(): void
    {
        $product = $this->getRecord();
        $categories = array_map('intval', $this->data['categories'] ?? []);
        $primaryCategoryId = isset($this->data['primary_category_id'])
            ? (int) $this->data['primary_category_id']
            : null;

        $product->categories()->sync($categories);

        foreach ($categories as $categoryId) {
            $product->categories()->updateExistingPivot($categoryId, [
                'is_primary' => $primaryCategoryId !== null && $categoryId === $primaryCategoryId,
            ]);
        }
    }
}
