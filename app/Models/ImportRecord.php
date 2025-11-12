<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportRecord extends Model
{
    protected $table = 'import_records';

    protected $fillable = [
        'external_id',
        'model_type',
        'model_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
