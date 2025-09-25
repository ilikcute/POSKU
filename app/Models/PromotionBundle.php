<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PromotionBundle extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_id',
        'buy_product_id',
        'buy_quantity',
        'get_product_id',
        'get_quantity',
        'get_price',
    ];

    protected $casts = [
        'buy_quantity' => 'integer',
        'get_quantity' => 'integer',
        'get_price' => 'decimal:2',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    public function buyProduct()
    {
        return $this->belongsTo(Product::class, 'buy_product_id');
    }

    public function getProduct()
    {
        return $this->belongsTo(Product::class, 'get_product_id');
    }
}
