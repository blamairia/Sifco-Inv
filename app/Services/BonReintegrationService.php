<?php

namespace App\Services;

use App\Models\BonReintegration;
use App\Models\BonReintegrationItem;
use App\Models\Roll;
use App\Models\RollLifecycleEvent;
use App\Models\StockMovement;
use App\Models\StockQuantity;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BonReintegrationService
{
    /**
     * Receive a return voucher and reintegrate items into stock.
     */
    public function receive(BonReintegration $bonReintegration): void
    {
        if ($bonReintegration->status !== 'draft') {
            throw new Exception("Seuls les bons en brouillon peuvent être réceptionnés. Statut actuel : {$bonReintegration->status}");
        }

        DB::transaction(function () use ($bonReintegration) {
            $items = $bonReintegration->bonReintegrationItems;

            if ($items->isEmpty()) {
                throw new Exception('Impossible de réceptionner un bon de réintégration sans lignes.');
            }

            foreach ($items as $item) {
                if ($item->item_type === 'roll') {
                    $this->reinstateRollItem($bonReintegration, $item);
                } else {
                    $this->reinstateProductItem($bonReintegration, $item);
                }
            }

            $bonReintegration->update([
                'status' => 'received',
                'return_date' => $bonReintegration->return_date ?? now(),
            ]);
        });
    }

    protected function reinstateRollItem(BonReintegration $bonReintegration, BonReintegrationItem $item): void
    {
        if (! $item->roll_id) {
            throw new Exception('Une ligne de bobine doit référencer la bobine d\'origine.');
        }

        $roll = Roll::findOrFail($item->roll_id);
        $previousWeight = (float) ($item->previous_weight_kg ?? 0);
        $returnedWeight = (float) ($item->returned_weight_kg ?? 0);
        $previousLength = (float) ($item->previous_length_m ?? $roll->length);
        $returnedLength = (float) ($item->returned_length_m ?? 0);

        if ($returnedWeight <= 0) {
            throw new Exception("Le poids réintégré pour la bobine {$roll->ean_13} doit être supérieur à zéro.");
        }

        if ($returnedLength <= 0) {
            throw new Exception("La longueur réintégrée pour la bobine {$roll->ean_13} doit être supérieure à zéro.");
        }

        $weightDelta = $returnedWeight - $previousWeight;
        $lengthDelta = $returnedLength - $previousLength;

        $roll->update([
            'status' => Roll::STATUS_IN_STOCK,
            'warehouse_id' => $bonReintegration->warehouse_id,
            'weight_kg' => $returnedWeight,
            'length_m' => $returnedLength,
            'notes' => $this->mergeNotes($roll->notes, "Réintégré via {$bonReintegration->bon_number}"),
        ]);

        $movement = StockMovement::create([
            'movement_number' => $this->generateMovementNumber(),
            'product_id' => $roll->product_id,
            'warehouse_to_id' => $bonReintegration->warehouse_id,
            'movement_type' => 'RETURN',
            'qty_moved' => 1,
            'cump_at_movement' => $item->cump_at_return ?? $roll->cump,
            'status' => 'confirmed',
            'reference_number' => $bonReintegration->bon_number,
            'user_id' => Auth::id() ?? 1,
            'performed_at' => now(),
            'notes' => "Réintégration bobine {$roll->ean_13}",
            'roll_weight_before_kg' => $previousWeight,
            'roll_weight_after_kg' => $returnedWeight,
            'roll_weight_delta_kg' => $weightDelta,
            'roll_length_before_m' => $previousLength,
            'roll_length_after_m' => $returnedLength,
            'roll_length_delta_m' => $lengthDelta,
        ]);

        // Log reintegration event
        RollLifecycleEvent::logReintegration(
            roll: $roll,
            movement: $movement,
            previousWeight: $previousWeight,
            returnedWeight: $returnedWeight,
            previousLength: $previousLength,
            returnedLength: $returnedLength,
            warehouseId: $bonReintegration->warehouse_id
        );

        $this->updateStockQuantity(
            $roll->product_id,
            $bonReintegration->warehouse_id,
            1,
            $item->cump_at_return ?? $roll->cump,
            $returnedWeight,
            $returnedLength,
            $movement->id,
        );

        $item->update([
            'weight_delta_kg' => $weightDelta,
            'length_delta_m' => $lengthDelta,
            'returned_length_m' => $returnedLength,
            'previous_length_m' => $previousLength,
            'value_returned' => $returnedWeight * ($item->cump_at_return ?? $roll->cump),
        ]);
    }

    protected function reinstateProductItem(BonReintegration $bonReintegration, BonReintegrationItem $item): void
    {
        $qty = (float) $item->qty_returned;

        if ($qty <= 0) {
            throw new Exception('Les quantités réintégrées doivent être positives.');
        }

        $cump = (float) ($item->cump_at_return ?? $bonReintegration->cump_at_return ?? 0);

        $movement = StockMovement::create([
            'movement_number' => $this->generateMovementNumber(),
            'product_id' => $item->product_id,
            'warehouse_to_id' => $bonReintegration->warehouse_id,
            'movement_type' => 'RETURN',
            'qty_moved' => $qty,
            'cump_at_movement' => $cump,
            'status' => 'confirmed',
            'reference_number' => $bonReintegration->bon_number,
            'user_id' => Auth::id() ?? 1,
            'performed_at' => now(),
            'notes' => 'Réintégration produit',
        ]);

        $this->updateStockQuantity(
            $item->product_id,
            $bonReintegration->warehouse_id,
            $qty,
            $cump,
            0,
            0,
            $movement->id,
        );

        $item->update([
            'value_returned' => $qty * $cump,
        ]);
    }

    protected function updateStockQuantity(
        int $productId,
        int $warehouseId,
        float $qtyChange,
        float $cump,
        float $weightChange = 0,
        float $lengthChange = 0,
        ?int $movementId = null
    ): void {
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

        $stockQuantity->total_qty = (float) $stockQuantity->total_qty + $qtyChange;

        if ($weightChange !== 0.0) {
            $stockQuantity->total_weight_kg = (float) ($stockQuantity->total_weight_kg ?? 0) + $weightChange;
        }

        if ($lengthChange !== 0.0) {
            $stockQuantity->total_length_m = (float) ($stockQuantity->total_length_m ?? 0) + $lengthChange;
        }

        $stockQuantity->cump_snapshot = $cump;
        if ($movementId) {
            $stockQuantity->last_movement_id = $movementId;
        }

        $stockQuantity->save();
    }

    protected function generateMovementNumber(): string
    {
        $prefix = 'RET-ROLL-' . now()->format('Ymd');
        $sequence = StockMovement::whereDate('created_at', now()->toDateString())->count() + 1;

        return sprintf('%s-%04d', $prefix, $sequence);
    }

    protected function mergeNotes(?string $existing, string $additional): string
    {
        if (blank($existing)) {
            return $additional;
        }

        return trim($existing . PHP_EOL . $additional);
    }
}
