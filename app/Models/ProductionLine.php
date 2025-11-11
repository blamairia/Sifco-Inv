<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProductionLine extends Model
{
    protected $fillable = [
        'name',
        'code',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function bonEntrees(): MorphMany
    {
        return $this->morphMany(BonEntree::class, 'sourceable');
    }

    public function bonSorties(): MorphMany
    {
        return $this->morphMany(BonSortie::class, 'destinationable');
    }
}
