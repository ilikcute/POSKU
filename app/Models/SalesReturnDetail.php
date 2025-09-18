<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesReturnDetail extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sales_return_id',
        'product_id',
        'quantity',
        'price_at_return',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_at_return' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($detail) {
            $detail->subtotal = $detail->price_at_return * $detail->quantity;
        });
    }

    // Relationships
    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}