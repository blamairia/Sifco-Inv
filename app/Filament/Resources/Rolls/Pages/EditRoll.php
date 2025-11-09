<?php

namespace App\Filament\Resources\Rolls\Pages;

use App\Filament\Resources\Rolls\RollResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRoll extends EditRecord
{
    protected static string $resource = RollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (array_key_exists('status', $data) && $data['status'] !== $this->record->status) {
            Notification::make()
                ->title('Transition interdite')
                ->danger()
                ->body('Utilisez les ajustements pour modifier le statut de la bobine.')
                ->send();

            $this->halt();
        }

        unset($data['status']);

        return $data;
    }
}
