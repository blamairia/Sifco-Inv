<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockMovement extends Model
{
    protected $table = 'stock_movements';

    protected $fillable = [
        'movement_number',
        'product_id',
        'warehouse_from_id',
        'warehouse_to_id',
        'movement_type',
        'qty_moved',
        'roll_weight_before_kg',
        'roll_weight_after_kg',
        'roll_weight_delta_kg',
        'roll_length_before_m',
        'roll_length_after_m',
        'roll_length_delta_m',
        'cump_at_movement',
        'value_moved',
        'status',
        'reference_number',
        'user_id',
        'performed_at',
        'approved_by_id',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'qty_moved' => 'decimal:2',
        'cump_at_movement' => 'decimal:2',
        'value_moved' => 'decimal:2',
        'roll_weight_before_kg' => 'decimal:3',
        'roll_weight_after_kg' => 'decimal:3',
        'roll_weight_delta_kg' => 'decimal:3',
    'roll_length_before_m' => 'decimal:3',
    'roll_length_after_m' => 'decimal:3',
    'roll_length_delta_m' => 'decimal:3',
        'performed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouseFrom(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_from_id');
    }

    public function warehouseTo(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_to_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'draft');
    }

    // Helper methods
    public function isReception(): bool
    {
        return $this->movement_type === 'RECEPTION';
    }

    public function isIssue(): bool
    {
        return $this->movement_type === 'ISSUE';
    }

    public function isTransfer(): bool
    {
        return $this->movement_type === 'TRANSFER';
    }

    public function isReturn(): bool
    {
        return $this->movement_type === 'RETURN';
    }

    public function isAdjustment(): bool
    {
        return $this->movement_type === 'ADJUSTMENT';
    }

    public static function generateMovementNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', now()->toDateString())->count() + 1;
        return 'SMOV-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
