<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Category;
use Illuminate\Support\Str;

class Product extends Model
{
    // Manufacturing Stage (Type Logique)
    public const TYPE_RAW_MATERIAL = 'raw_material';
    public const TYPE_SEMI_FINISHED = 'semi_finished';
    public const TYPE_FINISHED_GOOD = 'finished_good';
    // Non-fabrication logical types (non fabrication state)
    public const TYPE_CONSUMABLE = 'consumable';
    public const TYPE_EQUIPMENT = 'equipment';
    public const TYPE_OTHER = 'other';

    // Physical Form (Forme Physique)
    public const FORM_ROLL = 'roll';
    public const FORM_SHEET = 'sheet';
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

    /**
     * Product-type check for consumables (logical type)
     */
    public function isConsumableProduct(): bool
    {
        return $this->product_type === self::TYPE_CONSUMABLE;
    }

    /**
     * Product-type check for equipment (logical type)
     */
    public function isEquipmentProduct(): bool
    {
        return $this->product_type === self::TYPE_EQUIPMENT;
    }

    /**
     * Product-type check for other types
     */
    public function isOtherProduct(): bool
    {
        return $this->product_type === self::TYPE_OTHER;
    }

    // Static option arrays
    public static function productTypes(): array
    {
        return [
            self::TYPE_RAW_MATERIAL,
            self::TYPE_SEMI_FINISHED,
            self::TYPE_FINISHED_GOOD,
            self::TYPE_CONSUMABLE,
            self::TYPE_EQUIPMENT,
            self::TYPE_OTHER,
        ];
    }

