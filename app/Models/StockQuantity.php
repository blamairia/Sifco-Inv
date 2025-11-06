<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockQuantity extends Model
{
    protected $table = 'stock_quantities';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'total_qty',
        'total_weight_kg',
        'reserved_qty',
        'cump_snapshot',
        'last_movement_id',
    ];

    protected $casts = [
        'total_qty' => 'decimal:2',
        'total_weight_kg' => 'decimal:3',
        'reserved_qty' => 'decimal:2',
        'available_qty' => 'decimal:2',
        'cump_snapshot' => 'decimal:2',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function lastMovement(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class, 'last_movement_id');
    }

    // Scopes
    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('total_qty', '<', 'min_stock')
            ->orWhereColumn('total_qty', '<', 'safety_stock');
    }

    // Helper methods
    public function getAvailableQtyAttribute(): float
    {
        return $this->total_qty - $this->reserved_qty;
    }

    public function isLowStock(): bool
    {
        $product = $this->product;
        return $this->total_qty < $product->min_stock 
            || $this->total_qty < $product->safety_stock;
    }

    public function getTotalValueAttribute(): float
    {
        return $this->total_qty * $this->cump_snapshot;
    }
}
