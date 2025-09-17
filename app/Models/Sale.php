<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'store_id',
        'member_id',
        'total_amount',
        'discount',
        'tax',
        'final_amount',
        'payment_method',
        'amount_paid',
        'change_due',
        'transaction_date',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_due' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($sale) {
            if (!$sale->invoice_number) {
                $sale->invoice_number = self::generateInvoiceNumber();
            }
        });
    }

    public static function generateInvoiceNumber()
    {
        $date = now()->format('Ymd');
        $lastInvoice = self::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastInvoice ? 
            intval(substr($lastInvoice->invoice_number, -4)) + 1 : 1;
        
        return 'INV-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function member()
    {
        return $this->belongsTo(Customer::class, 'member_id');
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class);
    }

    // Scopes
    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', now());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('transaction_date', now()->month)
                    ->whereYear('transaction_date', now()->year);
    }

    public function scopeByStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    // Methods
    public function updateStock()
    {
        foreach ($this->saleDetails as $detail) {
            $stock = Stock::where('product_id', $detail->product_id)
                         ->where('store_id', $this->store_id)
                         ->first();
            
            if ($stock) {
                $stock->decrement('quantity', $detail->quantity);
            }
        }
    }

    public function canBeReturned()
    {
        $returnedAmount = $this->salesReturns()->sum('final_amount');
        return $this->final_amount > $returnedAmount;
    }

    public function getReturnableAmount()
    {
        $returnedAmount = $this->salesReturns()->sum('final_amount');
        return $this->final_amount - $returnedAmount;
    }

    public function getTotalProfit()
    {
        $totalProfit = 0;
        foreach ($this->saleDetails as $detail) {
            $purchasePrice = $detail->product->purchase_price ?? 0;
            $sellingPrice = $detail->price_at_sale - $detail->discount_per_item;
            $profit = ($sellingPrice - $purchasePrice) * $detail->quantity;
            $totalProfit += $profit;
        }
        return $totalProfit;
    }
}