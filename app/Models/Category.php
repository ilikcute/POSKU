<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'division_id', 'code'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
