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
        'category_id',
        'subcategory_id',
        'unit_id',
        'paper_roll_type_id',
    ];

    protected $casts = [
        'gsm' => 'integer',
        'width' => 'integer',
        'min_stock' => 'decimal:2',
        'safety_stock' => 'decimal:2',
        'avg_cost' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function paperRollType()
    {
        return $this->belongsTo(PaperRollType::class);
    }

    public function rollSpecifications()
    {
        return $this->hasMany(RollSpecification::class);
    }

    public function stockLevels()
    {
        return $this->hasMany(StockLevel::class);
    }

    public function rolls()
    {
        return $this->hasMany(Roll::class);
    }
}
