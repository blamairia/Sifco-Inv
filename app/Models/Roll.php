<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roll extends Model
{
    public const STATUS_IN_STOCK = 'in_stock';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_CONSUMED = 'consumed';
    public const STATUS_DAMAGED = 'damaged';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'bon_entree_item_id',
        'product_id',
        'warehouse_id',
        'ean_13',
        'batch_number',
        'received_date',
        'received_from_movement_id',
        'status',
        'weight_kg',
        'length_m',
        'cump_value',
        'is_manual_entry',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
        'weight_kg' => 'decimal:3',
        'length_m' => 'decimal:3',
        'cump_value' => 'decimal:4',
        'is_manual_entry' => 'boolean',
    ];

    protected $appends = [
        'weight',
        'cump',
        'length',
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

    public function adjustments()
    {
        return $this->hasMany(RollAdjustment::class);
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
        if (! is_null($this->getAttribute('weight_kg'))) {
            return (float) $this->getAttribute('weight_kg');
        }

        return $this->bonEntreeItem?->qty_entered ?? 0;
    }

    public function getCumpAttribute()
    {
        if (! is_null($this->getAttribute('cump_value'))) {
            return (float) $this->getAttribute('cump_value');
        }

        return $this->bonEntreeItem?->price_ttc ?? 0;
    }

    public function getLengthAttribute()
    {
        if (! is_null($this->getAttribute('length_m'))) {
            return (float) $this->getAttribute('length_m');
        }

        return $this->bonEntreeItem?->length_m ?? 0;
    }
}
