<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RackShelf extends Model
{
    use HasFactory;

    protected $fillable = ['rack_id', 'shelf_no', 'name'];

    public function rack()
    {
        return $this->belongsTo(Rack::class);
    }

    public function shelfProducts()
    {
        return $this->hasMany(RackShelfProduct::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'rack_shelf_products')
            ->withTimestamps()
            ->withPivot(['position']);
    }
}
