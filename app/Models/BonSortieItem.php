<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonSortieItem extends Model
{
    protected $table = 'bon_sortie_items';

    protected $fillable = [
        'bon_sortie_id',
        'product_id',
        'qty_issued',
        'cump_at_issue',
        // value_issued is a generated column: qty_issued * cump_at_issue
    ];

    protected $casts = [
        'qty_issued' => 'decimal:2',
        'cump_at_issue' => 'decimal:2',
        'value_issued' => 'decimal:2', // Generated column, read-only
    ];

    public function bonSortie(): BelongsTo
    {
        return $this->belongsTo(BonSortie::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
