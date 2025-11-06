<?php

namespace App\Filament\Resources\RollAdjustments\Pages;

use App\Filament\Resources\RollAdjustments\RollAdjustmentResource;
use App\Models\Roll;
use App\Models\RollAdjustment;
use App\Services\RollAdjustmentService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateRollAdjustment extends CreateRecord
{
    protected static string $resource = RollAdjustmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $entries = $data['entries'] ?? [];

        if (! is_array($entries) || count($entries) === 0) {
            throw ValidationException::withMessages([
                'entries' => 'Ajoutez au moins une ligne d\'ajustement.',
            ]);
        }

        $service = app(RollAdjustmentService::class);
        $adjustments = collect();

        foreach ($entries as $index => $entry) {
            $operation = $entry['operation'] ?? null;

            if (! $operation) {
                throw ValidationException::withMessages([
                    "entries.{$index}.operation" => 'Sélectionnez l\'opération à appliquer.',
                ]);
            }

            $context = $this->buildContext($entry);

            switch ($operation) {
                case 'add':
                    $adjustments->push($service->addRoll($this->buildAddPayload($entry, $index)));
                    break;

                case 'remove':
                    $roll = $this->resolveRoll($entry, $index);
                    $this->assertRollStatus($roll, [Roll::STATUS_IN_STOCK], $index);

                    $newStatus = $entry['removal_status'] ?? Roll::STATUS_CONSUMED;
                    if (! in_array($newStatus, [Roll::STATUS_CONSUMED, Roll::STATUS_ARCHIVED], true)) {
                        throw ValidationException::withMessages([
                            "entries.{$index}.removal_status" => 'Statut de retrait invalide.',
                        ]);
                    }

                    $adjustments->push($service->adjustRollStatus($roll, $newStatus, RollAdjustment::TYPE_REMOVE, $context));
                    break;

                case 'damage':
                    $roll = $this->resolveRoll($entry, $index);
                    $this->assertRollStatus($roll, [Roll::STATUS_IN_STOCK], $index);
                    $adjustments->push($service->adjustRollStatus($roll, Roll::STATUS_DAMAGED, RollAdjustment::TYPE_DAMAGE, $context));
                    break;

                case 'restore':
                    $roll = $this->resolveRoll($entry, $index);
                    $this->assertRollStatus($roll, [Roll::STATUS_DAMAGED, Roll::STATUS_CONSUMED, Roll::STATUS_ARCHIVED], $index);
                    $adjustments->push($service->adjustRollStatus($roll, Roll::STATUS_IN_STOCK, RollAdjustment::TYPE_RESTORE, $context));
                    break;

                case 'weight_adjust':
                    $roll = $this->resolveRoll($entry, $index);
                    $this->assertRollStatus($roll, [Roll::STATUS_IN_STOCK], $index);

                    $newWeight = $entry['new_weight_kg'] ?? null;
                    if (is_null($newWeight)) {
                        throw ValidationException::withMessages([
                            "entries.{$index}.new_weight_kg" => 'Indiquez le nouveau poids de la bobine.',
                        ]);
                    }

                    $adjustments->push($service->adjustRollWeight($roll, (float) $newWeight, $context));
                    break;

                default:
                    throw ValidationException::withMessages([
                        "entries.{$index}.operation" => 'Opération non prise en charge.',
                    ]);
            }
        }

        if ($adjustments->isEmpty()) {
            throw ValidationException::withMessages([
                'entries' => 'Aucun ajustement n\'a été créé.',
            ]);
        }

        return $adjustments->first();
    }

    protected function buildContext(array $entry): array
    {
        return [
            'reason' => $entry['reason'] ?? null,
            'notes' => $entry['notes'] ?? null,
            'adjusted_by' => auth()->id(),
            'approved_by' => $entry['approved_by'] ?? null,
            'approved_at' => $entry['approved_at'] ?? null,
        ];
    }

    protected function buildAddPayload(array $entry, int $index): array
    {
        $required = ['product_id', 'warehouse_id', 'ean_13', 'new_weight_kg'];

        foreach ($required as $field) {
            if (blank($entry[$field] ?? null)) {
                throw ValidationException::withMessages([
                    "entries.{$index}.{$field}" => 'Champ requis pour l\'ajout d\'une bobine.',
                ]);
            }
        }

        return [
            'product_id' => $entry['product_id'],
            'warehouse_id' => $entry['warehouse_id'],
            'ean_13' => $entry['ean_13'],
            'batch_number' => $entry['batch_number'] ?? null,
            'received_date' => $entry['received_date'] ?? null,
            'weight_kg' => (float) $entry['new_weight_kg'],
            'cump_value' => $entry['cump_value'] ?? null,
            'reason' => $entry['reason'] ?? null,
            'notes' => $entry['notes'] ?? null,
            'adjusted_by' => auth()->id(),
        ];
    }

    protected function resolveRoll(array $entry, int $index): Roll
    {
        $rollId = $entry['roll_id'] ?? null;

        if (! $rollId) {
            throw ValidationException::withMessages([
                "entries.{$index}.roll_id" => 'Sélectionnez une bobine.',
            ]);
        }

        $roll = Roll::find($rollId);

        if (! $roll) {
            throw ValidationException::withMessages([
                "entries.{$index}.roll_id" => 'Bobine introuvable ou déjà mise à jour.',
            ]);
        }

        return $roll;
    }

    protected function assertRollStatus(Roll $roll, array $allowedStatuses, int $index): void
    {
        if (! in_array($roll->status, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                "entries.{$index}.roll_id" => 'Le statut actuel de la bobine ne permet pas cette opération.',
            ]);
        }
    }
}
