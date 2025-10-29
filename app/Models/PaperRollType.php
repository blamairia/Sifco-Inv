<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaperRollType extends Model
{
    protected $fillable = [
        'type_code',
        'name',
        'grammage',
        'laise',
        'weight',
        'description',
    ];

    protected $casts = [
        'grammage' => 'integer',
        'laise' => 'integer',
        'weight' => 'decimal:2',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function rollSpecifications()
    {
        return $this->hasMany(RollSpecification::class);
    }
}
