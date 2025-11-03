<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roll extends Model
{
    protected $fillable = [
        'bon_entree_item_id',
        'product_id',
        'warehouse_id',
        'ean_13',
        'batch_number',
        'received_date',
        'received_from_movement_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    protected $appends = [
        'weight',
        'cump',
    ];

    // Relationships
    public function bonEntreeItem()
    {
        return $this->belongsTo(BonEntreeItem::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receivedFromMovement()
    {
        return $this->belongsTo(StockMovement::class, 'received_from_movement_id');
    }

    // Scopes
    public function scopeInStock($query)
    {
        return $query->where('status', 'in_stock');
    }

    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    // Accessors - Get weight and CUMP from the related BonEntreeItem
    public function getWeightAttribute()
    {
        return $this->bonEntreeItem?->qty_entered ?? 0;
    }

    public function getCumpAttribute()
    {
        return $this->bonEntreeItem?->price_ttc ?? 0;
    }
}
