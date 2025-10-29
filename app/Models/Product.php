<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'type',
        'gsm',
        'flute',
        'width',
        'min_stock',
        'safety_stock',
        'avg_cost',
    ];

    protected $casts = [
        'gsm' => 'integer',
        'width' => 'integer',
        'min_stock' => 'decimal:2',
        'safety_stock' => 'decimal:2',
        'avg_cost' => 'decimal:2',
    ];
}
