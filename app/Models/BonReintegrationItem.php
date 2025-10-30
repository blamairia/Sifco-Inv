<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonReintegrationItem extends Model
{
    protected $table = 'bon_reintegration_items';

    protected $fillable = [
        'bon_reintegration_id',
        'product_id',
        'qty_returned',
        'cump_at_return',
        'value_returned',
    ];

    protected $casts = [
        'qty_returned' => 'decimal:2',
        'cump_at_return' => 'decimal:2',
        'value_returned' => 'decimal:2',
    ];

    public function bonReintegration(): BelongsTo
    {
        return $this->belongsTo(BonReintegration::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