    public static function productTypeOptions(): array
    {
        return [
            self::TYPE_RAW_MATERIAL => 'Matière première',
            self::TYPE_SEMI_FINISHED => 'Semi-fini',
            self::TYPE_FINISHED_GOOD => 'Produit fini',
            self::TYPE_CONSUMABLE => 'Consommable',
            self::TYPE_EQUIPMENT => 'Équipement',
            self::TYPE_OTHER => 'Autre',
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

    protected static function booted(): void
    {
        static::creating(function (self $product) {
            // If code is not provided, auto-generate a code string.
            if (empty($product->code)) {
                $product->code = self::generateCode(
                    $product->name ?? ('product-' . time()),
                    $product->type ?? null,
                    $product->form_type ?? null,
                    $product->grammage ?? null,
                    $product->type_papier ?? null,
                    $product->flute ?? null,
                    $product->laize ?? null
                );
            }
        });

        static::updating(function (self $product) {
            // If key attributes changed, and the current code matches the earlier auto-generated code, regenerate
            $tracked = ['name', 'type', 'form_type', 'grammage', 'laize', 'flute', 'type_papier', 'primary_category_id'];
            $dirty = false;
            foreach ($tracked as $t) {
                if ($product->isDirty($t)) {
                    $dirty = true;
                    break;
                }
            }

            if (! $dirty) {
                return;
            }

            // Build original attrs and compute original suggested code
            $originalName = $product->getOriginal('name') ?? $product->name;
            $originalType = $product->getOriginal('type') ?? $product->type;
            $originalForm = $product->getOriginal('form_type') ?? $product->form_type;
            $originalGrammage = $product->getOriginal('grammage') ?? $product->grammage;
            $originalLaize = $product->getOriginal('laize') ?? $product->laize;
            $originalFlute = $product->getOriginal('flute') ?? $product->flute;
            $originalPaper = $product->getOriginal('type_papier') ?? $product->type_papier;
            $originalPrimaryCatId = $product->getOriginal('primary_category_id') ?? null;
            $originalPrimaryCatName = null;
            if ($originalPrimaryCatId) {
                $originalPrimaryCatName = Category::find($originalPrimaryCatId)?->name;
            }

            $originalGenerated = self::generateCode(
                $originalName,
                $originalType,
                $originalForm,
                $originalGrammage,
                $originalPaper,
                $originalFlute,
                $originalLaize,
                $originalPrimaryCatName,
            );
            $originalCode = $product->getOriginal('code') ?? '';

            // If the code was manually changed in this update, don't override it.
            if ($product->isDirty('code')) {
                return;
            }

            // Only regenerate when the existing code was auto-generated previously.
            $wasAuto = self::isAutoGeneratedCode($originalCode) && ($originalGenerated === $originalCode);
            if (! $wasAuto) {
                return;
            }

            // Regenerate code on tracked attribute change when previous code is auto-generated
            {
                // Determine new primary category name if set
                $newPrimaryCatId = $product->primary_category_id ?? $product->getAttribute('primary_category_id') ?? null;
                $newPrimaryCatName = null;
                if ($newPrimaryCatId) {
                    $newPrimaryCatName = Category::find($newPrimaryCatId)?->name;
                }

                $product->code = self::generateCode(
                    $product->name,
                    $product->type ?? null,
                    $product->form_type ?? null,
                    $product->grammage ?? null,
                    $product->type_papier ?? null,
                    $product->flute ?? null,
                    $product->laize ?? null,
                    $newPrimaryCatName,
                );
            }
        });
    }

    /**
     * Generate a product code based on the name and type.
     * Format: PREFIX-ABBR-NNN (e.g. PROD-KR80-001 or CONS-FILM-006)
     *
     * - PREFIX: 'CONS' for consommables, otherwise 'PROD'
     * - ABBR: derived from name (initials and numeric tokens)
     * - NNN: sequence number for collisions
     */
    public static function generateCode(
        string $name,
        ?string $type = null,
        ?string $formType = null,
        ?int $grammage = null,
        ?string $paperType = null,
        ?string $flute = null,
        ?int $laize = null,
        ?string $primaryCategoryName = null
    ): string
    {
        $form = $formType ?? $type;

        $prefix = ($form === self::FORM_OTHER || $type === 'consommable') ? 'CONS' : 'PROD';

        // If primary category name provided, prefer category abbreviation
        if ($primaryCategoryName) {
            $normalizedCat = Str::ascii(mb_strtoupper($primaryCategoryName));
            $catWords = array_values(array_filter(array_map('trim', preg_split('/\s+/', $normalizedCat))));
            $catAbbr = '';
            foreach ($catWords as $cw) {
                $catAbbr .= substr($cw, 0, 1);
                if (strlen($catAbbr) >= 3) {
                    break;
                }
            }
            $catAbbr = substr($catAbbr . ($grammage ?? $laize ?? ''), 0, 5);
        }

        // Normalize name and extract numeric token
    $upper = mb_strtoupper($name ?? '');
        // Remove accents and non alnum/space
        $normalized = Str::ascii($upper);
        $normalized = preg_replace('/[^A-Z0-9 ]+/', ' ', $normalized);
        $words = array_values(array_filter(array_map('trim', preg_split('/\s+/', $normalized))));

    // Build abbreviation depending on form type
        $abbrLetters = '';
    foreach ($words as $w) {
            if (preg_match('/^[A-Z]+$/', $w)) {
                $abbrLetters .= substr($w, 0, 1);
            } elseif (preg_match('/^[0-9]+/', $w)) {
                // If the token is numeric, keep it as numeric token
                // We'll append it after letters when present
                break;
            }

            if (strlen($abbrLetters) >= 3) {
                break;
            }
        }

        // Prefer explicit grams/width if provided
        $num = $grammage ?? $laize;
        if (! $num) {
            preg_match('/(\d{1,4})/', $normalized, $m);
            $num = $m[1] ?? null;
        }

        $abbr = $abbrLetters;
        if ($num) {
            $abbr = substr(($abbr . $num), 0, 6);
        }
        $abbr = $abbr ?: strtoupper(Str::substr(Str::slug($name ?? 'prod'), 0, 5));

        // If we have a primary category abbreviation, prefer it for rolls.
        if (isset($catAbbr) && ($form === self::FORM_ROLL || $type === 'papier_roll')) {
            $abbr = $catAbbr;
        }

        // If roll, prefer paperType + grammage (e.g., KR80)
    if ($form === self::FORM_ROLL || $type === 'papier_roll' || $type === self::FORM_ROLL) {
            $paperCandidate = $paperType ?: ($words[0] ?? null);
            if ($paperCandidate) {
                $p = preg_replace('/[^A-Z0-9]+/', '', Str::ascii(mb_strtoupper($paperCandidate)));
                $paperAbbr = strtoupper(substr($p, 0, 2));
                $abbr = $paperAbbr . ($num ? $num : '');
            }
        }

        // If finished/good or sheet, try to include flute+ply like C3E or fallback
    if ($form === self::FORM_SHEET || $form === self::FORM_OTHER || $type === 'fini' || $type === self::FORM_SHEET) {
            $fl = $flute ?? null;
            // try to find ply number
            preg_match('/(\d)\s*plis|(\d)plis|\b(\d)\b/i', $name, $plyMatch);
            $ply = $plyMatch[1] ?? $plyMatch[2] ?? $plyMatch[3] ?? null;
            if ($fl && $ply) {
                $abbr = 'C' . $ply . strtoupper(substr($fl, 0, 1));
            }
        }

        $base = $prefix . '-' . $abbr;

        // Determine next sequence
        $like = $base . '-%';
        $codes = self::where('code', 'like', $like)->pluck('code')->toArray();

        $max = 0;
        foreach ($codes as $c) {
            if (preg_match('/-(\d{3})$/', $c, $m)) {
                $n = (int) $m[1];
                $max = max($max, $n);
            }
        }

        $next = str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);

        return $base . '-' . $next;
    }

    /**
     * Determine if a code string looks like an auto-generated code.
     */
    public static function isAutoGeneratedCode(?string $code): bool
    {
        if (empty($code)) {
            return false;
        }

        return (bool) preg_match('/^(PROD|CONS)-[A-Z0-9\-]{1,10}-\d{3}$/', $code);
    }

}
