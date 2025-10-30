<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BonTransfert extends Model
{
    protected $table = 'bon_transferts';

    protected $fillable = [
        'bon_number',
        'warehouse_from_id',
        'warehouse_to_id',
        'transfer_date',
        'status',
        'requested_by_id',
        'transferred_at',
        'received_at',
        'received_by_id',
        'notes',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'transferred_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function warehouseFrom(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_from_id');
    }

    public function warehouseTo(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_to_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_id');
    }

    public function bonTransfertItems(): HasMany
    {
        return $this->hasMany(BonTransfertItem::class);
    }

    public static function generateBonNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', now()->toDateString())->count() + 1;
        return 'BTRN-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
