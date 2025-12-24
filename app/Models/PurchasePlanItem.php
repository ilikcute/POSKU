<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_plan_id',
        'product_id',
        'current_stock',
        'max_stock',
        'min_order_qty',
        'needed_qty',
        'planned_qty',
    ];

    protected $casts = [
        'current_stock' => 'integer',
        'max_stock' => 'integer',
        'min_order_qty' => 'integer',
        'needed_qty' => 'integer',
        'planned_qty' => 'integer',
    ];

    public function plan()
    {
        return $this->belongsTo(PurchasePlan::class, 'purchase_plan_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
