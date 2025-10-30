<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonTransfertItem extends Model
{
    protected $table = 'bon_transfert_items';

    protected $fillable = [
        'bon_transfert_id',
        'product_id',
        'qty_transferred',
        'cump_at_transfer',
        'value_transferred',
    ];

    protected $casts = [
        'qty_transferred' => 'decimal:2',
        'cump_at_transfer' => 'decimal:2',
        'value_transferred' => 'decimal:2',
    ];

    public function bonTransfert(): BelongsTo
    {
        return $this->belongsTo(BonTransfert::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
