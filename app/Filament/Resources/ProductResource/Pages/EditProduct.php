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
