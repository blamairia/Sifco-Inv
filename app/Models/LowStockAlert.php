<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LowStockAlert extends Model
{
    protected $table = 'low_stock_alerts';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'current_qty',
        'min_stock',
        'safety_stock',
        'severity',
        'status',
        'notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'current_qty' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'safety_stock' => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'ACTIVE');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'RESOLVED');
    }

    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeSafetyStockAlerts($query)
    {
        return $query->where('alert_type', 'safety_stock_reached');
    }

    // Helper methods
    public static function generateAlertNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', now()->toDateString())->count() + 1;
        return 'ALERT-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function acknowledge(int $userId, ?float $reorderQty = null): void
    {
        $this->update([
            'is_acknowledged' => true,
            'acknowledged_by_id' => $userId,
            'acknowledged_at' => now(),
            'reorder_requested' => $reorderQty !== null,
            'reorder_qty' => $reorderQty,
        ]);
    }

    public function isMinStockAlert(): bool
    {
        return $this->alert_type === 'min_stock_reached';
    }

    public function isSafetyStockAlert(): bool
    {
        return $this->alert_type === 'safety_stock_reached';
    }
}
