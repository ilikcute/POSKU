<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sale_id',
        'product_id',
        'promotion_id',
        'quantity',
        'price_at_sale',
        'discount_per_item',
        'tax_type',
        'tax_rate',
        'tax_amount',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_at_sale' => 'decimal:2',
        'discount_per_item' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($detail) {
            $detail->subtotal = ($detail->price_at_sale - $detail->discount_per_item) * $detail->quantity;
        });
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
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

    public function getProfit()
    {
        $purchasePrice = $this->product->purchase_price ?? 0;
        $sellingPrice = $this->price_at_sale - $this->discount_per_item;
        return ($sellingPrice - $purchasePrice) * $this->quantity;
    }
}
