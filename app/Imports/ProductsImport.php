<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class ProductsImport implements ToCollection, WithHeadingRow
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
     * @param Collection $rows
     * @return Collection|null
     */
    public function collection(Collection $rows)
    {
        $processedRows = $rows->map(function (array $row) {
            // Filter row to only include fillable fields
            $data = array_intersect_key($row, array_flip($this->fillableFields));

            // Convert empty strings to null for nullable fields
            foreach ($data as $key => $value) {
                if ($value === '' || $value === null) {
                    $data[$key] = null;
                } elseif (is_numeric($value) && in_array($key, ['purchase_price', 'selling_price', 'member_price', 'vip_price', 'wholesale_price', 'tax_rate', 'weight'])) {
                    $data[$key] = (float) $value;
                } elseif (is_numeric($value) && in_array($key, ['min_wholesale_qty', 'stock', 'min_stock_alert', 'max_stock_alert'])) {
                    $data[$key] = (int) $value;
                }
            }

            // Remove rows without required product_code or name
            if (empty($data['product_code']) || empty($data['name'])) {
                return null;
            }

            // Validate foreign keys and uniques
            $validationErrors = [];
            if (!empty($data['category_id']) && !DB::table('categories')->where('id', $data['category_id'])->exists()) {
                $validationErrors[] = "Invalid category_id";
            }
            if (!empty($data['division_id']) && !DB::table('divisions')->where('id', $data['division_id'])->exists()) {
                $validationErrors[] = "Invalid division_id";
            }
            if (!empty($data['rack_id']) && !DB::table('racks')->where('id', $data['rack_id'])->exists()) {
                $validationErrors[] = "Invalid rack_id";
            }
            if (!empty($data['supplier_id']) && !DB::table('suppliers')->where('id', $data['supplier_id'])->exists()) {
                $validationErrors[] = "Invalid supplier_id";
            }
            if (!empty($data['barcode'])) {
                $barcodeExists = DB::table('products')->where('barcode', $data['barcode'])
                    ->where('product_code', '!=', $data['product_code'])
                    ->exists();
                if ($barcodeExists) {
                    $validationErrors[] = "Duplicate barcode";
                }
            }

            if (!empty($validationErrors)) {
                // Skip invalid rows, but could log or collect errors
                return null;
            }

            return $data;
        })->filter(); // Remove null rows

        return $processedRows->values()->toArray();
    }
}
