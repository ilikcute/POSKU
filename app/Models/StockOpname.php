<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'docno',
        'created_by',
        'status',
        'notes',
        'adjusted_at',
    ];

    protected $casts = [
        'adjusted_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
