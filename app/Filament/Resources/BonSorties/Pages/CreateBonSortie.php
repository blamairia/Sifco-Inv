<?php

namespace App\Filament\Resources\BonSorties\Pages;

use App\Filament\Resources\BonSorties\BonSortieResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateBonSortie extends CreateRecord
{
    protected static string $resource = BonSortieResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'draft';

        // Ensure destination is set if destinationable is provided but front-end did not set destination
        if (empty($data['destination']) && !empty($data['destinationable_type']) && !empty($data['destinationable_id'])) {
            $class = $data['destinationable_type'];
            try {
                $related = $class::find($data['destinationable_id']);
                if ($related) {
                    $data['destination'] = $related->name ?? ($related->title ?? (string) $related);
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return $data;
    }
}
