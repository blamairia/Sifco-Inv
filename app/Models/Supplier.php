<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'email',
    ];

    public function rollSpecifications()
    {
        return $this->hasMany(RollSpecification::class);
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }
}
