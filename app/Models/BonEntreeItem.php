<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'price_ht',
        'price_ttc',
        // line_total_ttc is a generated column: qty_entered * price_ttc
    ];

    protected $casts = [
        'qty_entered' => 'decimal:2',
        'weight_kg' => 'decimal:3',
        'length_m' => 'decimal:3',
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
    public function scopeBobines($query)
    {
        return $query->where('item_type', 'bobine');
    }

    public function scopeProducts($query)
    {
        return $query->where('item_type', 'product');
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
