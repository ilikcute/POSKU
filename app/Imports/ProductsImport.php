<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    protected $fillableFields;

    public function __construct()
    {
        // Get all columns from products table, exclude id and timestamps
        $this->fillableFields = collect(Schema::getColumnListing('products'))->reject(function ($field) {
            return in_array($field, ['id', 'created_at', 'updated_at']);
        })->toArray();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Filter row to only include fillable fields
        $data = array_intersect_key($row, array_flip($this->fillableFields));

        // Convert empty strings to null for nullable fields
        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        // Temporarily unguard to allow mass assignment of all fields
        Product::unguard();
        $product = new Product($data);
        Product::reguard();

        return $product;
    }
}
