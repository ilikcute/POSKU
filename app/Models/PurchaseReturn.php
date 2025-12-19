<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_id',
        'user_id',
        'store_id',
        'station_id',
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
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function purchaseReturnDetails()
    {
        return $this->hasMany(PurchaseReturnDetail::class);
    }

    // Methods
    public function updateStock()
    {
        foreach ($this->purchaseReturnDetails as $detail) {
            $stock = Stock::where('product_id', $detail->product_id)
                         ->where('store_id', $this->store_id)
                         ->first();
            
            if ($stock) {
                $stock->decrement('quantity', $detail->quantity);
            }
        }
    }

    public function generateReturnNumber()
    {
        $date = now()->format('Ymd');
        $lastReturn = self::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastReturn ? 
            intval(substr($lastReturn->return_number ?? 'RTN-00000000-0000', -4)) + 1 : 1;
        
        return 'RTN-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
