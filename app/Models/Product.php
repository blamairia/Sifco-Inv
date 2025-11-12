<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    public const TYPE_RAW_MATERIAL = 'raw_material';
    public const TYPE_SEMI_FINISHED = 'semi_finished';
    public const TYPE_FINISHED_GOOD = 'finished_good';

    public const FORM_TYPE_PAPIER_ROLL = 'papier_roll';
    public const FORM_TYPE_CONSUMABLE = 'consommable';
    public const FORM_TYPE_FINISHED = 'fini';

    protected $fillable = [
        'code',
        'name',
        'type',
        'description',
        'grammage',
        'laize',
        'flute',
        'type_papier',
        'extra_attributes',
        'unit_id',
        'product_type',
        'is_active',
        'is_roll',
        'min_stock',
        'safety_stock',
        'sheet_width_mm',
        'sheet_length_mm',
    ];

    protected $casts = [
        'grammage' => 'integer',
        'laize' => 'integer',
        'extra_attributes' => 'array',
        'is_active' => 'boolean',
        'is_roll' => 'boolean',
        'min_stock' => 'decimal:2',
        'safety_stock' => 'decimal:2',
        'sheet_width_mm' => 'decimal:2',
        'sheet_length_mm' => 'decimal:2',
        'product_type' => 'string',
        'type' => 'string',
    ];

    // Many-to-Many with categories
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function rolls()
    {
        return $this->hasMany(Roll::class);
    }

    public function stockQuantities()
    {
        return $this->hasMany(StockQuantity::class);
    }

    public function primaryCategory(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_category')
            ->withPivot('is_primary')
            ->wherePivot('is_primary', true);
    }

    // Helper to get primary category
    public function category()
    {
        return $this->primaryCategory()->first();
    }

    // Accessor for primary category attribute
    public function getCategoryAttribute()
    {
        if ($this->relationLoaded('categories')) {
            return $this->categories->firstWhere(function ($category) {
                return (bool) ($category->pivot->is_primary ?? false);
            });
        }

        return $this->category();
    }

    public function scopeRolls(Builder $builder): Builder
    {
        return $builder->where('is_roll', true);
    }

    public function scopeSheets(Builder $builder): Builder
    {
        return $builder->where('is_roll', false)
            ->where(function (Builder $subQuery): void {
                $subQuery->whereNotNull('sheet_width_mm')
                    ->orWhereNotNull('sheet_length_mm');
            });
    }

    public function scopeOfProductType(Builder $builder, string $productType): Builder
    {
        return $builder->where('product_type', $productType);
    }

    public function isSheet(): bool
    {
        return ! $this->is_roll && (
            ! is_null($this->sheet_width_mm) ||
            ! is_null($this->sheet_length_mm)
        );
    }

    public function isRoll(): bool
    {
        return (bool) $this->is_roll;
    }

    public function isFinishedGood(): bool
    {
        return $this->product_type === self::TYPE_FINISHED_GOOD;
    }

    public static function productTypes(): array
    {
        return [
            self::TYPE_RAW_MATERIAL,
            self::TYPE_SEMI_FINISHED,
            self::TYPE_FINISHED_GOOD,
        ];
    }

    public static function productTypeOptions(): array
    {
        return [
            self::TYPE_RAW_MATERIAL => 'Matière première',
            self::TYPE_SEMI_FINISHED => 'Semi-fini',
            self::TYPE_FINISHED_GOOD => 'Produit fini',
        ];
    }

    public static function typeOptions(): array
    {
        return [
            self::FORM_TYPE_PAPIER_ROLL => 'Papier en Bobine',
            self::FORM_TYPE_CONSUMABLE => 'Consommable',
            self::FORM_TYPE_FINISHED => 'Produit Fini',
        ];
    }

    public static function labelForType(?string $type): string
    {
        return self::typeOptions()[$type] ?? ($type ?? '—');
    }

    public static function labelForProductType(?string $type): string
    {
        return self::productTypeOptions()[$type] ?? ($type ?? '—');
    }

}
