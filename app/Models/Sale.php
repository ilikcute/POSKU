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
        'station_id',
        'customer_id',
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

    public static function generateInvoiceNumberForUpdate(): string
    {
        $date = now()->format('Ymd');
        $lastInvoice = self::whereDate('created_at', now())
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInvoice
            ? intval(substr($lastInvoice->invoice_number, -4)) + 1
            : 1;

        return 'INV-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * @deprecated Use customer() instead. Kept for backward compatibility with existing views.
     */
    public function member()
    {
        return $this->customer();
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
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

    // Methods
    public function updateStock()
    {
        foreach ($this->saleDetails as $detail) {
            $stock = Stock::where('product_id', $detail->product_id)
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
