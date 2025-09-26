<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImportHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_data',
        'imported_at',
    ];

    protected $casts = [
        'product_data' => 'array',
        'imported_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
