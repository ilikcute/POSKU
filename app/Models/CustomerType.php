<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'discount_percentage',
        'description',
        'is_active',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_customers');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
