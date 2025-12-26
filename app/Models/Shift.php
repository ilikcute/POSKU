<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'shift_code',
        'name',
        'start_time',
        'end_time',
        'total_struk',
        'initial_cash',
        'final_cash',
        'total_sales',
        'total_discount',
        'total_tax',
        'total_purchase',
        'total_sales_return',
        'total_purchase_return',
        'total_stock_movements',
        'variance',
        'status',
        'device_id',
        'station_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
