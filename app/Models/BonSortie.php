<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BonSortie extends Model
{
    protected $table = 'bon_sorties';

    protected $fillable = [
        'bon_number',
        'warehouse_id',
        'issued_date',
        'destination',
        'destinationable_type',
        'destinationable_id',
        'status',
        'issued_by_id',
        'issued_at',
        'notes',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'issued_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_id');
    }

    public function bonSortieItems(): HasMany
    {
        return $this->hasMany(BonSortieItem::class);
    }

    public function destinationable(): MorphTo
    {
        return $this->morphTo();
    }

    public function productionLine(): ?ProductionLine
    {
        return $this->destinationable instanceof ProductionLine ? $this->destinationable : null;
    }

    public static function generateBonNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', now()->toDateString())->count() + 1;
        return 'BSRT-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
