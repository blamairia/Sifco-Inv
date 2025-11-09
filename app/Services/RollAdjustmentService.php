<?php

namespace App\Services;

use App\Models\Roll;
use App\Models\RollAdjustment;
use App\Models\StockMovement;
use App\Models\StockQuantity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RollAdjustmentService
{
    public function addRoll(array $data): RollAdjustment
    {
        return DB::transaction(function () use ($data) {
            $weight = isset($data['weight_kg']) ? (float) $data['weight_kg'] : 0.0;
            $length = isset($data['length_m']) ? (float) $data['length_m'] : 0.0;

            if ($weight <= 0) {
                throw new InvalidArgumentException('Le poids de la bobine doit être supérieur à zéro.');
            }

            if ($length <= 0) {
                throw new InvalidArgumentException('La longueur de la bobine doit être supérieure à zéro.');
            }

            $roll = Roll::create([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'ean_13' => $data['ean_13'],
                'batch_number' => $data['batch_number'] ?? null,
                'received_date' => $data['received_date'] ?? now(),
                'status' => Roll::STATUS_IN_STOCK,
                'weight_kg' => $weight,
                'length_m' => $length,
                'cump_value' => $data['cump_value'] ?? null,
                'is_manual_entry' => true,
                'notes' => $data['notes'] ?? null,
            ]);

            $reason = $data['reason'] ?? 'Ajustement manuel';

            $adjustment = $this->logAdjustment(
                $roll,
                RollAdjustment::TYPE_ADD,
                null,
                Roll::STATUS_IN_STOCK,
                $reason,
                $data,
                null,
                $roll->weight,
                $roll->weight,
                0.0,
                $length,
                $length,
            );

            $movement = $this->createMovement(
                $roll,
                1,
                $reason,
                $roll->weight,
                null,
                $roll->weight,
                $length,
                0.0,
                $length,
            );

            $this->updateStockQuantity(
                $roll->product_id,
                $roll->warehouse_id,
                1,
                $roll->cump,
                $roll->weight,
                $length,
                $movement->id,
            );

            return $adjustment;
        });
    }

    public function adjustRollStatus(Roll $roll, string $newStatus, string $adjustmentType, array $data): RollAdjustment
    {
        return DB::transaction(function () use ($roll, $newStatus, $adjustmentType, $data) {
            $previousStatus = $roll->status;
            $previousWeight = $roll->weight;
            $previousLength = (float) $roll->length;

            $newLength = $previousLength;

            if (array_key_exists('new_length_m', $data) && $data['new_length_m'] !== null) {
                $newLength = (float) $data['new_length_m'];
            }

            if (in_array($newStatus, [Roll::STATUS_CONSUMED, Roll::STATUS_ARCHIVED], true)) {
                $newLength = 0.0;
            } elseif ($newStatus === Roll::STATUS_IN_STOCK && in_array($previousStatus, [Roll::STATUS_CONSUMED, Roll::STATUS_ARCHIVED, Roll::STATUS_DAMAGED], true)) {
                if ($newLength <= 0) {
                    throw new InvalidArgumentException('La longueur doit être fournie pour remettre la bobine en stock.');
                }
            }

            $roll->update([
                'status' => $newStatus,
                'length_m' => $newLength,
                'notes' => $this->mergeNotes($roll->notes, $data['notes'] ?? null),
            ]);

            $reason = $data['reason'] ?? 'Ajustement manuel';

            $weightDelta = 0.0;
            if (in_array($newStatus, [Roll::STATUS_CONSUMED, Roll::STATUS_ARCHIVED], true)) {
                $weightDelta = -$previousWeight;
            } elseif ($newStatus === Roll::STATUS_IN_STOCK && in_array($previousStatus, [Roll::STATUS_CONSUMED, Roll::STATUS_ARCHIVED, Roll::STATUS_DAMAGED], true)) {
                $weightDelta = $roll->weight;
            }

            $lengthDelta = round($newLength - $previousLength, 3);

            $adjustment = $this->logAdjustment(
                $roll,
                $adjustmentType,
                $previousStatus,
                $newStatus,
                $reason,
                $data,
                $previousWeight,
                $roll->weight,
                $weightDelta,
                $previousLength,
                $newLength,
                $lengthDelta,
            );

            $qtyDelta = match ($newStatus) {
                Roll::STATUS_CONSUMED, Roll::STATUS_ARCHIVED => -1,
                Roll::STATUS_IN_STOCK => $previousStatus === Roll::STATUS_IN_STOCK ? 0 : 1,
                default => 0,
            };

            if ($qtyDelta !== 0 || $weightDelta !== 0.0 || $lengthDelta !== 0.0) {
                $movement = $this->createMovement($roll, $qtyDelta, $reason, $weightDelta, $previousWeight, $roll->weight, $lengthDelta, $previousLength, $newLength);
                $this->updateStockQuantity($roll->product_id, $roll->warehouse_id, $qtyDelta, $roll->cump, $weightDelta, $lengthDelta, $movement->id);
            }

            return $adjustment;
        });
    }

    public function adjustRollWeight(Roll $roll, float $newWeight, array $data): RollAdjustment
    {
        return DB::transaction(function () use ($roll, $newWeight, $data) {
            $previousWeight = $roll->weight;
            $previousLength = (float) $roll->length;

            if ($newWeight <= 0) {
                throw new InvalidArgumentException('Le nouveau poids doit être supérieur à zéro.');
            }

            $weightDelta = round($newWeight - $previousWeight, 3);

            if ($weightDelta === 0.0) {
                throw new InvalidArgumentException('Le poids renseigné est identique au poids actuel.');
            }

            $roll->update([
                'weight_kg' => $newWeight,
                'notes' => $this->mergeNotes($roll->notes, $data['notes'] ?? null),
            ]);

            $reason = $data['reason'] ?? 'Ajustement manuel du poids';

            $adjustment = $this->logAdjustment(
                $roll,
                RollAdjustment::TYPE_WEIGHT_ADJUST,
                $roll->status,
                $roll->status,
                $reason,
                $data,
                $previousWeight,
                $roll->weight,
                $weightDelta,
                $previousLength,
                (float) $roll->length,
                0.0,
            );

            $movement = $this->createMovement($roll, 0, $reason, $weightDelta, $previousWeight, $roll->weight, 0.0, $previousLength, (float) $roll->length);

            $this->updateStockQuantity($roll->product_id, $roll->warehouse_id, 0, $roll->cump, $weightDelta, 0.0, $movement->id);

            return $adjustment;
        });
    }

    protected function logAdjustment(
        Roll $roll,
        string $type,
        ?string $previousStatus,
        string $newStatus,
        string $reason,
        array $context,
        ?float $previousWeight,
        ?float $newWeight,
        ?float $weightDelta = null,
        ?float $previousLength = null,
        ?float $newLength = null,
        ?float $lengthDelta = null,
    ): RollAdjustment
    {
        return RollAdjustment::create([
            'adjustment_number' => $this->generateAdjustmentNumber(),
            'roll_id' => $roll->id,
            'product_id' => $roll->product_id,
            'warehouse_id' => $roll->warehouse_id,
            'adjustment_type' => $type,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'previous_weight_kg' => $previousWeight,
            'new_weight_kg' => $newWeight,
            'weight_delta_kg' => $weightDelta ?? (($newWeight ?? 0) - ($previousWeight ?? 0)),
            'previous_length_m' => $previousLength,
            'new_length_m' => $newLength,
            'length_delta_m' => $lengthDelta ?? (($newLength ?? 0) - ($previousLength ?? 0)),
            'reason' => $reason,
            'adjusted_by' => $context['adjusted_by'] ?? Auth::id(),
            'approved_by' => $context['approved_by'] ?? null,
            'approved_at' => $context['approved_at'] ?? null,
            'notes' => $context['notes'] ?? null,
        ]);
    }

    protected function createMovement(
        Roll $roll,
        float $qtyDelta,
        ?string $reason = null,
        ?float $weightDelta = null,
        ?float $previousWeight = null,
        ?float $newWeight = null,
        ?float $lengthDelta = null,
        ?float $previousLength = null,
        ?float $newLength = null,
    ): StockMovement
    {
        $warehouseTo = null;
        $warehouseFrom = null;

        if ($qtyDelta > 0 || ($qtyDelta === 0 && ($weightDelta ?? 0) > 0)) {
            $warehouseTo = $roll->warehouse_id;
        } elseif ($qtyDelta < 0 || ($qtyDelta === 0 && ($weightDelta ?? 0) < 0)) {
            $warehouseFrom = $roll->warehouse_id;
        }

        return StockMovement::create([
            'movement_number' => $this->generateMovementNumber(),
            'movement_type' => 'ADJUSTMENT',
            'product_id' => $roll->product_id,
            'warehouse_to_id' => $warehouseTo,
            'warehouse_from_id' => $warehouseFrom,
            'qty_moved' => abs($qtyDelta),
            'cump_at_movement' => $roll->cump,
            'status' => 'confirmed',
            'reference_number' => $roll->ean_13,
            'user_id' => Auth::id(),
            'performed_at' => now(),
            'notes' => $reason,
            'roll_weight_before_kg' => $previousWeight,
            'roll_weight_after_kg' => $newWeight,
            'roll_weight_delta_kg' => $weightDelta ?? (($newWeight ?? 0) - ($previousWeight ?? 0)),
            'roll_length_before_m' => $previousLength,
            'roll_length_after_m' => $newLength,
            'roll_length_delta_m' => $lengthDelta ?? (($newLength ?? 0) - ($previousLength ?? 0)),
        ]);
    }

    protected function updateStockQuantity(
        int $productId,
        int $warehouseId,
        float $qtyChange,
        float $cump,
        ?float $weightChange = null,
        ?float $lengthChange = null,
        ?int $movementId = null
    ): void
    {
        $stockQuantity = StockQuantity::firstOrCreate(
            [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'total_qty' => 0,
                'total_weight_kg' => 0,
                'total_length_m' => 0,
                'cump_snapshot' => $cump,
            ],
        );

        if ($qtyChange !== 0.0) {
            $stockQuantity->increment('total_qty', $qtyChange);
        }

        if (! is_null($weightChange) && $weightChange !== 0.0) {
            $stockQuantity->increment('total_weight_kg', $weightChange);
        }

        if (! is_null($lengthChange) && $lengthChange !== 0.0) {
            $stockQuantity->increment('total_length_m', $lengthChange);
        }

        $stockQuantity->update([
            'cump_snapshot' => $cump,
            'last_movement_id' => $movementId ?? $stockQuantity->last_movement_id,
        ]);
    }

    protected function generateAdjustmentNumber(): string
    {
        $prefix = 'ADJ-ROLL-' . now()->format('Ymd');
        $sequence = RollAdjustment::whereDate('created_at', now()->toDateString())->count() + 1;

        return sprintf('%s-%04d', $prefix, $sequence);
    }

    protected function generateMovementNumber(): string
    {
        $prefix = 'MOV-ROLL-' . now()->format('Ymd');
        $sequence = StockMovement::whereDate('created_at', now()->toDateString())->count() + 1;

        return sprintf('%s-%04d', $prefix, $sequence);
    }

    protected function mergeNotes(?string $existing, ?string $additional): ?string
    {
        if (blank($additional)) {
            return $existing;
        }

        if (blank($existing)) {
            return $additional;
        }

        return trim($existing . PHP_EOL . $additional);
    }
}
