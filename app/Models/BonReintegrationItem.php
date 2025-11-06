<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonReintegrationItem extends Model
{
    protected $table = 'bon_reintegration_items';

    protected $fillable = [
        'bon_reintegration_id',
        'item_type',
        'product_id',
        'roll_id',
        'qty_returned',
        'previous_weight_kg',
        'returned_weight_kg',
        'weight_delta_kg',
        'cump_at_return',
        'value_returned',
    ];

    protected $casts = [
        'qty_returned' => 'decimal:2',
        'cump_at_return' => 'decimal:2',
        'value_returned' => 'decimal:2',
        'previous_weight_kg' => 'decimal:3',
        'returned_weight_kg' => 'decimal:3',
        'weight_delta_kg' => 'decimal:3',
    ];

    public function bonReintegration(): BelongsTo
    {
        return $this->belongsTo(BonReintegration::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function roll(): BelongsTo
    {
        return $this->belongsTo(Roll::class);
    }
}
