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
        'supplier_id',
        'document_number',
        'warehouse_id',
        'expected_date',
        'received_date',
        'status',
        'total_amount_ht',
        'frais_approche',
        'total_amount_ttc',
        'notes',
    ];

    protected $casts = [
        'expected_date' => 'date',
        'received_date' => 'date',
        'total_amount_ht' => 'decimal:2',
        'frais_approche' => 'decimal:2',
        'total_amount_ttc' => 'decimal:2',
    ];

    // Relationships
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
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

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
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

    /**
     * Recalculate all totals from line items
     */
    public function recalculateTotals(): void
    {
        $this->total_amount_ht = $this->bonEntreeItems->sum(function ($item) {
            return $item->qty_entered * $item->price_ht;
        });
        
        $this->total_amount_ttc = $this->total_amount_ht + $this->frais_approche;
        
        $this->save();
    }
}
