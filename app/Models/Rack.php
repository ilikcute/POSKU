<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rack extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'rack_code', 'rack_type', 'shelf_count', 'shelf_plan'];

    protected $casts = [
        'shelf_plan' => 'array',
    ];

    public function shelves()
    {
        return $this->hasMany(RackShelf::class);
    }
}
