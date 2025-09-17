<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sale_id',
        'user_id',
        'store_id',
        'total_amount',
        'final_amount',
        'notes',
        'return_date',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'return_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($return) {
            $return->return_date = now();
        });
    }

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function salesReturnDetails()
    {
        return $this->hasMany(SalesReturnDetail::class);
    }

    // Methods
    public function updateStock()
    {
        foreach ($this->salesReturnDetails as $detail) {
            $stock = Stock::firstOrCreate(
                [
                    'product_id' => $detail->product_id,
                    'store_id' => $this->store_id,
                ],
                ['quantity' => 0]
            );
            
            $stock->increment('quantity', $detail->quantity);
        }
    }

    public function generateReturnNumber()
    {
        $date = now()->format('Ymd');
        $lastReturn = self::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastReturn ? 
            intval(substr($lastReturn->return_number ?? 'SRT-00000000-0000', -4)) + 1 : 1;
        
        return 'SRT-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}