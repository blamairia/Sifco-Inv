<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\StockQuantity;

class BonEntreeItem extends Model
{
    protected $table = 'bon_entree_items';

    protected $fillable = [
        'bon_entree_id',
        'item_type',
        'product_id',
        'ean_13',
        'batch_number',
        'roll_id',
        'qty_entered',
        'weight_kg',
        'length_m',
        'sheet_width_mm',
        'sheet_length_mm',
        'price_ht',
        'price_ttc',
        'line_total_ttc', // Added for Azure SQL compatibility (storedAs not supported)
    ];

    protected $casts = [
        'qty_entered' => 'decimal:2',
        'weight_kg' => 'decimal:3',
        'length_m' => 'decimal:3',
        'sheet_width_mm' => 'decimal:2',
        'sheet_length_mm' => 'decimal:2',
        'price_ht' => 'decimal:2',
        'price_ttc' => 'decimal:2',
        'line_total_ttc' => 'decimal:2', // Generated column, read-only
    ];

    // Relationships
    public function bonEntree(): BelongsTo
    {
        return $this->belongsTo(BonEntree::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function roll(): BelongsTo
    {
        return $this->belongsTo(Roll::class);
    }

    // Scopes
    public function scopeBobines(Builder $query): Builder
    {
        return $query->where('item_type', 'bobine');
    }

    public function scopeProducts(Builder $query): Builder
    {
        return $query->where('item_type', 'product');
    }

    public function scopePallets(Builder $query): Builder
    {
        return $query->where('item_type', 'pallet');
    }

    // Helpers
    public function isBobine(): bool
    {
        return $this->item_type === 'bobine';
    }

    public function isProduct(): bool
    {
        return $this->item_type === 'product';
    }

    public function isPallet(): bool
    {
        return $this->item_type === 'pallet';
    }

    // Helper methods
    public function calculateLineTotal(): void
    {
        $this->update([
            'line_total_ttc' => $this->qty_entered * $this->price_ttc,
        ]);
    }

    /**
     * Calculate new CUMP (Coût Unitaire Moyen Pondéré)
     * Formula: (old_qty × old_cump + new_qty × price_ttc) / (old_qty + new_qty)
     */
    public function calculateNewCUMP(): float
    {
        $currentStockQty = StockQuantity::where('product_id', $this->product_id)
            ->where('warehouse_id', $this->bonEntree->warehouse_id)
            ->first();

        if (!$currentStockQty) {
            // First entry for this product/warehouse
            return $this->price_ttc;
        }

        $oldQty = $currentStockQty->total_qty;
        $oldCump = $currentStockQty->cump_snapshot;
        $newQty = $this->qty_entered;
        $newPrice = $this->price_ttc;

        return ($oldQty * $oldCump + $newQty * $newPrice) / ($oldQty + $newQty);
    }
}
