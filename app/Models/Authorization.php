<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Authorization extends Model
{
    /** @use HasFactory<\Database\Factories\AuthorizationFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'password',
        'store_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
