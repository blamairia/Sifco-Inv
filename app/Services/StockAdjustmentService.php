<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\StockQuantity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use InvalidArgumentException;

class StockAdjustmentService
{
    /**
     * Enregistre un ajustement de stock pour les produits hors bobines.
     *
     * @param  array  $data
     * @return StockAdjustment
     *
     * @throws Exception
     */
    public function adjust(array $data): StockAdjustment
    {
        try {
            return DB::transaction(function () use ($data) {
                $product = Product::query()->findOrFail($data['product_id'] ?? null);

                if ($product->isRoll()) {
                    throw new InvalidArgumentException('Utilisez l'interface des ajustements de bobines pour ce produit.');
                }

                $warehouseId = $data['warehouse_id'] ?? null;
                if (! $warehouseId) {
                    throw new InvalidArgumentException('Sélectionnez un entrepôt valide.');
                }

                $stockQty = StockQuantity::firstOrCreate(
                    [
                        'product_id' => $product->id,
                        'warehouse_id' => $warehouseId,
                    ],
                    [
                        'total_qty' => 0,
                        'total_weight_kg' => 0,
                        'total_length_m' => 0,
                        'reserved_qty' => 0,
                        'cump_snapshot' => 0,
                    ],
                );

                $qtyBefore = (float) $stockQty->total_qty;
                $weightBefore = (float) ($stockQty->total_weight_kg ?? 0.0);

                $qtyAfter = array_key_exists('new_quantity', $data)
                    ? (float) $data['new_quantity']
                    : (float) ($data['qty_after'] ?? $qtyBefore);

                $weightAfter = array_key_exists('new_weight_kg', $data)
                    ? ($data['new_weight_kg'] === null ? $weightBefore : (float) $data['new_weight_kg'])
                    : (float) ($data['weight_after_kg'] ?? $weightBefore);

                if ($qtyAfter < 0) {
                    throw new InvalidArgumentException('La quantité ne peut pas être négative.');
                }

                if ($weightAfter < 0) {
                    throw new InvalidArgumentException('Le poids total ne peut pas être négatif.');
                }

                $qtyChange = round($qtyAfter - $qtyBefore, 3);
                $weightChange = round($weightAfter - $weightBefore, 3);

                if ($this->isNoop($qtyChange, $weightChange)) {
                    throw new InvalidArgumentException('Aucun changement détecté entre le stock actuel et la saisie.');
                }

                $adjustmentType = $qtyChange > 0 ? 'INCREASE' : ($qtyChange < 0 ? 'DECREASE' : 'CORRECTION');

                $reason = trim((string) ($data['reason'] ?? 'Ajustement manuel'));
                if ($reason === '') {
                    throw new InvalidArgumentException('La raison de l’ajustement est obligatoire.');
                }

                $adjustmentNumber = $data['adjustment_number'] ?? StockAdjustment::generateAdjustmentNumber();

                $adjustment = StockAdjustment::create([
                    'adjustment_number' => $adjustmentNumber,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId,
                    'qty_before' => $qtyBefore,
                    'qty_after' => $qtyAfter,
                    'qty_change' => $qtyChange,
                    'weight_before_kg' => $weightBefore,
                    'weight_after_kg' => $weightAfter,
                    'weight_change_kg' => $weightChange,
                    'adjustment_type' => $adjustmentType,
                    'reason' => $reason,
                    'adjusted_by' => Auth::id() ?? 1,
                    'notes' => $data['notes'] ?? null,
                ]);

                $movement = $this->createStockMovement(
                    productId: $product->id,
                    warehouseId: $warehouseId,
                    adjustmentNumber: $adjustment->adjustment_number,
                    stockQty: $stockQty,
                    qtyChange: $qtyChange,
                    weightBefore: $weightBefore,
                    weightAfter: $weightAfter,
                    weightChange: $weightChange,
                    reason: $reason,
                );

                $stockQty->update([
                    'total_qty' => $qtyAfter,
                    'total_weight_kg' => $weightAfter,
                    'last_movement_id' => $movement->id,
                ]);

                Log::info('Stock adjustment enregistré', [
                    'adjustment' => $adjustment->adjustment_number,
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouseId,
                    'qty_before' => $qtyBefore,
                    'qty_after' => $qtyAfter,
                    'qty_change' => $qtyChange,
                    'weight_before' => $weightBefore,
                    'weight_after' => $weightAfter,
                    'weight_change' => $weightChange,
                ]);

                return $adjustment->refresh();
            });
        } catch (Exception $e) {
            Log::error('Stock adjustment failed', [
                'error' => $e->getMessage(),
                'product_id' => $data['product_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'] ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Marque un ajustement comme approuvé.
     */
    public function approve(StockAdjustment $adjustment): void
    {
        $adjustment->update([
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        Log::info('Stock adjustment approuvé', [
            'adjustment' => $adjustment->adjustment_number,
            'user_id' => Auth::id(),
        ]);
    }

    protected function createStockMovement(
        int $productId,
        int $warehouseId,
        string $adjustmentNumber,
        StockQuantity $stockQty,
        float $qtyChange,
        float $weightBefore,
        float $weightAfter,
        float $weightChange,
        string $reason,
    ): StockMovement {
        $isIncrease = $qtyChange > 0 || ($qtyChange == 0.0 && $weightChange > 0);
        $isDecrease = $qtyChange < 0 || ($qtyChange == 0.0 && $weightChange < 0);

        return StockMovement::create([
            'movement_number' => StockMovement::generateMovementNumber(),
            'product_id' => $productId,
            'warehouse_to_id' => $isIncrease ? $warehouseId : null,
            'warehouse_from_id' => $isDecrease ? $warehouseId : null,
            'movement_type' => 'ADJUSTMENT',
            'qty_moved' => abs($qtyChange),
            'cump_at_movement' => $stockQty->cump_snapshot,
            'value_moved' => abs($qtyChange) * (float) ($stockQty->cump_snapshot ?? 0),
            'status' => 'confirmed',
            'reference_number' => $adjustmentNumber,
            'user_id' => Auth::id() ?? 1,
            'performed_at' => now(),
            'notes' => sprintf('Ajustement manuel : %s', $reason),
            'roll_weight_before_kg' => $weightBefore,
            'roll_weight_after_kg' => $weightAfter,
            'roll_weight_delta_kg' => $weightChange,
        ]);
    }

    protected function isNoop(float $qtyChange, float $weightChange): bool
    {
        return abs($qtyChange) < 0.0001 && abs($weightChange) < 0.0001;
    }
}

