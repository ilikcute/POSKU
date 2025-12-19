<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_id',
        'user_id',
        'movement_type',
        'quantity',
        'reason',
        'reference_type',
        'reference_id',
        'old_quantity',
        'new_quantity',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'old_quantity' => 'integer',
        'new_quantity' => 'integer',
    ];

    protected $appends = [
        'product',
        'store',
    ];

    // Relationships
    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProductAttribute()
    {
        return $this->stock?->product;
    }

    public function getStoreAttribute()
    {
        return $this->stock?->store;
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }
}
