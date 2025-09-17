<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Pilih kolom yang ingin Anda ekspor
        return Product::select('product_code', 'name', 'description', 'purchase_price', 'selling_price')->get();
    }

    public function headings(): array
    {
        // Definisikan nama header kolom di file Excel
        return [
            'Kode Produk',
            'Nama Produk',
            'Deskripsi',
            'Harga Beli',
            'Harga Jual',
        ];
    }
}
