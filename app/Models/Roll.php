<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roll extends Model
{
    protected $fillable = [
        'bon_entree_id',
        'product_id',
        'warehouse_id',
        'ean_13',
        'batch_number',
        'received_date',
        'received_from_movement_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receivedFromMovement()
    {
        return $this->belongsTo(StockMovement::class, 'received_from_movement_id');
    }

    public function bonEntree()
    {
        return $this->belongsTo(BonEntree::class);
    }
}
