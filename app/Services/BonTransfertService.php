<?php

namespace App\Services;

use App\Models\BonTransfert;
use App\Models\Roll;
use App\Models\StockMovement;
use App\Models\StockQuantity;
use Illuminate\Support\Facades\Auth;
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
                'status' => 'in_transit',
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
                if ($roll->status !== Roll::STATUS_IN_STOCK) {
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
     * IMPORTANT: Rolls are ALWAYS tracked as 1 unit (not by weight)
     */
    protected function processRollItem(BonTransfert $bonTransfert, $item): void
    {
        $roll = Roll::findOrFail($item->roll_id);
        $weight = $roll->weight;

        // Create transfer OUT movement (source warehouse)
        // ROLLS ARE ALWAYS QTY = 1 (one roll = one unit)
        $movementOut = StockMovement::create([
            'movement_number' => $this->generateMovementNumber('TRF-OUT'),
            'product_id' => $item->product_id,
            'warehouse_from_id' => $bonTransfert->warehouse_from_id,
            'warehouse_to_id' => $bonTransfert->warehouse_to_id,
            'movement_type' => 'TRANSFER',
            'qty_moved' => -1, // ALWAYS -1 for roll transfer OUT
            'cump_at_movement' => $item->cump_at_transfer,
            'reference_number' => $bonTransfert->bon_number,
            'user_id' => Auth::id() ?? 1,
            'performed_at' => now(),
            'status' => 'confirmed',
            'notes' => "Transfer Roll EAN: {$roll->ean_13} to " . $bonTransfert->warehouseTo->name,
            'roll_weight_before_kg' => $weight,
            'roll_weight_after_kg' => 0,
            'roll_weight_delta_kg' => -$weight,
        ]);

        // Create transfer IN movement (destination warehouse)
        // ROLLS ARE ALWAYS QTY = 1 (one roll = one unit)
        $movementIn = StockMovement::create([
            'movement_number' => $this->generateMovementNumber('TRF-IN'),
            'product_id' => $item->product_id,
            'warehouse_from_id' => $bonTransfert->warehouse_from_id,
            'warehouse_to_id' => $bonTransfert->warehouse_to_id,
            'movement_type' => 'TRANSFER',
            'qty_moved' => 1, // ALWAYS 1 for roll transfer IN
            'cump_at_movement' => $item->cump_at_transfer,
            'reference_number' => $bonTransfert->bon_number,
            'user_id' => Auth::id() ?? 1,
            'performed_at' => now(),
            'status' => 'confirmed',
            'notes' => "Transfer Roll EAN: {$roll->ean_13} from " . $bonTransfert->warehouseFrom->name,
            'roll_weight_before_kg' => 0,
            'roll_weight_after_kg' => $weight,
            'roll_weight_delta_kg' => $weight,
        ]);

        // Update roll's warehouse
        $roll->update([
            'warehouse_id' => $bonTransfert->warehouse_to_id,
            'received_from_movement_id' => $movementIn->id,
        ]);

        // Update source warehouse stock quantity (decrease by 1 roll)
        $this->decrementStockQuantity(
            $item->product_id,
            $bonTransfert->warehouse_from_id,
            1, // ALWAYS 1 for rolls
            $weight
        );

        // Update destination warehouse stock quantity (increase by 1 roll)
        $this->incrementStockQuantity(
            $item->product_id,
            $bonTransfert->warehouse_to_id,
            1, // ALWAYS 1 for rolls
            $weight
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
            'user_id' => Auth::id() ?? 1,
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
            'user_id' => Auth::id() ?? 1,
            'performed_at' => now(),
            'status' => 'confirmed',
            'notes' => "Transfer from " . $bonTransfert->warehouseFrom->name . " - " . $bonTransfert->warehouseFrom->warehouse_type,
        ]);

        // Update source warehouse stock quantity (decrease)
        $this->decrementStockQuantity(
            $item->product_id,
            $bonTransfert->warehouse_from_id,
            $item->qty_transferred
        );

        // Update destination warehouse stock quantity (increase)
        $this->incrementStockQuantity(
            $item->product_id,
            $bonTransfert->warehouse_to_id,
            $item->qty_transferred
        );
    }

    /**
     * Decrement stock quantity (for source warehouse during transfer)
     */
    protected function decrementStockQuantity(
        int $productId,
        int $warehouseId,
        float $qtyToDecrement,
        float $weightToDecrement = 0
    ): void {
        $stockQty = StockQuantity::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->firstOrFail();

        $currentQty = (float) $stockQty->total_qty;
        $stockQty->total_qty = max(0, $currentQty - $qtyToDecrement);

        if ($weightToDecrement !== 0) {
            $currentWeight = (float) ($stockQty->total_weight_kg ?? 0);
            $stockQty->total_weight_kg = max(0, $currentWeight - $weightToDecrement);
        }

        $stockQty->save();
    }

    /**
     * Increment stock quantity (for destination warehouse during transfer)
     */
    protected function incrementStockQuantity(
        int $productId,
        int $warehouseId,
        float $qtyToIncrement,
        float $weightToIncrement = 0
    ): void {
        $stockQty = StockQuantity::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->first();

        if ($stockQty) {
            // Increment existing stock
            $stockQty->total_qty = (float) $stockQty->total_qty + $qtyToIncrement;

            if ($weightToIncrement !== 0) {
                $stockQty->total_weight_kg = (float) ($stockQty->total_weight_kg ?? 0) + $weightToIncrement;
            }

            $stockQty->save();
        } else {
            // Create new stock quantity record for destination warehouse
            StockQuantity::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'total_qty' => $qtyToIncrement,
                'total_weight_kg' => $weightToIncrement,
                'cump_snapshot' => 0, // Will be set properly on first reception
            ]);
        }
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
