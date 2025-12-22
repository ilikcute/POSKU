<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyClosing extends Model
{
    protected $fillable = [
        'store_id',
        'business_date',
        'finalized_by',
        'finalized_at',
        'total_sales',
        'total_sales_return',
        'total_purchase',
        'total_purchase_return',
        'total_discount',
        'total_tax',
        'cash_counted_total',
        'expected_cash_total',
        'variance_total',
        'sales_count',
        'station_count',
        'shift_count',
        'meta',
        'notes',
    ];

    protected $casts = [
        'business_date' => 'date',
        'finalized_at' => 'datetime',
        'total_sales' => 'decimal:2',
        'total_sales_return' => 'decimal:2',
        'total_purchase' => 'decimal:2',
        'total_purchase_return' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'cash_counted_total' => 'decimal:2',
        'expected_cash_total' => 'decimal:2',
        'variance_total' => 'decimal:2',
        'meta' => 'array',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    public function finalizedByUser()
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }
}
