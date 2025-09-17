<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Mencocokkan header di Excel dengan kolom di database
        return new Product([
            'product_code' => $row['product_code'],
            'name' => $row['name'],
            'description' => $row['description'],
            'purchase_price' => $row['purchase_price'],
            'selling_price' => $row['selling_price'],
        ]);
    }
}
