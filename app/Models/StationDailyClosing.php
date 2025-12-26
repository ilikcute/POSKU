<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StationDailyClosing extends Model
{
    protected $fillable = [
        'station_id',
        'business_date',
        'closed_by',
        'closed_at',
        'cash_counted',
        'expected_cash',
        'variance',
        'cash_sales_total',
        'cash_sales_return_total',
        'cash_purchase_total',
        'cash_purchase_return_total',
        'total_sales',
        'total_sales_return',
        'total_purchase',
        'total_purchase_return',
        'total_discount',
        'total_tax',
        'sales_count',
        'shift_count',
        'shifts_initial_cash_total',
        'notes',
        'meta',
    ];

    protected $casts = [
        'business_date' => 'date',
        'closed_at' => 'datetime',
        'cash_counted' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'variance' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'total_sales_return' => 'decimal:2',
        'total_purchase' => 'decimal:2',
        'total_purchase_return' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'meta' => 'array',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
