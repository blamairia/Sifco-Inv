<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RollSpecification extends Model
{
    protected $fillable = [
        'product_id',
        'paper_roll_type_id',
        'supplier_id',
        'purchase_price',
        'description',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function paperRollType()
    {
        return $this->belongsTo(PaperRollType::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function receiptItems()
    {
        return $this->hasMany(ReceiptItem::class);
    }

    public function rolls()
    {
        return $this->hasMany(Roll::class);
    }
}
