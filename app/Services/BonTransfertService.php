<?php

namespace App\Services;

use App\Models\BonTransfert;
use App\Models\BonTransfertItem;
use App\Models\Roll;
use App\Models\StockMovement;
use App\Models\StockQuantity;
use App\Services\CumpCalculator;
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
            $bonTransfert->loadMissing(['bonTransfertItems', 'warehouseFrom', 'warehouseTo']);

            $this->validateStockAvailability($bonTransfert);

            foreach ($bonTransfert->bonTransfertItems as $item) {
                if ($item->item_type === 'roll') {
                    $this->processRollTransfer($bonTransfert, $item);
                } else {
                    $this->processProductTransfer($bonTransfert, $item);
                }
            }

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
     * Receive the transfer at the destination warehouse.
     */
    public function receive(BonTransfert $bonTransfert): void
    {
        if ($bonTransfert->status !== 'in_transit') {
            throw new \Exception("Only in-transit transfers can be received. Current status: {$bonTransfert->status}");
        }

        DB::beginTransaction();
        try {
            $bonTransfert->loadMissing(['bonTransfertItems', 'warehouseTo']);

            foreach ($bonTransfert->bonTransfertItems as $item) {
                if ($item->item_type === 'roll') {
                    $this->receiveRollItem($bonTransfert, $item);
                } else {
                    $this->receiveProductItem($bonTransfert, $item);
                }
            }

            $bonTransfert->update([
                'status' => 'received',
                'received_at' => now(),
                'received_by_id' => Auth::id() ?? 1,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("BonTransfert receive failed: " . $e->getMessage());
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
    protected function processRollTransfer(BonTransfert $bonTransfert, BonTransfertItem $item): void
    {
        $roll = Roll::lockForUpdate()->findOrFail($item->roll_id);
        $weight = $roll->weight;

        $movementOut = StockMovement::create([
            'movement_number' => $this->generateMovementNumber('TRF-OUT'),
            'product_id' => $item->product_id,
            'warehouse_from_id' => $bonTransfert->warehouse_from_id,
            'warehouse_to_id' => $bonTransfert->warehouse_to_id,
            'movement_type' => 'TRANSFER',
            'qty_moved' => -1,
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

        $movementIn = StockMovement::create([
            'movement_number' => $this->generateMovementNumber('TRF-IN'),
            'product_id' => $item->product_id,
            'warehouse_from_id' => $bonTransfert->warehouse_from_id,
            'warehouse_to_id' => $bonTransfert->warehouse_to_id,
            'movement_type' => 'TRANSFER',
            'qty_moved' => 1,
            'cump_at_movement' => $item->cump_at_transfer,
            'reference_number' => $bonTransfert->bon_number,
            'user_id' => Auth::id() ?? 1,
            'performed_at' => now(),
            'status' => 'pending',
            'notes' => "Transfer Roll EAN: {$roll->ean_13} from " . $bonTransfert->warehouseFrom->name,
            'roll_weight_before_kg' => 0,
            'roll_weight_after_kg' => $weight,
            'roll_weight_delta_kg' => $weight,
        ]);

        $roll->update([
            'warehouse_id' => $bonTransfert->warehouse_to_id,
            'status' => Roll::STATUS_RESERVED,
        ]);

        $this->decrementStockQuantity(
            $item->product_id,
            $bonTransfert->warehouse_from_id,
            1,
            $weight,
            $movementOut->id
        );

        $item->update([
            'movement_out_id' => $movementOut->id,
            'movement_in_id' => $movementIn->id,
            'weight_transferred_kg' => $weight,
        ]);
    }

    protected function processProductTransfer(BonTransfert $bonTransfert, BonTransfertItem $item): void
    {
        $qty = (float) $item->qty_transferred;

        $movementOut = StockMovement::create([
            'movement_number' => $this->generateMovementNumber('TRF-OUT'),
            'product_id' => $item->product_id,
            'warehouse_from_id' => $bonTransfert->warehouse_from_id,
            'warehouse_to_id' => $bonTransfert->warehouse_to_id,
            'movement_type' => 'TRANSFER',
            'qty_moved' => -$qty,
            'cump_at_movement' => $item->cump_at_transfer,
            'reference_number' => $bonTransfert->bon_number,
            'user_id' => Auth::id() ?? 1,
            'performed_at' => now(),
            'status' => 'confirmed',
            'notes' => "Transfer to " . $bonTransfert->warehouseTo->name . " - " . $bonTransfert->warehouseTo->warehouse_type,
        ]);

        $movementIn = StockMovement::create([
            'movement_number' => $this->generateMovementNumber('TRF-IN'),
            'product_id' => $item->product_id,
            'warehouse_from_id' => $bonTransfert->warehouse_from_id,
            'warehouse_to_id' => $bonTransfert->warehouse_to_id,
            'movement_type' => 'TRANSFER',
            'qty_moved' => $qty,
            'cump_at_movement' => $item->cump_at_transfer,
            'reference_number' => $bonTransfert->bon_number,
            'user_id' => Auth::id() ?? 1,
            'performed_at' => now(),
            'status' => 'pending',
            'notes' => "Transfer from " . $bonTransfert->warehouseFrom->name . " - " . $bonTransfert->warehouseFrom->warehouse_type,
        ]);

        $this->decrementStockQuantity(
            $item->product_id,
            $bonTransfert->warehouse_from_id,
            $qty,
            0,
            $movementOut->id
        );

        $item->update([
            'movement_out_id' => $movementOut->id,
            'movement_in_id' => $movementIn->id,
        ]);
    }

    protected function decrementStockQuantity(
        int $productId,
        int $warehouseId,
        float $qtyToDecrement,
        float $weightToDecrement = 0,
        ?int $movementId = null
    ): void {
        $stockQty = StockQuantity::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->firstOrFail();

        $currentQty = (float) $stockQty->total_qty;
        $stockQty->total_qty = max(0, $currentQty - $qtyToDecrement);

        if ($weightToDecrement !== 0) {
            $currentWeight = (float) ($stockQty->total_weight_kg ?? 0);
            $stockQty->total_weight_kg = max(0, $currentWeight - $weightToDecrement);
        }

        if ($movementId) {
            $stockQty->last_movement_id = $movementId;
        }

        $stockQty->save();
    }

    protected function incrementDestinationStockQuantity(
        int $productId,
        int $warehouseId,
        float $qtyToIncrement,
        float $weightToIncrement,
        float $newCump,
        int $movementId
    ): StockQuantity {
        $stockQty = StockQuantity::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->lockForUpdate()
            ->first();

        if (! $stockQty) {
            $stockQty = StockQuantity::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'total_qty' => 0,
                'total_weight_kg' => 0,
                'reserved_qty' => 0,
                'cump_snapshot' => 0,
            ]);

            $stockQty = StockQuantity::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();
        }

        $stockQty->total_qty = (float) $stockQty->total_qty + $qtyToIncrement;

        if ($weightToIncrement !== 0) {
            $stockQty->total_weight_kg = (float) ($stockQty->total_weight_kg ?? 0) + $weightToIncrement;
        }

        $stockQty->cump_snapshot = $newCump;
        $stockQty->last_movement_id = $movementId;
        $stockQty->save();

        return $stockQty;
    }

    protected function receiveRollItem(BonTransfert $bonTransfert, BonTransfertItem $item): void
    {
        $movementIn = $item->movementIn;

        if (! $movementIn) {
            throw new \Exception('Missing inbound movement for roll transfer item.');
        }

        $movementIn->update([
            'status' => 'confirmed',
            'performed_at' => now(),
        ]);

        $roll = Roll::lockForUpdate()->findOrFail($item->roll_id);
        $roll->update([
            'status' => Roll::STATUS_IN_STOCK,
            'warehouse_id' => $bonTransfert->warehouse_to_id,
            'received_from_movement_id' => $movementIn->id,
        ]);

        $weight = (float) ($item->weight_transferred_kg ?? $roll->weight);
        $newCump = CumpCalculator::calculate(
            $item->product_id,
            $bonTransfert->warehouse_to_id,
            1,
            (float) $item->cump_at_transfer,
        );

        $this->incrementDestinationStockQuantity(
            $item->product_id,
            $bonTransfert->warehouse_to_id,
            1,
            $weight,
            $newCump,
            $movementIn->id,
        );
    }

    protected function receiveProductItem(BonTransfert $bonTransfert, BonTransfertItem $item): void
    {
        $movementIn = $item->movementIn;

        if (! $movementIn) {
            throw new \Exception('Missing inbound movement for product transfer item.');
        }

        $movementIn->update([
            'status' => 'confirmed',
            'performed_at' => now(),
        ]);

        $qty = (float) $item->qty_transferred;
        $newCump = CumpCalculator::calculate(
            $item->product_id,
            $bonTransfert->warehouse_to_id,
            $qty,
            (float) $item->cump_at_transfer,
        );

        $this->incrementDestinationStockQuantity(
            $item->product_id,
            $bonTransfert->warehouse_to_id,
            $qty,
            0,
            $newCump,
            $movementIn->id,
        );
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
