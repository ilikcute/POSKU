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
        'image',
        'purchase_price',
        'selling_price',
        'final_price',
        'member_price',
        'vip_price',
        'wholesale_price',
        'min_wholesale_qty',
        'min_order_qty',
        'tax_type',
        'tax_rate',
        'stock',
        'category_id',
        'division_id',
        'rack_id',
        'supplier_id',
        'unit',
        'weight',
        'min_stock_alert',
        'max_stock_alert',
        'reorder',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'member_price' => 'decimal:2',
        'vip_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_type' => 'string',
        'weight' => 'decimal:2',
        'min_wholesale_qty' => 'integer',
        'min_order_qty' => 'integer',
        'stock' => 'integer',
        'min_stock_alert' => 'integer',
        'max_stock_alert' => 'integer',
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
            ->with(['tiers', 'bundles'])
            ->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->when($customer, function ($query) use ($customer) {
                $query->whereHas('customerTypes', function ($q) use ($customer) {
                    $q->where('customer_type_id', $customer->customer_type_id);
                })->orWhereDoesntHave('customerTypes');
            })
            ->orderBy('discount_value', 'desc')
            ->get();

        if ($activePromotions->isEmpty()) {
            return [
                'original_price' => $basePrice,
                'final_price' => $basePrice,
                'discount_amount' => 0,
                'promotion_name' => null,
                'bundled_products' => [],
            ];
        }

        $bestFinalPrice = $basePrice;
        $bestDiscountAmount = 0;
        $bestPromotionName = null;
        $bestPromotionId = null;
        $bestBundledProducts = [];

        foreach ($activePromotions as $promotion) {
            $candidateFinalPrice = $basePrice;
            $candidateDiscountAmount = 0;
            $candidateBundledProducts = [];

            if ($promotion->promotion_type === 'tiered_pricing') {
                // Find applicable tier
                $applicableTier = $promotion->tiers()
                    ->where('min_quantity', '<=', $quantity)
                    ->orderBy('min_quantity', 'desc')
                    ->first();

                if ($applicableTier) {
                    $tierDiscount = $applicableTier->discount_type === 'percentage'
                        ? $basePrice * ($applicableTier->discount_value / 100)
                        : $applicableTier->discount_value;

                    if ($promotion->max_discount_amount && $tierDiscount > $promotion->max_discount_amount) {
                        $tierDiscount = $promotion->max_discount_amount;
                    }

                    $candidateFinalPrice = round($basePrice - $tierDiscount, 0);
                    $candidateDiscountAmount = round($tierDiscount, 0);
                }
            } elseif ($promotion->promotion_type === 'bundling') {
                // Handle bundling - add free products
                foreach ($promotion->bundles as $bundle) {
                    if ($bundle->buy_product_id == $this->id && $quantity >= $bundle->buy_quantity) {
                        $freeQty = floor($quantity / $bundle->buy_quantity) * $bundle->get_quantity;
                        $candidateBundledProducts[] = [
                            'product_id' => $bundle->get_product_id,
                            'quantity' => $freeQty,
                            'price' => $bundle->get_price,
                            'product_name' => $bundle->getProduct->name,
                        ];
                    }
                }
                // For bundling, final price remains base, discount 0
                $candidateFinalPrice = $basePrice;
                $candidateDiscountAmount = 0;
            } else {
                // Original logic for other types
                $promoDiscount = $promotion->discount_type === 'percentage'
                    ? $basePrice * ($promotion->discount_value / 100)
                    : $promotion->discount_value;

                if ($promotion->max_discount_amount && $promoDiscount > $promotion->max_discount_amount) {
                    $promoDiscount = $promotion->max_discount_amount;
                }

                $candidateFinalPrice = round($basePrice - $promoDiscount, 0);
                $candidateDiscountAmount = round($promoDiscount, 0);
            }

            // Choose the best (lowest price)
            if ($candidateFinalPrice < $bestFinalPrice) {
                $bestFinalPrice = $candidateFinalPrice;
                $bestDiscountAmount = $candidateDiscountAmount;
                $bestPromotionName = $promotion->name;
                $bestPromotionId = $promotion->id;
                $bestBundledProducts = $candidateBundledProducts;
            }
        }

        $finalPrice = $bestFinalPrice;
        $discountAmount = $bestDiscountAmount;
        $promotionName = $bestPromotionName;
        $bundledProducts = $bestBundledProducts;

        return [
            'original_price' => $basePrice,
            'final_price' => $finalPrice,
            'discount_amount' => $discountAmount,
            'promotion_name' => $promotionName,
            'promotion_id' => $bestPromotionId,
            'bundled_products' => $bundledProducts,
        ];
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Sync the product's stock field with the sum of quantities from stocks table.
     * If no stock records exist, create a default one with quantity 0 and log as initial.
     */
    public function syncStock()
    {
        // Check if any stock records exist for this product
        $existingStocks = $this->stocks();

        if ($existingStocks->count() == 0) {
            // Create a default stock record for store_id=1 (assuming default store exists)
            $stock = Stock::create([
                'product_id' => $this->id,
                'store_id' => 1, // Default store
                'quantity' => 0,
            ]);

            // Log initial stock movement
            $userId = auth()->id() ?? \App\Models\User::first()->id ?? 1; // Default to first user or 1
            $stock->stockMovements()->create([
                'user_id' => $userId,
                'movement_type' => 'in', // Initial stock as 'in'
                'quantity' => 0,
                'reason' => 'Initial stock for new product',
                'reference_type' => 'initial',
                'reference_id' => null,
                'old_quantity' => 0,
                'new_quantity' => 0,
            ]);
        }

        // Sum all quantities from stocks
        $totalStock = $this->stocks()->sum('quantity');

        // Update the product's stock field
        $this->update(['stock' => $totalStock]);

        return $this;
    }
}
