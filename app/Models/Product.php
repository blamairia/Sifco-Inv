<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'description',
        'grammage',
        'laize',
        'flute',
        'type_papier',
        'extra_attributes',
        'unit_id',
        'is_active',
        'is_roll',
        'min_stock',
        'safety_stock',
    ];

    protected $casts = [
        'grammage' => 'integer',
        'laize' => 'integer',
        'extra_attributes' => 'array',
        'is_active' => 'boolean',
        'is_roll' => 'boolean',
        'min_stock' => 'decimal:2',
        'safety_stock' => 'decimal:2',
    ];

    // Many-to-Many with categories
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_category')
            ->withPivot('is_primary');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    
    public function rolls()
    {
        return $this->hasMany(Roll::class);
    }
    
    public function stockQuantities()
    {
        return $this->hasMany(StockQuantity::class);
    }

}
