<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
    protected ?int $primaryCategoryId = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->primaryCategoryId = isset($data['primary_category_id'])
            ? (int) $data['primary_category_id']
            : null;

        unset($data['primary_category_id']);

        // Ensure the primary category is included in the categories list when submitting.
        if ($this->primaryCategoryId) {
            $categories = $data['categories'] ?? [];
            $categories = array_map('intval', $categories);
            if (! in_array($this->primaryCategoryId, $categories, true)) {
                $categories[] = $this->primaryCategoryId;
            }
            $data['categories'] = array_values(array_unique($categories));
        }

        // Generate a product code if not supplied in the form
            if (empty($data['code'])) {
                $name = $data['name'] ?? 'product';
                // Prefer the 'type' family if present (papier_roll, consommable, fini), otherwise use the 'form_type'.
                $type = $data['type'] ?? ($data['form_type'] ?? null);
                $formType = $data['form_type'] ?? null;
                $primaryCategoryName = null;
                if ($this->primaryCategoryId) {
                    $primaryCategoryName = \App\Models\Category::find($this->primaryCategoryId)?->name;
                }

                $data['code'] = \App\Models\Product::generateCode(
                    $name,
                    $type,
                    $formType,
                    isset($data['grammage']) ? (int) $data['grammage'] : null,
                    $data['type_papier'] ?? null,
                    $data['flute'] ?? null,
                    isset($data['laize']) ? (int) $data['laize'] : null,
                    $primaryCategoryName,
                );
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Parent CreateRecord does not define afterCreate() in this version of Filament.
        // Avoid calling parent::afterCreate() to prevent PHP falling through to Livewire::__call.
        $this->syncPrimaryCategory();
    }

    protected function syncPrimaryCategory(): void
    {
        $product = $this->record;

        if (! $product) {
            return;
        }

        $categoryIds = $product->categories()->pluck('categories.id')->all();

        foreach ($categoryIds as $categoryId) {
            $product->categories()->updateExistingPivot($categoryId, [
                'is_primary' => $this->primaryCategoryId !== null && $categoryId === $this->primaryCategoryId,
            ]);
        }
    }
}
