<?php

namespace App\Services;

use App\Models\StockQuantity;

class CumpCalculator
{
    /**
     * Calculate new CUMP (Coût Unitaire Moyen Pondéré / Weighted Average Cost)
     * 
     * Formula: (old_qty × old_cump + new_qty × new_price) / (old_qty + new_qty)
     * 
     * @param int $productId
     * @param int $warehouseId
     * @param float $newQty
     * @param float $unitPrice
     * @return float The new CUMP value
     */
    public static function calculate(int $productId, int $warehouseId, float $newQty, float $unitPrice): float
    {
        $stockQty = StockQuantity::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        // First entry for this product/warehouse
        if (!$stockQty || $stockQty->total_qty == 0) {
            return $unitPrice;
        }

        $oldQty = $stockQty->total_qty;
        $oldCump = $stockQty->cump_snapshot;

        // Weighted average calculation
        return ($oldQty * $oldCump + $newQty * $unitPrice) / ($oldQty + $newQty);
    }

    /**
     * Get current CUMP for a product at a warehouse
     * 
     * @param int $productId
     * @param int $warehouseId
     * @return float|null Current CUMP or null if no stock
     */
    public static function getCurrentCump(int $productId, int $warehouseId): ?float
    {
        $stockQty = StockQuantity::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        return $stockQty ? $stockQty->cump_snapshot : null;
    }
}
