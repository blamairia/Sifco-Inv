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
        // Preserve the primary category selection in the livewire page data so syncPrimaryCategory can find it
        if (isset($data['primary_category_id'])) {
            $this->data['primary_category_id'] = (int) $data['primary_category_id'];
        } else {
            $this->data['primary_category_id'] = null;
        }

        // If the primary category is set but not present in the categories list, add it
        if ($this->data['primary_category_id']) {
            $categories = $data['categories'] ?? [];
            $categories = array_map('intval', $categories);
            if (! in_array($this->data['primary_category_id'], $categories, true)) {
                $categories[] = $this->data['primary_category_id'];
            }
            $data['categories'] = array_values(array_unique($categories));
        }

        unset($data['primary_category_id']);
        return $data;
    }

    public function afterSave(): void
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
