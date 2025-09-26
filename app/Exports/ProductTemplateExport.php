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
        // Sample data untuk 3 baris contoh
        $samples = [
            // Sample 1
            [
                'PRD001', // product_code
                '1234567890123', // barcode
                'Produk Contoh 1', // name
                'Deskripsi produk contoh 1', // description
                'images/produk1.jpg', // image
                10000, // purchase_price
                15000, // selling_price
                14000, // member_price
                13000, // vip_price
                12000, // wholesale_price
                10, // min_wholesale_qty
                10, // tax_rate
                1, // category_id (contoh)
                1, // division_id
                1, // rack_id
                1, // supplier_id
                'PCS', // unit
                0.5, // weight
                5, // min_stock_alert
            ],
            // Sample 2
            [
                'PRD002',
                '1234567890124',
                'Produk Contoh 2',
                'Deskripsi produk contoh 2',
                'images/produk2.jpg',
                20000,
                30000,
                28000,
                26000,
                25000,
                20,
                12,
                2,
                1,
                2,
                2,
                'KG',
                1.0,
                10,
            ],
            // Sample 3
            [
                'PRD003',
                null,
                'Produk Contoh 3',
                null,
                null,
                5000,
                8000,
                null,
                null,
                null,
                null,
                0,
                null,
                null,
                null,
                null,
                'UNIT',
                null,
                0,
            ],
            // 2 baris kosong untuk tambahan
            array_fill(0, count($this->fields), ''),
            array_fill(0, count($this->fields), ''),
        ];

        return collect($samples);
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
