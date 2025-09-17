<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    /** @use HasFactory<\Database\Factories\ShiftFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_id',
        'start_time',
        'end_time',
        'initial_cash',
        'final_cash',
        'total_sales',
        'variance',
        'status',
    ];

    public $timestamps = false;
}
