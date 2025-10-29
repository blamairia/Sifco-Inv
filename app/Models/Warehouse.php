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
}
