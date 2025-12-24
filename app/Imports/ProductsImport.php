<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class ProductsImport implements ToCollection, WithHeadingRow
{
    protected $fillableFields;
    protected $processedData = [];

    public function __construct()
    {
        // Get all columns from products table, exclude id and timestamps
        $this->fillableFields = collect(Schema::getColumnListing('products'))->reject(function ($field) {
            return in_array($field, ['id', 'created_at', 'updated_at']);
        })->toArray();
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        Log::info('Processing ' . $rows->count() . ' rows from Excel');

        $this->processedData = $rows->map(function ($row, $index) {
            // Convert Collection to array if needed
            $rowData = $row instanceof Collection ? $row->toArray() : (array) $row;

            Log::info('Processing row ' . ($index + 1), $rowData);

            // Filter row to only include fillable fields
            $data = array_intersect_key($rowData, array_flip($this->fillableFields));

            // Convert empty strings to null for nullable fields
            foreach ($data as $key => $value) {
                if ($value === '' || $value === null) {
                    $data[$key] = null;
                } elseif (is_numeric($value) && in_array($key, ['purchase_price', 'selling_price', 'final_price', 'member_price', 'vip_price', 'wholesale_price', 'tax_rate', 'weight'])) {
                    $data[$key] = (float) $value;
                } elseif (is_numeric($value) && in_array($key, ['min_wholesale_qty', 'min_order_qty', 'stock', 'min_stock_alert', 'max_stock_alert'])) {
                    $data[$key] = (int) $value;
                }
            }

            $taxType = $data['tax_type'] ?? 'N';
            $taxRate = $data['tax_rate'] ?? 0;
            if (isset($data['selling_price']) && empty($data['final_price'])) {
                $taxAmount = $taxType === 'Y' ? ($data['selling_price'] * $taxRate / 100) : 0;
                $data['final_price'] = $data['selling_price'] + $taxAmount;
            }

            // Remove rows without required product_code or name
            if (empty($data['product_code']) || empty($data['name'])) {
                Log::warning('Skipping row due to missing product_code or name', $data);
                return null;
            }

            // Validate foreign keys and uniques
            $validationErrors = [];
            if (!empty($data['category_id']) && !DB::table('categories')->where('id', $data['category_id'])->exists()) {
                $validationErrors[] = "Invalid category_id: " . $data['category_id'];
            }
            if (!empty($data['division_id']) && !DB::table('divisions')->where('id', $data['division_id'])->exists()) {
                $validationErrors[] = "Invalid division_id: " . $data['division_id'];
            }
            if (!empty($data['rack_id']) && !DB::table('racks')->where('id', $data['rack_id'])->exists()) {
                $validationErrors[] = "Invalid rack_id: " . $data['rack_id'];
            }
            if (!empty($data['supplier_id']) && !DB::table('suppliers')->where('id', $data['supplier_id'])->exists()) {
                $validationErrors[] = "Invalid supplier_id: " . $data['supplier_id'];
            }
            if (!empty($data['barcode'])) {
                $barcodeExists = DB::table('products')->where('barcode', $data['barcode'])
                    ->where('product_code', '!=', $data['product_code'])
                    ->exists();
                if ($barcodeExists) {
                    $validationErrors[] = "Duplicate barcode: " . $data['barcode'];
                }
            }

            if (!empty($validationErrors)) {
                Log::warning('Skipping row due to validation errors', [
                    'product_code' => $data['product_code'] ?? 'N/A',
                    'errors' => $validationErrors
                ]);
                return null;
            }

            // Remove stock from data to prevent manual intervention
            unset($data['stock']);

            Log::info('Row processed successfully', ['product_code' => $data['product_code']]);
            return $data;
        })->filter()->values();

        Log::info('Total valid rows processed: ' . $this->processedData->count());
    }

    /**
     * Get processed data
     */
    public function getProcessedData()
    {
        return $this->processedData;
    }
}
