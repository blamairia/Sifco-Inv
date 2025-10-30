<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    protected $table = 'stock_adjustments';

    protected $fillable = [
        'adjustment_number',
        'product_id',
        'warehouse_id',
        'qty_adjustment',
        'reason',
        'adjustment_date',
        'status',
        'created_by_id',
        'approved_by_id',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'qty_adjustment' => 'decimal:2',
        'adjustment_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public static function generateAdjustmentNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', now()->toDateString())->count() + 1;
        return 'ADJ-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
