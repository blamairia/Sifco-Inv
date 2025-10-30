<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BonEntreeItem extends Model
{
    protected $table = 'bon_entree_items';

    protected $fillable = [
        'bon_entree_id',
        'product_id',
        'qty_entered',
        'price_ht',
        'price_ttc',
        'line_total_ttc',
    ];

    protected $casts = [
        'qty_entered' => 'decimal:2',
        'price_ht' => 'decimal:2',
        'price_ttc' => 'decimal:2',
        'line_total_ttc' => 'decimal:2',
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
