<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    /** @use HasFactory<\Database\Factories\ShiftFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'shift_code',
        'name',
        'store_id',
        'start_time',
        'end_time',
        'total_struk',
        'initial_cash',
        'final_cash',
        'total_sales',
        'total_discount',
        'total_tax',
        'total_purchase',
        'variance',
        'status',
    ];

    public $timestamps = false;
}
