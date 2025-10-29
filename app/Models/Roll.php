<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roll extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'roll_specification_id',
        'ean_13',
        'qty',
        'status',
        'batch_number',
        'received_date',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
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

    public function rollSpecification()
    {
        return $this->belongsTo(RollSpecification::class);
    }
}
