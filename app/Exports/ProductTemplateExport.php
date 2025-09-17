<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductTemplateExport implements FromCollection, WithHeadings, WithStyles
{
    /**
     * Return empty collection atau contoh data
     */
    public function collection()
    {
        // Return empty collection untuk template kosong
        // atau bisa return contoh data
        return collect([
            [
                'PRD001',
                'Contoh Produk 1',
                'Deskripsi contoh produk 1',
                '10000',
                '15000',
            ],
            // Tambahkan baris kosong untuk template
            ['', '', '', '', ''],
        ]);
    }

    /**
     * Header kolom
     */
    public function headings(): array
    {
        return [
            'product_code',
            'name',
            'description',
            'purchase_price',
            'selling_price',
        ];
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
