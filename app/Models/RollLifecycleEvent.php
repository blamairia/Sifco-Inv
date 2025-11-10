<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RollLifecycleEvent extends Model
{
    const TYPE_RECEPTION = 'RECEPTION';
    const TYPE_TRANSFER = 'TRANSFER';
    const TYPE_TRANSFER_COMPLETED = 'TRANSFER_COMPLETED';
    const TYPE_SORTIE = 'SORTIE';
    const TYPE_REINTEGRATION = 'REINTEGRATION';
    const TYPE_ADJUSTMENT = 'ADJUSTMENT';

    protected $table = 'roll_lifecycle_events';

    protected $fillable = [
        'roll_id',
        'stock_movement_id',
        'event_type',
        'reference_number',
        'weight_before_kg',
        'weight_after_kg',
        'weight_delta_kg',
        'length_before_m',
        'length_after_m',
        'length_delta_m',
        'has_waste',
        'waste_weight_kg',
        'waste_length_m',
        'waste_reason',
        'warehouse_from_id',
        'warehouse_to_id',
        'triggered_by_id',
        'metadata',
        'notes',
    ];

    protected $casts = [
        'weight_before_kg' => 'decimal:3',
        'weight_after_kg' => 'decimal:3',
        'weight_delta_kg' => 'decimal:3',
        'length_before_m' => 'decimal:3',
        'length_after_m' => 'decimal:3',
        'length_delta_m' => 'decimal:3',
        'waste_weight_kg' => 'decimal:3',
        'waste_length_m' => 'decimal:3',
        'has_waste' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships

    public function roll(): BelongsTo
    {
        return $this->belongsTo(Roll::class);
    }

    public function stockMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class);
    }

    public function warehouseFrom(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_from_id');
    }

    public function warehouseTo(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_to_id');
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_id');
    }

    // Scopes

    public function scopeWithWaste($query)
    {
        return $query->where('has_waste', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeByReferenceNumber($query, string $referenceNumber)
    {
        return $query->where('reference_number', $referenceNumber);
    }

    // Helpers

    private static function logEvent(
        Roll $roll,
        string $eventType,
        ?StockMovement $movement = null,
        ?string $referenceNumber = null,
        array $data = []
    ): self {
        // Calculate deltas
        $weightBefore = $data['weight_before_kg'] ?? ($roll->weight_kg ?? 0);
        $weightAfter = $data['weight_after_kg'] ?? $weightBefore;
        $lengthBefore = $data['length_before_m'] ?? ($roll->length_m ?? 0);
        $lengthAfter = $data['length_after_m'] ?? $lengthBefore;

        // Detect waste based on deltas
        $hasWaste = false;
        $wasteWeight = 0;
        $wasteLength = 0;

        if ($weightAfter < $weightBefore || $lengthAfter < $lengthBefore) {
            $hasWaste = true;
            $wasteWeight = max(0, $weightBefore - $weightAfter);
            $wasteLength = max(0, $lengthBefore - $lengthAfter);
        }

        return self::create([
            'roll_id' => $roll->id,
            'stock_movement_id' => $movement?->id,
            'event_type' => $eventType,
            'reference_number' => $referenceNumber,
            'weight_before_kg' => $weightBefore,
            'weight_after_kg' => $weightAfter,
            'weight_delta_kg' => $weightAfter - $weightBefore,
            'length_before_m' => $lengthBefore,
            'length_after_m' => $lengthAfter,
            'length_delta_m' => $lengthAfter - $lengthBefore,
            'has_waste' => $hasWaste,
            'waste_weight_kg' => $wasteWeight,
            'waste_length_m' => $wasteLength,
            'waste_reason' => $data['waste_reason'] ?? null,
            'warehouse_from_id' => $data['warehouse_from_id'] ?? null,
            'warehouse_to_id' => $data['warehouse_to_id'] ?? null,
            'triggered_by_id' => $data['triggered_by_id'] ?? \Illuminate\Support\Facades\Auth::id(),
            'metadata' => $data['metadata'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public static function logReception(Roll $roll, StockMovement $movement): self
    {
        return self::logEvent(
            roll: $roll,
            eventType: self::TYPE_RECEPTION,
            movement: $movement,
            referenceNumber: $movement->reference_number,
            data: [
                'weight_before_kg' => 0,
                'weight_after_kg' => $roll->weight_kg,
                'length_before_m' => 0,
                'length_after_m' => $roll->length_m,
                'warehouse_to_id' => $roll->warehouse_id,
                'notes' => "Réception bobine {$roll->ean_13}",
            ]
        );
    }

    public static function logTransfer(
        Roll $roll,
        StockMovement $movement,
        int $sourceWarehouseId,
        int $destWarehouseId
    ): self {
        return self::logEvent(
            roll: $roll,
            eventType: self::TYPE_TRANSFER,
            movement: $movement,
            referenceNumber: $movement->reference_number,
            data: [
                'weight_before_kg' => $roll->weight_kg ?? $roll->weight ?? 0,
                'weight_after_kg' => $roll->weight_kg ?? $roll->weight ?? 0,
                'length_before_m' => $roll->length_m ?? $roll->length ?? 0,
                'length_after_m' => $roll->length_m ?? $roll->length ?? 0,
                'warehouse_from_id' => $sourceWarehouseId,
                'warehouse_to_id' => $destWarehouseId,
                'notes' => "Transfert bobine {$roll->ean_13}",
            ]
        );
    }

    public static function logSortie(Roll $roll, StockMovement $movement): self
    {
        return self::logEvent(
            roll: $roll,
            eventType: self::TYPE_SORTIE,
            movement: $movement,
            referenceNumber: $movement->reference_number,
            data: [
                'weight_before_kg' => $movement->roll_weight_before_kg ?? 0,
                'weight_after_kg' => $movement->roll_weight_after_kg ?? 0,
                'length_before_m' => $movement->roll_length_before_m ?? 0,
                'length_after_m' => $movement->roll_length_after_m ?? 0,
                'warehouse_from_id' => $roll->warehouse_id,
                'notes' => "Sortie bobine {$roll->ean_13}",
            ]
        );
    }

    public static function logTransferCompleted(
        Roll $roll,
        StockMovement $movement,
        int $destWarehouseId
    ): self {
        return self::logEvent(
            roll: $roll,
            eventType: self::TYPE_TRANSFER_COMPLETED,
            movement: $movement,
            referenceNumber: $movement->reference_number,
            data: [
                'warehouse_to_id' => $destWarehouseId,
                'notes' => "Transfert terminé bobine {$roll->ean_13}",
            ]
        );
    }

    public static function logReintegration(
        Roll $roll,
        StockMovement $movement,
        float $previousWeight,
        float $returnedWeight,
        float $previousLength,
        float $returnedLength,
        int $warehouseId
    ): self {
        return self::logEvent(
            roll: $roll,
            eventType: self::TYPE_REINTEGRATION,
            movement: $movement,
            referenceNumber: $movement->reference_number,
            data: [
                'weight_before_kg' => $previousWeight,
                'weight_after_kg' => $returnedWeight,
                'length_before_m' => $previousLength,
                'length_after_m' => $returnedLength,
                'warehouse_to_id' => $warehouseId,
                'notes' => "Réintégration bobine {$roll->ean_13}",
            ]
        );
    }
}