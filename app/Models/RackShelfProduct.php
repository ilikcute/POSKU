<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RackShelfProduct extends Model
{
    use HasFactory;

    protected $fillable = ['rack_shelf_id', 'product_id', 'position'];

    public function shelf()
    {
        return $this->belongsTo(RackShelf::class, 'rack_shelf_id');
    }
}
