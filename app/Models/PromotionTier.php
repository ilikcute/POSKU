<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PromotionTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'promotion_id',
        'min_quantity',
        'discount_type',
        'discount_value',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_quantity' => 'integer',
    ];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }
}
