<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_code',
        'barcode',
        'name',
        'description',
        'purchase_price',
        'selling_price',
        'member_price',
        'vip_price',
        'wholesale_price',
        'min_wholesale_qty',
        'category_id',
        'division_id',
        'rack_id',
        'supplier_id',
        'unit',
        'min_stock_alert',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'member_price' => 'decimal:2',
        'vip_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'min_wholesale_qty' => 'integer',
        'min_stock_alert' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function rack()
    {
        return $this->belongsTo(Rack::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_products')
            ->withPivot('special_price')
            ->withTimestamps();
    }

    public function getPrice($customerTypeCode = 'REGULAR', $quantity = 1)
    {
        switch (strtoupper($customerTypeCode)) {
            case 'VIP':
                return $this->vip_price ?? $this->selling_price;
            case 'MEMBER':
                return $this->member_price ?? $this->selling_price;
            case 'WHOLESALE':
                if ($quantity >= $this->min_wholesale_qty) {
                    return $this->wholesale_price ?? $this->selling_price;
                }

                return $this->selling_price;
            default:
                return $this->selling_price;
        }
    }

    public function getPriceWithPromotion(?Customer $customer = null, $quantity = 1)
    {
        $customerTypeCode = $customer?->customerType?->code ?? 'REGULAR';
        $basePrice = $this->getPrice($customerTypeCode, $quantity);

        $activePromotions = $this->promotions()
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->when($customer, function ($query) use ($customer) {
                $query->whereHas('customerTypes', function ($q) use ($customer) {
                    $q->where('customer_type_id', $customer->customer_type_id);
                })->orWhereDoesntHave('customerTypes');
            })
            ->orderBy('discount_value', 'desc')
            ->first();

        if (! $activePromotions) {
            return [
                'original_price' => $basePrice,
                'final_price' => $basePrice,
                'discount_amount' => 0,
                'promotion_name' => null,
            ];
        }

        $discountAmount = $activePromotions->discount_type === 'percentage'
            ? $basePrice * ($activePromotions->discount_value / 100)
            : $activePromotions->discount_value;

        if ($activePromotions->max_discount_amount && $discountAmount > $activePromotions->max_discount_amount) {
            $discountAmount = $activePromotions->max_discount_amount;
        }

        return [
            'original_price' => $basePrice,
            'final_price' => $basePrice - $discountAmount,
            'discount_amount' => $discountAmount,
            'promotion_name' => $activePromotions->name,
        ];
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
}
