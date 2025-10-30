<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BonReintegration extends Model
{
    protected $table = 'bon_reintegrations';

    protected $fillable = [
        'bon_number',
        'bon_sortie_id',
        'warehouse_id',
        'return_date',
        'status',
        'verified_by_id',
        'verified_at',
        'physical_condition',
        'notes',
    ];

    protected $casts = [
        'return_date' => 'date',
        'verified_at' => 'datetime',
    ];

    public function bonSortie(): BelongsTo
    {
        return $this->belongsTo(BonSortie::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function bonReintegrationItems(): HasMany
    {
        return $this->hasMany(BonReintegrationItem::class);
    }

    public static function generateBonNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', now()->toDateString())->count() + 1;
        return 'BRIN-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
