<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptItem extends Model
{
    protected $fillable = [
        'receipt_id',
        'roll_specification_id',
        'qty_received',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'qty_received' => 'integer',
        'total_price' => 'decimal:2',
    ];

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }

    public function rollSpecification()
    {
        return $this->belongsTo(RollSpecification::class);
    }

    /**
     * Get related rolls created from this receipt item
     */
    public function rolls()
    {
        return $this->hasMany(Roll::class, 'receipt_item_id');
    }
}
