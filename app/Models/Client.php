<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Represents a B2B client that can receive goods via Bon de Sortie.
 */
class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'email',
        'phone',
        'mobile',
        'tax_number',
        'address_line1',
        'address_line2',
        'city',
        'country',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function bonSorties(): MorphMany
    {
        return $this->morphMany(BonSortie::class, 'destinationable');
    }
}
