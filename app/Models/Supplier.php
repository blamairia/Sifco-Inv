<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Supplier extends Model
{
    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'payment_terms',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function bonReceptions(): HasMany
    {
        return $this->hasMany(BonReception::class);
    }

    public function bonEntrees(): MorphMany
    {
        return $this->morphMany(BonEntree::class, 'sourceable');
    }
}
