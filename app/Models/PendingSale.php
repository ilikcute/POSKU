<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingSale extends Model
{
    protected $fillable = [
        'station_id',
        'user_id',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
