<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RollAdjustment extends Model
{
    public const TYPE_ADD = 'ADD';
    public const TYPE_REMOVE = 'REMOVE';
    public const TYPE_DAMAGE = 'DAMAGE';
    public const TYPE_RESTORE = 'RESTORE';
    public const TYPE_WEIGHT_ADJUST = 'WEIGHT_ADJUST';

    public const STATUS_IN_STOCK = 'in_stock';
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_CONSUMED = 'consumed';
    public const STATUS_DAMAGED = 'damaged';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'adjustment_number',
        'roll_id',
        'product_id',
        'warehouse_id',
        'adjustment_type',
        'previous_status',
        'new_status',
    'previous_weight_kg',
    'new_weight_kg',
    'weight_delta_kg',
        'reason',
        'adjusted_by',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'previous_weight_kg' => 'decimal:3',
        'new_weight_kg' => 'decimal:3',
        'weight_delta_kg' => 'decimal:3',
    ];

    public function roll()
    {
        return $this->belongsTo(Roll::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function adjustedBy()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
