<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;
    protected ?int $primaryCategoryId = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->primaryCategoryId = isset($data['primary_category_id'])
            ? (int) $data['primary_category_id']
            : null;

        unset($data['primary_category_id']);

        // If the primary category is set but not present in the category list, add it to the categories array
        if ($this->primaryCategoryId) {
            $categories = $data['categories'] ?? [];
            $categories = array_map('intval', $categories);
            if (! in_array($this->primaryCategoryId, $categories, true)) {
                $categories[] = $this->primaryCategoryId;
            }
            $data['categories'] = array_values(array_unique($categories));
        }

        return $data;
    }

    public function afterSave(): void
    {
        // Parent EditRecord does not define afterSave() in this Filament version.
        // Avoid calling parent::afterSave() to prevent PHP falling through to Livewire::__call.
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
