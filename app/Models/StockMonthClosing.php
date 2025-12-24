<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMonthClosing extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'product_id',
        'year',
        'month',
        'opening_qty',
        'opening_value_dpp',
        'opening_value_final',
        'in_qty_purchase',
        'in_qty_sales_return',
        'out_qty_sale',
        'out_qty_purchase_return',
        'adjustment_qty',
        'closing_qty',
        'closing_value_dpp',
        'closing_value_final',
        'closed_by',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'opening_qty' => 'integer',
        'in_qty_purchase' => 'integer',
        'in_qty_sales_return' => 'integer',
        'out_qty_sale' => 'integer',
        'out_qty_purchase_return' => 'integer',
        'adjustment_qty' => 'integer',
        'closing_qty' => 'integer',
        'opening_value_dpp' => 'decimal:2',
        'opening_value_final' => 'decimal:2',
        'closing_value_dpp' => 'decimal:2',
        'closing_value_final' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
