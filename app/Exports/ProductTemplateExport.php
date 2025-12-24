<?php

namespace App\Exports;

use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateExport implements FromCollection, WithHeadings, WithStyles
{
    protected $fields;

    public function __construct()
    {
        // Get all columns from products table, exclude id and timestamps
        $this->fields = collect(Schema::getColumnListing('products'))->reject(function ($field) {
            return in_array($field, ['id', 'created_at', 'updated_at']);
        })->values()->toArray();
    }

    /**
     * Return sample data untuk template
     */
    public function collection()
    {
        $samples = [
            [
                'product_code' => 'PRD001',
                'barcode' => '1234567890123',
                'name' => 'Produk Contoh 1',
                'description' => 'Deskripsi produk contoh 1',
                'image' => 'images/produk1.jpg',
                'purchase_price' => 10000,
                'selling_price' => 15000,
                'final_price' => 16650,
                'member_price' => 14000,
                'vip_price' => 13000,
                'wholesale_price' => 12000,
                'tax_rate' => 11,
                'tax_type' => 'Y',
                'min_wholesale_qty' => 10,
                'min_order_qty' => 12,
                'stock' => 100,
                'category_id' => 1,
                'division_id' => 1,
                'rack_id' => 1,
                'supplier_id' => 1,
                'unit' => 'PCS',
                'weight' => 0.5,
                'min_stock_alert' => 5,
                'max_stock_alert' => 10,
                'reorder' => 'Reorder when low',
            ],
            [
                'product_code' => 'PRD002',
                'barcode' => '1234567890124',
                'name' => 'Produk Contoh 2',
                'description' => 'Deskripsi produk contoh 2',
                'image' => 'images/produk2.jpg',
                'purchase_price' => 20000,
                'selling_price' => 30000,
                'final_price' => 33300,
                'member_price' => 28000,
                'vip_price' => 26000,
                'wholesale_price' => 25000,
                'tax_rate' => 11,
                'tax_type' => 'Y',
                'min_wholesale_qty' => 20,
                'min_order_qty' => 6,
                'stock' => 50,
                'category_id' => 2,
                'division_id' => 1,
                'rack_id' => 2,
                'supplier_id' => 2,
                'unit' => 'KG',
                'weight' => 1.0,
                'min_stock_alert' => 10,
                'max_stock_alert' => 20,
                'reorder' => 'Reorder note',
            ],
            [
                'product_code' => 'PRD003',
                'barcode' => null,
                'name' => 'Produk Contoh 3',
                'description' => null,
                'image' => null,
                'purchase_price' => 5000,
                'selling_price' => 8000,
                'final_price' => 8880,
                'member_price' => null,
                'vip_price' => null,
                'wholesale_price' => null,
                'tax_rate' => 11,
                'tax_type' => 'Y',
                'min_wholesale_qty' => 5,
                'min_order_qty' => 1,
                'stock' => 0,
                'category_id' => null,
                'division_id' => null,
                'rack_id' => null,
                'supplier_id' => null,
                'unit' => 'UNIT',
                'weight' => null,
                'min_stock_alert' => 0,
                'max_stock_alert' => 5,
                'reorder' => null,
            ],
        ];

        $orderedSamples = collect($samples)->map(function ($row) {
            return collect($this->fields)->map(function ($field) use ($row) {
                return $row[$field] ?? '';
            })->toArray();
        });

        $orderedSamples->push(array_fill(0, count($this->fields), ''));
        $orderedSamples->push(array_fill(0, count($this->fields), ''));

        return $orderedSamples;
    }

    /**
     * Header kolom berdasarkan fillable fields
     */
    public function headings(): array
    {
        return $this->fields;
    }

    /**
     * Style untuk worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk header
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFCCCCCC'],
                ],
            ],
        ];
    }
}
