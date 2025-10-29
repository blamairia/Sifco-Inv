<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roll extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'ean_13',
        'qty',
        'status',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
