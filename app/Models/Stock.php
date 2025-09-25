<?php

namespace App\Models;

use App\Models\Store;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Stock extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'store_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereHas('product', function ($q) {
            $q->whereRaw('stocks.quantity <= products.min_stock_alert');
        });
    }

    public function scopeByStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // Methods
    public function isLowStock()
    {
        return $this->quantity <= $this->product->min_stock_alert;
    }

    public function adjustStock($newQuantity, $reason = 'Manual Adjustment', $userId = null)
    {
        $oldQuantity = $this->quantity;
        $difference = $newQuantity - $oldQuantity;
        
        $this->update(['quantity' => $newQuantity]);
        
        // Log stock movement
        $this->stockMovements()->create([
            'user_id' => $userId ?? auth()->id(),
            'movement_type' => $difference > 0 ? 'in' : 'out',
            'quantity' => abs($difference),
            'reason' => $reason,
            'reference_type' => 'adjustment',
            'reference_id' => null,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
        ]);

        return $this;
    }

    public function addStock($quantity, $reason = 'Purchase', $referenceType = null, $referenceId = null, $userId = null)
    {
        $oldQuantity = $this->quantity;
        $newQuantity = $oldQuantity + $quantity;
        
        $this->increment('quantity', $quantity);
        
        // Log stock movement
        $this->stockMovements()->create([
            'user_id' => $userId ?? auth()->id(),
            'movement_type' => 'in',
            'quantity' => $quantity,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
        ]);

        return $this;
    }

    public function reduceStock($quantity, $reason = 'Sale', $referenceType = null, $referenceId = null, $userId = null)
    {
        $oldQuantity = $this->quantity;
        $newQuantity = max(0, $oldQuantity - $quantity);
        
        $this->decrement('quantity', $quantity);
        
        // Log stock movement
        $this->stockMovements()->create([
            'user_id' => $userId ?? auth()->id(),
            'movement_type' => 'out',
            'quantity' => $quantity,
            'reason' => $reason,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $newQuantity,
        ]);

        return $this;
    }

    public function getStockValue()
    {
        return $this->quantity * $this->product->purchase_price;
    }

    public function getSellingValue()
    {
        return $this->quantity * $this->product->selling_price;
    }
}