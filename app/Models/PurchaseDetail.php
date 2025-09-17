<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'price_at_sale',
        'discount_per_item',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_at_sale' => 'decimal:2',
        'discount_per_item' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($detail) {
            $detail->subtotal = ($detail->price_at_sale - $detail->discount_per_item) * $detail->quantity;
        });
    }

    // Relationships
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Methods
    public function getUnitPriceAfterDiscount()
    {
        return $this->price_at_sale - $this->discount_per_item;
    }

    public function getTotalDiscount()
    {
        return $this->discount_per_item * $this->quantity;
    }
}