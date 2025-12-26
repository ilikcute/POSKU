<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_code',
        'member_code',
        'name',
        'email',
        'phone',
        'address',
        'customer_type_id',
        'is_active',
        'points',
        'photo',
        'status',
        'joined_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points' => 'integer',
        'joined_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function customerType()
    {
        return $this->belongsTo(CustomerType::class);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->where('is_active', true)
                ->orWhere('status', 'active');
        });
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/member-photos/' . $this->photo);
        }
        return null;
    }
}
