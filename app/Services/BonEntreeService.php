<?php

namespace App\Services;

use App\Models\BonEntree;
use App\Models\BonEntreeItem;
use App\Models\Roll;
use App\Models\RollLifecycleEvent;
use App\Models\StockMovement;
use App\Models\StockQuantity;
use App\Services\CumpCalculator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BonEntreeService
{
    /**
     * Validate Bon d'Entrée (draft → pending)
     * - Calculate frais d'approche distribution
     * - Update price_ttc for all items
     * 
     * @param BonEntree $bonEntree
     * @throws Exception
     */
    public function validate(BonEntree $bonEntree): void
    {
        if ($bonEntree->status !== 'draft') {
            throw new Exception("Only draft bons can be validated. Current status: {$bonEntree->status}");
        }

        DB::beginTransaction();

        try {
            // Calculate frais d'approche distribution
            $this->distributeFraisApproche($bonEntree);

            // Update status to pending
            $bonEntree->update([
                'status' => 'pending',
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Receive Bon d'Entrée (pending → received)
     * - Create Roll records for bobines
     * - Create StockMovement for all items
     * - Update StockQuantity with CUMP
     * 
     * @param BonEntree $bonEntree
     * @throws Exception
     */
    public function receive(BonEntree $bonEntree): void
    {
        Log::info("========== RECEIVE METHOD CALLED FOR BON: {$bonEntree->bon_number} ==========");
        
        if ($bonEntree->status !== 'pending') {
            throw new Exception("Only pending bons can be received. Current status: {$bonEntree->status}");
        }

        if (!$bonEntree->warehouse_id) {
            throw new Exception("Warehouse is required to receive the bon.");
        }

        DB::beginTransaction();

        try {
            $items = $bonEntree->bonEntreeItems;
            
            Log::info("Processing {$items->count()} items for bon {$bonEntree->bon_number}");

            foreach ($items as $item) {
                Log::info("Item #{$item->id}: type={$item->item_type}, isBobine=" . ($item->isBobine() ? 'YES' : 'NO') . ", EAN={$item->ean_13}");
                
                if ($item->isBobine()) {
                    Log::info("Processing as BOBINE");
                    $this->processBobineItem($item, $bonEntree);
                } else {
                    Log::info("Processing as PRODUCT");
                    $this->processProductItem($item, $bonEntree);
                }
            }

            // Update bon status and received date
            $bonEntree->update([
                'status' => 'received',
                'received_date' => now(),
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error receiving bon: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Process a bobine item (qty = 1 per item)
     * - Create Roll record
     * - Link roll to item
     * - Create stock movement
     * - Update stock quantity
     */
    protected function processBobineItem(BonEntreeItem $item, BonEntree $bonEntree): void
    {
        Log::info("Creating Roll for item #{$item->id} with EAN: {$item->ean_13}");

        $weight = (float) ($item->weight_kg ?? 0);
        $length = (float) ($item->length_m ?? 0);

        if ($weight <= 0) {
            $weight = (float) ($item->qty_entered ?? 0);
        }

        if ($weight <= 0) {
            throw new Exception("Poids invalide pour la bobine {$item->ean_13}. Veuillez saisir un poids en kg.");
        }

        if ($length <= 0) {
            throw new Exception("Métrage invalide pour la bobine {$item->ean_13}. Veuillez saisir une longueur en mètres.");
        }

        // Ensure persisted weight is up to date for reporting purposes
        if ((float) ($item->getAttribute('weight_kg') ?? 0) !== $weight) {
            $item->update(['weight_kg' => $weight]);
        }

        if ((float) ($item->getAttribute('length_m') ?? 0) !== $length) {
            $item->update(['length_m' => $length]);
        }
        
        // Create Roll record
        $roll = Roll::create([
            'bon_entree_item_id' => $item->id,
            'product_id' => $item->product_id,
            'warehouse_id' => $bonEntree->warehouse_id,
            'ean_13' => $item->ean_13,
            'batch_number' => $item->batch_number,
            'received_date' => $bonEntree->received_date ?? now(),
            'status' => Roll::STATUS_IN_STOCK,
            'weight_kg' => $weight,
            'length_m' => $length,
            'cump_value' => $item->price_ttc,
            'notes' => $bonEntree->notes,
        ]);

        Log::info("Roll created with ID: {$roll->id}");

        // Link roll to item
        $item->update(['roll_id' => $roll->id]);

        // Calculate CUMP (qty = 1 for bobine)
        $newCump = CumpCalculator::calculate(
            $item->product_id,
            $bonEntree->warehouse_id,
            1,
            $item->price_ttc
        );

        // Create stock movement
        $movement = StockMovement::create([
            'movement_number' => $this->generateMovementNumber(),
            'product_id' => $item->product_id,
            'warehouse_to_id' => $bonEntree->warehouse_id,
            'movement_type' => 'RECEPTION',
            'qty_moved' => 1,
            'cump_at_movement' => $newCump,
            'status' => 'confirmed',
            'reference_number' => $bonEntree->bon_number,
            'user_id' => Auth::id() ?? 1,
            'performed_at' => now(),
            'notes' => "Bobine EAN: {$item->ean_13} depuis Bon d'Entrée #{$bonEntree->bon_number}",
            'roll_weight_before_kg' => 0,
            'roll_weight_after_kg' => $weight,
            'roll_weight_delta_kg' => $weight,
            'roll_length_before_m' => 0,
            'roll_length_after_m' => $length,
            'roll_length_delta_m' => $length,
        ]);

        // Link movement to roll
        $roll->update(['received_from_movement_id' => $movement->id]);

        // Log reception event
        RollLifecycleEvent::logReception($roll, $movement);

        // Update or create stock quantity
        $this->updateStockQuantity(
            $item->product_id,
            $bonEntree->warehouse_id,
            1,
            $newCump,
            $weight,
            $length
        );
    }

    /**
     * Process a normal product item
     * - Create stock movement
     * - Update stock quantity
     */
    protected function processProductItem(BonEntreeItem $item, BonEntree $bonEntree): void
    {
        // Calculate CUMP
        $newCump = CumpCalculator::calculate(
            $item->product_id,
            $bonEntree->warehouse_id,
            $item->qty_entered,
            $item->price_ttc
        );

        // Create stock movement
        StockMovement::create([
            'movement_number' => $this->generateMovementNumber(),
            'product_id' => $item->product_id,
            'warehouse_to_id' => $bonEntree->warehouse_id,
            'movement_type' => 'RECEPTION',
            'qty_moved' => $item->qty_entered,
            'cump_at_movement' => $newCump,
            'status' => 'confirmed',
            'reference_number' => $bonEntree->bon_number,
            'user_id' => Auth::id() ?? 1,
            'performed_at' => now(),
            'notes' => "Entrée produit depuis Bon d'Entrée #{$bonEntree->bon_number}",
        ]);

        // Update or create stock quantity
        $this->updateStockQuantity(
            $item->product_id,
            $bonEntree->warehouse_id,
            $item->qty_entered,
            $newCump
        );
    }

    /**
     * Update or create stock quantity record
     */
    protected function updateStockQuantity(int $productId, int $warehouseId, float $qtyToAdd, float $newCump, ?float $weightToAdd = null, ?float $lengthToAdd = null): void
    {
        $stockQty = StockQuantity::firstOrCreate(
            [
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'total_qty' => 0,
                'total_weight_kg' => 0,
                'total_length_m' => 0,
                'cump_snapshot' => $newCump,
            ],
        );

        $stockQty->increment('total_qty', $qtyToAdd);

        if (! is_null($weightToAdd)) {
            $stockQty->increment('total_weight_kg', $weightToAdd);
        }

        if (! is_null($lengthToAdd)) {
            $stockQty->increment('total_length_m', $lengthToAdd);
        }

        $stockQty->update(['cump_snapshot' => $newCump]);
    }

    /**
     * Distribute frais d'approche across all items
     * Updates price_ttc = price_ht + (frais_per_unit)
     */
    protected function distributeFraisApproche(BonEntree $bonEntree): void
    {
        $fraisApproche = $bonEntree->frais_approche ?? 0;

        if ($fraisApproche == 0) {
            // No frais, just copy price_ht to price_ttc
            foreach ($bonEntree->bonEntreeItems as $item) {
                $item->update(['price_ttc' => $item->price_ht]);
            }
            return;
        }

        // Calculate total quantity (bobines count as 1 each, products use qty_entered)
        $totalQty = $bonEntree->bonEntreeItems->sum(function ($item) {
            return $item->isBobine() ? 1 : $item->qty_entered;
        });

        if ($totalQty == 0) {
            throw new Exception("Cannot distribute frais d'approche: total quantity is zero.");
        }

        $fraisPerUnit = $fraisApproche / $totalQty;

        // Update each item's price_ttc
        foreach ($bonEntree->bonEntreeItems as $item) {
            $qtyForCalculation = $item->isBobine() ? 1 : $item->qty_entered;
            $fraisForItem = $fraisPerUnit * $qtyForCalculation;
            $priceTtc = $item->price_ht + ($fraisForItem / $qtyForCalculation);

            $item->update(['price_ttc' => $priceTtc]);
        }

        // Recalculate totals
        $bonEntree->update([
            'total_amount_ht' => $bonEntree->bonEntreeItems->sum(function ($item) {
                $qty = $item->isBobine() ? 1 : $item->qty_entered;
                return $qty * $item->price_ht;
            }),
            'total_amount_ttc' => $bonEntree->bonEntreeItems->sum(function ($item) {
                $qty = $item->isBobine() ? 1 : $item->qty_entered;
                return $qty * $item->price_ttc;
            }),
        ]);
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
