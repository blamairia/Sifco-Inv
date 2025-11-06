<?php

namespace App\Services;

use App\Models\BonSortie;
use App\Models\BonSortieItem;
use App\Models\Roll;
use App\Models\StockMovement;
use App\Models\StockQuantity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BonSortieService
{
    /**
     * Issue a Bon de Sortie (draft -> issued)
     * - Update Roll statuses to 'consumed'
     * - Decrement StockQuantity for products
     * - Create StockMovement for all items
     *
     * @param BonSortie $bonSortie
     * @throws Exception
     */
    public function issue(BonSortie $bonSortie): void
    {
        if ($bonSortie->status !== 'draft') {
            throw new Exception("Only draft bons can be issued. Current status: {$bonSortie->status}");
        }

        if (!$bonSortie->warehouse_id) {
            throw new Exception("Warehouse is required to issue the bon.");
        }

        DB::beginTransaction();

        try {
            $items = $bonSortie->bonSortieItems;
            Log::info("Processing {$items->count()} items for bon de sortie {$bonSortie->bon_number}");

            foreach ($items as $item) {
                if ($item->roll_id) {
                    $this->processRollItem($item, $bonSortie);
                } else {
                    $this->processProductItem($item, $bonSortie);
                }
            }

            // Update bon status and issued date
            $bonSortie->update([
                'status' => 'issued',
                'issued_date' => now(),
                'issued_by_id' => Auth::id(),
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error issuing bon de sortie: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Process a roll item (bobine)
     */
    protected function processRollItem(BonSortieItem $item, BonSortie $bonSortie): void
    {
        $roll = Roll::findOrFail($item->roll_id);

        if ($roll->status !== Roll::STATUS_IN_STOCK) {
            throw new Exception("Roll #{$roll->id} (EAN: {$roll->ean_13}) is not in stock. Current status: {$roll->status}");
        }

        $previousWeight = $roll->weight;

        // Update roll status
        $roll->update(['status' => Roll::STATUS_CONSUMED]);

        // Get current CUMP for movement record
        $cump = StockQuantity::where('product_id', $roll->product_id)
            ->where('warehouse_id', $bonSortie->warehouse_id)
            ->value('cump_snapshot') ?? 0;

        $item->update([
            'qty_issued' => 1,
            'weight_kg' => $previousWeight,
            'cump_at_issue' => $cump,
        ]);

        // Create stock movement
        StockMovement::create([
            'movement_number' => $this->generateMovementNumber(),
            'product_id' => $roll->product_id,
            'warehouse_from_id' => $bonSortie->warehouse_id,
            'movement_type' => 'ISSUE',
            'qty_moved' => 1,
            'cump_at_movement' => $cump,
            'status' => 'confirmed',
            'reference_number' => $bonSortie->bon_number,
            'user_id' => Auth::id() ?? 1,
            'performed_at' => now(),
            'notes' => "Sortie Bobine EAN: {$roll->ean_13} via Bon de Sortie #{$bonSortie->bon_number}",
            'roll_weight_before_kg' => $previousWeight,
            'roll_weight_after_kg' => 0,
            'roll_weight_delta_kg' => -$previousWeight,
        ]);

        // Update stock quantity
        $this->updateStockQuantity($roll->product_id, $bonSortie->warehouse_id, 1, $previousWeight);
    }

    /**
     * Process a normal product item
     */
    protected function processProductItem(BonSortieItem $item, BonSortie $bonSortie): void
    {
        $stockQty = StockQuantity::where('product_id', $item->product_id)
            ->where('warehouse_id', $bonSortie->warehouse_id)
            ->first();

        if (!$stockQty || $stockQty->total_qty < $item->qty_issued) {
            throw new Exception("Insufficient stock for product #{$item->product_id}. Required: {$item->qty_issued}, Available: " . ($stockQty->total_qty ?? 0));
        }

        // Create stock movement
        StockMovement::create([
            'movement_number' => $this->generateMovementNumber(),
            'product_id' => $item->product_id,
            'warehouse_from_id' => $bonSortie->warehouse_id,
            'movement_type' => 'ISSUE',
            'qty_moved' => $item->qty_issued,
            'cump_at_movement' => $stockQty->cump_snapshot,
            'status' => 'confirmed',
            'reference_number' => $bonSortie->bon_number,
            'user_id' => Auth::id() ?? 1,
            'performed_at' => now(),
            'notes' => "Sortie produit via Bon de Sortie #{$bonSortie->bon_number}",
        ]);

        // Update stock quantity
        $this->updateStockQuantity($item->product_id, $bonSortie->warehouse_id, $item->qty_issued);
    }

    /**
     * Update stock quantity record by decrementing
     */
    protected function updateStockQuantity(int $productId, int $warehouseId, float $qtyToDecrement, float $weightToDecrement = 0): void
    {
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
     * Generate unique movement number
     */
    protected function generateMovementNumber(): string
    {
        $date = now()->format('Ymd');
        $count = StockMovement::whereDate('created_at', now()->toDateString())->count() + 1;
        return 'MOV-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
