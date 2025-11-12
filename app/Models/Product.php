<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    // Manufacturing Stage (Type Logique)
    public const TYPE_RAW_MATERIAL = 'raw_material';
    public const TYPE_SEMI_FINISHED = 'semi_finished';
    public const TYPE_FINISHED_GOOD = 'finished_good';

    // Physical Form (Forme Physique)
    public const FORM_ROLL = 'roll';
    public const FORM_SHEET = 'sheet';
    public const FORM_CONSUMABLE = 'consumable';
    public const FORM_OTHER = 'other';

    protected $fillable = [
        'code',
        'name',
        'product_type',
        'form_type',
        'description',
        'grammage',
        'laize',
        'flute',
        'type_papier',
        'extra_attributes',
        'unit_id',
        'is_active',
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
        'min_stock' => 'decimal:2',
        'safety_stock' => 'decimal:2',
        'sheet_width_mm' => 'decimal:2',
        'sheet_length_mm' => 'decimal:2',
        'product_type' => 'string',
        'form_type' => 'string',
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

    // Scopes for form types
    public function scopeRolls(Builder $query): Builder
    {
        return $query->where('form_type', self::FORM_ROLL);
    }

    public function scopeSheets(Builder $query): Builder
    {
        return $query->where('form_type', self::FORM_SHEET);
    }

    public function scopeConsumables(Builder $query): Builder
    {
        return $query->where('form_type', self::FORM_CONSUMABLE);
    }

    public function scopeOfFormType(Builder $query, string $formType): Builder
    {
        return $query->where('form_type', $formType);
    }

    public function scopeOfProductType(Builder $query, string $productType): Builder
    {
        return $query->where('product_type', $productType);
    }

    // Helper methods for form type checking
    public function isRoll(): bool
    {
        return $this->form_type === self::FORM_ROLL;
    }

    public function isSheet(): bool
    {
        return $this->form_type === self::FORM_SHEET;
    }

    public function isConsumable(): bool
    {
        return $this->form_type === self::FORM_CONSUMABLE;
    }

    // Helper methods for product type checking
    public function isRawMaterial(): bool
    {
        return $this->product_type === self::TYPE_RAW_MATERIAL;
    }

    public function isSemiFinished(): bool
    {
        return $this->product_type === self::TYPE_SEMI_FINISHED;
    }

    public function isFinishedGood(): bool
    {
        return $this->product_type === self::TYPE_FINISHED_GOOD;
    }

    // Static option arrays
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

    public static function formTypes(): array
    {
        return [
            self::FORM_ROLL,
            self::FORM_SHEET,
            self::FORM_CONSUMABLE,
            self::FORM_OTHER,
        ];
    }

    public static function formTypeOptions(): array
    {
        return [
            self::FORM_ROLL => 'Bobine (Roll)',
            self::FORM_SHEET => 'Feuille (Sheet)',
            self::FORM_CONSUMABLE => 'Consommable',
            self::FORM_OTHER => 'Autre',
        ];
    }

    public static function labelForFormType(?string $formType): string
    {
        return self::formTypeOptions()[$formType] ?? ($formType ?? '—');
    }

    public static function labelForProductType(?string $type): string
    {
        return self::productTypeOptions()[$type] ?? ($type ?? '—');
    }

}
