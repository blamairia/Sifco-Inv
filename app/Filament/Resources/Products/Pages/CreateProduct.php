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

        return $data;
    }

    public function afterCreate(): void
    {
        parent::afterCreate();

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
