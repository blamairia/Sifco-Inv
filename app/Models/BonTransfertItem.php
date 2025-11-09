<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\StockMovement;

class BonTransfertItem extends Model
{
    protected $table = 'bon_transfert_items';

    protected $fillable = [
        'bon_transfert_id',
        'item_type',
        'product_id',
        'roll_id',
        'qty_transferred',
        'cump_at_transfer',
        'value_transferred',
        'movement_out_id',
        'movement_in_id',
        'weight_transferred_kg',
    ];

    protected $casts = [
        'qty_transferred' => 'decimal:2',
        'cump_at_transfer' => 'decimal:2',
        'value_transferred' => 'decimal:2',
        'weight_transferred_kg' => 'decimal:3',
    ];

    public function bonTransfert(): BelongsTo
    {
        return $this->belongsTo(BonTransfert::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function roll(): BelongsTo
    {
        return $this->belongsTo(Roll::class);
    }

    public function movementOut(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class, 'movement_out_id');
    }

    public function movementIn(): BelongsTo
    {
        return $this->belongsTo(StockMovement::class, 'movement_in_id');
    }

    // Scopes
    public function scopeRolls($query)
    {
        return $query->where('item_type', 'roll');
    }

    public function scopeProducts($query)
    {
        return $query->where('item_type', 'product');
    }
}
