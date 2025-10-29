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

    public function stockLevels()
    {
        return $this->hasMany(StockLevel::class);
    }

    public function rolls()
    {
        return $this->hasMany(Roll::class);
    }
}
