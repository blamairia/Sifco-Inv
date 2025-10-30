<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BonEntree extends Model
{
    protected $table = 'bon_entrees';

    protected $fillable = [
        'bon_number',
        'bon_reception_id',
        'warehouse_id',
        'receipt_date',
        'status',
        'entered_by_id',
        'entered_at',
        'total_amount_ht',
        'frais_approche',
        'total_amount_ttc',
        'notes',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'entered_at' => 'datetime',
        'total_amount_ht' => 'decimal:2',
        'frais_approche' => 'decimal:2',
        'total_amount_ttc' => 'decimal:2',
    ];

    // Relationships
    public function bonReception(): BelongsTo
    {
        return $this->belongsTo(BonReception::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by_id');
    }

    public function bonEntreeItems(): HasMany
    {
        return $this->hasMany(BonEntreeItem::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    // Helper methods
    public static function generateBonNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', now()->toDateString())->count() + 1;
        return 'BENT-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function getTotalLinesCountAttribute(): int
    {
        return $this->bonEntreeItems()->count();
    }

    /**
     * Calculate frais d'approche allocation per item
     * Distributes frais_approche proportionally to each line item qty
     */
    public function allocateFraisApproche(): void
    {
        $totalQty = $this->bonEntreeItems()->sum('qty_entered');
        
        if ($totalQty == 0) {
            return;
        }

        $fraisPerUnit = $this->frais_approche / $totalQty;

        foreach ($this->bonEntreeItems as $item) {
            $item->update([
                'price_ttc' => $item->price_ht + $fraisPerUnit,
            ]);
        }
    }
}
