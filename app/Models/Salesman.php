<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salesman extends Model
{
    /** @use HasFactory<\Database\Factories\SalesmanFactory> */
    use HasFactory;

    protected $fillable = ['salesman_code', 'name', 'phone', 'email'];
}
