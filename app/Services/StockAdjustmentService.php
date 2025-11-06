<?php

namespace App\Services;

use App\Models\StockAdjustment;
use App\Models\StockMovement;
use App\Models\StockQuantity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class StockAdjustmentService
{
    /**
     * Create a stock adjustment
     * - Calculate the difference between current and new quantity
     * - Create StockAdjustment record
     * - Create StockMovement for audit trail
     * - Update StockQuantity
     *
     * @param array $data - ['product_id', 'warehouse_id', 'new_quantity', 'reason', 'notes']
     * @return StockAdjustment
     * @throws Exception
     */
    public function adjust(array $data): StockAdjustment
    {
        DB::beginTransaction();

        try {
            // Get current stock quantity
            $stockQty = StockQuantity::where('product_id', $data['product_id'])
                ->where('warehouse_id', $data['warehouse_id'])
                ->first();

            if (!$stockQty) {
                throw new Exception("No stock record found for this product/warehouse combination");
            }

            $qtyBefore = $stockQty->total_qty;
            $qtyAfter = $data['new_quantity'];
            $qtyChange = $qtyAfter - $qtyBefore;

            // Determine adjustment type
            $adjustmentType = $qtyChange > 0 ? 'INCREASE' : ($qtyChange < 0 ? 'DECREASE' : 'CORRECTION');

            // Create stock adjustment record
            $adjustment = StockAdjustment::create([
                'adjustment_number' => StockAdjustment::generateAdjustmentNumber(),
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'qty_change' => $qtyChange,
                'adjustment_type' => $adjustmentType,
                'reason' => $data['reason'],
                'adjusted_by' => Auth::id() ?? 1,
                'notes' => $data['notes'] ?? null,
            ]);

            // Create stock movement for audit trail
            StockMovement::create([
                'movement_number' => $this->generateMovementNumber(),
                'product_id' => $data['product_id'],
                'warehouse_to_id' => $qtyChange > 0 ? $data['warehouse_id'] : null,
                'warehouse_from_id' => $qtyChange < 0 ? $data['warehouse_id'] : null,
                'movement_type' => 'ADJUSTMENT',
                'qty_moved' => abs($qtyChange),
                'cump_at_movement' => $stockQty->cump_snapshot,
                'status' => 'confirmed',
                'reference_number' => $adjustment->adjustment_number,
                'user_id' => Auth::id() ?? 1,
                'performed_at' => now(),
                'notes' => "Adjustment: {$data['reason']} (from {$qtyBefore} to {$qtyAfter})",
            ]);

            // Update stock quantity
            $stockQty->update([
                'total_qty' => $qtyAfter,
            ]);

            DB::commit();

            Log::info("Stock adjustment {$adjustment->adjustment_number} created successfully", [
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['warehouse_id'],
                'qty_before' => $qtyBefore,
                'qty_after' => $qtyAfter,
                'qty_change' => $qtyChange,
            ]);

            return $adjustment;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Stock adjustment failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Approve a stock adjustment
     *
     * @param StockAdjustment $adjustment
     * @return void
     */
    public function approve(StockAdjustment $adjustment): void
    {
        $adjustment->update([
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        Log::info("Stock adjustment {$adjustment->adjustment_number} approved by user " . Auth::id());
    }

    /**
     * Generate unique movement number
     */
    protected function generateMovementNumber(): string
    {
        $date = now()->format('Ymd');
        $count = StockMovement::whereDate('created_at', now()->toDateString())->count() + 1;
        return 'MOV-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
