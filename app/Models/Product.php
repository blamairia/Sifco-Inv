<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public const TYPE_RAW_MATERIAL = 'raw_material';
    public const TYPE_SEMI_FINISHED = 'semi_finished';
    public const TYPE_FINISHED_GOOD = 'finished_good';

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
        'product_type',
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
    
    // Helper to get primary category
    public function category()
    {
        return $this->belongsToMany(Category::class, 'product_category')
            ->withPivot('is_primary')
            ->wherePivot('is_primary', true)
            ->first();
    }
    
    // Accessor for primary category
    public function getCategoryAttribute()
    {
        return $this->categories()->wherePivot('is_primary', true)->first();
    }

    public static function productTypes(): array
    {
        return [
            self::TYPE_RAW_MATERIAL,
            self::TYPE_SEMI_FINISHED,
            self::TYPE_FINISHED_GOOD,
        ];
    }

}
