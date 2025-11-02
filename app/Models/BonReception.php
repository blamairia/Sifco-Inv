<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BonReception extends Model
{
    protected $table = 'bon_receptions';

    protected $fillable = [
        'bon_number',
        'supplier_id',
        'delivery_note_ref',
        'purchase_order_ref',
        'receipt_date',
        'status',
        'verified_by_id',
        'verified_at',
        'conformity_issues',
        'notes',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'verified_at' => 'datetime',
        'conformity_issues' => 'json',
    ];

    // Relationships
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    // Scopes
    public function scopeUnverified($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    // Helper methods
    public static function generateBonNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', now()->toDateString())->count() + 1;
        return 'BREC-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function hasConformityIssues(): bool
    {
        return !empty($this->conformity_issues);
    }
}
