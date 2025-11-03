<?php

namespace App\Services;

use App\Models\BonTransfert;
use App\Models\Roll;
use App\Models\StockMovement;
use App\Models\StockQuantity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BonTransfertService
{
    /**
     * Execute the transfer: move stock from source to destination warehouse
     * 
     * @param BonTransfert $bonTransfert
     * @return void
     * @throws \Exception
     */
    public function transfer(BonTransfert $bonTransfert): void
    {
        if ($bonTransfert->status !== 'draft') {
            throw new \Exception("Can only transfer drafts. Current status: {$bonTransfert->status}");
        }

        DB::beginTransaction();
        try {
            // Validate stock availability in source warehouse
            $this->validateStockAvailability($bonTransfert);

            // Process each item
            foreach ($bonTransfert->bonTransfertItems as $item) {
                if ($item->item_type === 'roll') {
                    $this->processRollItem($bonTransfert, $item);
                } else {
                    $this->processProductItem($bonTransfert, $item);
                }
            }

            // Update bon status
            $bonTransfert->update([
                'status' => 'transferred',
                'transferred_at' => now(),
            ]);

            DB::commit();

            Log::info("BonTransfert {$bonTransfert->bon_number} transferred successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("BonTransfert transfer failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Validate that source warehouse has sufficient stock
     */
    protected function validateStockAvailability(BonTransfert $bonTransfert): void
    {
        foreach ($bonTransfert->bonTransfertItems as $item) {
            if ($item->item_type === 'roll') {
                // Check roll exists and is in source warehouse
                $roll = Roll::find($item->roll_id);
                if (!$roll) {
                    throw new \Exception("Roll ID {$item->roll_id} not found");
                }
                if ($roll->warehouse_id != $bonTransfert->warehouse_from_id) {
                    throw new \Exception("Roll {$roll->ean_13} is not in source warehouse");
                }
                if ($roll->status !== 'in_stock') {
                    throw new \Exception("Roll {$roll->ean_13} is not available (status: {$roll->status})");
                }
            } else {
                // Check product stock quantity
                $stockQty = StockQuantity::where('product_id', $item->product_id)
                    ->where('warehouse_id', $bonTransfert->warehouse_from_id)
                    ->first();

                if (!$stockQty || $stockQty->available_qty < $item->qty_transferred) {
                    $available = $stockQty->available_qty ?? 0;
                    throw new \Exception("Insufficient stock for product ID {$item->product_id}. Available: {$available}, Requested: {$item->qty_transferred}");
                }
            }
        }
    }

    /**
     * Process roll transfer: update roll's warehouse_id
     */
    protected function processRollItem(BonTransfert $bonTransfert, $item): void
    {
        $roll = Roll::findOrFail($item->roll_id);

        // Create transfer OUT movement (source warehouse)
        $movementOut = StockMovement::create([
            'movement_number' => $this->generateMovementNumber('TRF-OUT'),
            'product_id' => $item->product_id,
            'warehouse_from_id' => $bonTransfert->warehouse_from_id,
            'warehouse_to_id' => $bonTransfert->warehouse_to_id,
            'movement_type' => 'TRANSFER',
            'qty_moved' => -$item->qty_transferred,
            'cump_at_movement' => $item->cump_at_transfer,
            'reference_number' => $bonTransfert->bon_number,
            'user_id' => auth()->id() ?? 1,
            'performed_at' => now(),
            'status' => 'confirmed',
            'notes' => "Transfer to " . $bonTransfert->warehouseTo->name . " - " . $bonTransfert->warehouseTo->warehouse_type,
        ]);

        // Create transfer IN movement (destination warehouse)
        $movementIn = StockMovement::create([
            'movement_number' => $this->generateMovementNumber('TRF-IN'),
            'product_id' => $item->product_id,
            'warehouse_from_id' => $bonTransfert->warehouse_from_id,
            'warehouse_to_id' => $bonTransfert->warehouse_to_id,
            'movement_type' => 'TRANSFER',
            'qty_moved' => $item->qty_transferred,
            'cump_at_movement' => $item->cump_at_transfer,
            'reference_number' => $bonTransfert->bon_number,
            'user_id' => auth()->id() ?? 1,
            'performed_at' => now(),
            'status' => 'confirmed',
            'notes' => "Transfer from " . $bonTransfert->warehouseFrom->name . " - " . $bonTransfert->warehouseFrom->warehouse_type,
        ]);

        // Update roll's warehouse
        $roll->update([
            'warehouse_id' => $bonTransfert->warehouse_to_id,
            'received_from_movement_id' => $movementIn->id,
        ]);

        // Update source warehouse stock quantity (decrease)
        $this->updateStockQuantity(
            $item->product_id,
            $bonTransfert->warehouse_from_id,
            -$item->qty_transferred,
            $item->cump_at_transfer,
            $movementOut->id
        );

        // Update destination warehouse stock quantity (increase)
        $this->updateStockQuantity(
            $item->product_id,
            $bonTransfert->warehouse_to_id,
            $item->qty_transferred,
            $item->cump_at_transfer,
            $movementIn->id
        );
    }

    /**
     * Process product transfer: update stock quantities in both warehouses
     */
    protected function processProductItem(BonTransfert $bonTransfert, $item): void
    {
        // Create transfer OUT movement (source warehouse)
        $movementOut = StockMovement::create([
            'movement_number' => $this->generateMovementNumber('TRF-OUT'),
            'product_id' => $item->product_id,
            'warehouse_from_id' => $bonTransfert->warehouse_from_id,
            'warehouse_to_id' => $bonTransfert->warehouse_to_id,
            'movement_type' => 'TRANSFER',
            'qty_moved' => -$item->qty_transferred,
            'cump_at_movement' => $item->cump_at_transfer,
            'reference_number' => $bonTransfert->bon_number,
            'user_id' => auth()->id() ?? 1,
            'performed_at' => now(),
            'status' => 'confirmed',
            'notes' => "Transfer to " . $bonTransfert->warehouseTo->name . " - " . $bonTransfert->warehouseTo->warehouse_type,
        ]);

        // Create transfer IN movement (destination warehouse)
        $movementIn = StockMovement::create([
            'movement_number' => $this->generateMovementNumber('TRF-IN'),
            'product_id' => $item->product_id,
            'warehouse_from_id' => $bonTransfert->warehouse_from_id,
            'warehouse_to_id' => $bonTransfert->warehouse_to_id,
            'movement_type' => 'TRANSFER',
            'qty_moved' => $item->qty_transferred,
            'cump_at_movement' => $item->cump_at_transfer,
            'reference_number' => $bonTransfert->bon_number,
            'user_id' => auth()->id() ?? 1,
            'performed_at' => now(),
            'status' => 'confirmed',
            'notes' => "Transfer from " . $bonTransfert->warehouseFrom->name . " - " . $bonTransfert->warehouseFrom->warehouse_type,
        ]);

        // Update source warehouse stock quantity (decrease)
        $this->updateStockQuantity(
            $item->product_id,
            $bonTransfert->warehouse_from_id,
            -$item->qty_transferred,
            $item->cump_at_transfer,
            $movementOut->id
        );

        // Update destination warehouse stock quantity (increase)
        $this->updateStockQuantity(
            $item->product_id,
            $bonTransfert->warehouse_to_id,
            $item->qty_transferred,
            $item->cump_at_transfer,
            $movementIn->id
        );
    }

    /**
     * Update stock quantity for a product in a warehouse
     * CUMP is PRESERVED during transfer (not recalculated)
     */
    protected function updateStockQuantity(
        int $productId,
        int $warehouseId,
        float $qtyChange,
        float $cump,
        int $lastMovementId
    ): void {
        $stockQty = StockQuantity::firstOrNew([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
        ]);

        $stockQty->total_qty = ($stockQty->total_qty ?? 0) + $qtyChange;
        $stockQty->available_qty = ($stockQty->available_qty ?? 0) + $qtyChange;
        
        // IMPORTANT: Preserve CUMP during transfer (do not recalculate)
        if (!$stockQty->exists || $stockQty->cump_snapshot == 0) {
            $stockQty->cump_snapshot = $cump;
        }
        
        $stockQty->last_movement_id = $lastMovementId;
        $stockQty->save();
    }

    /**
     * Generate unique movement number
     */
    protected function generateMovementNumber(string $prefix): string
    {
        $date = now()->format('Ymd');
        $count = StockMovement::whereDate('created_at', now()->toDateString())
            ->where('movement_number', 'like', "{$prefix}-{$date}-%")
            ->count() + 1;
        
        return "{$prefix}-{$date}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
