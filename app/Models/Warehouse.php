<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $fillable = [
        'name',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function stockQuantities()
    {
        return $this->hasMany(StockQuantity::class);
    }

    public function rolls()
    {
        return $this->hasMany(Roll::class);
    }

    public function stockMovementsFrom()
    {
        return $this->hasMany(StockMovement::class, 'warehouse_from_id');
    }

    public function stockMovementsTo()
    {
        return $this->hasMany(StockMovement::class, 'warehouse_to_id');
    }
}
