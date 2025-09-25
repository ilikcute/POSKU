<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'name',
        'description',
        'promotion_type',
        'discount_type',
        'discount_value',
        'min_purchase_amount',
        'max_discount_amount',
        'min_quantity',
        'start_date',
        'end_date',
        'is_stackable',
        'is_active',
        'usage_limit',
        'usage_count',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_purchase_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_stackable' => 'boolean',
        'is_active' => 'boolean',
        'min_quantity' => 'integer',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'promotion_products')
            ->withPivot('special_price')
            ->withTimestamps();
    }

    public function customerTypes()
    {
        return $this->belongsToMany(CustomerType::class, 'promotion_customers');
    }

    public function tiers()
    {
        return $this->hasMany(PromotionTier::class);
    }

    public function bundles()
    {
        return $this->hasMany(PromotionBundle::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function isUsageLimitReached()
    {
        return $this->usage_limit && $this->usage_count >= $this->usage_limit;
    }

    public function incrementUsage()
    {
        $this->increment('usage_count');
    }
}
