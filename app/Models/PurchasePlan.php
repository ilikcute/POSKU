<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'created_by',
        'doc_no',
        'plan_date',
        'total_items',
        'total_qty',
    ];

    protected $casts = [
        'plan_date' => 'date',
        'total_items' => 'integer',
        'total_qty' => 'integer',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchasePlanItem::class);
    }
}
