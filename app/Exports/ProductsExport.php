<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    protected $exportFields;
    protected $headings;

    public function __construct()
    {
        // Get all columns from products table, exclude id and timestamps
        $this->exportFields = collect(Schema::getColumnListing('products'))->reject(function ($field) {
            return in_array($field, ['id', 'created_at', 'updated_at']);
        })->toArray();

        // Define headings with Indonesian translations
        $this->headings = [
            'product_code' => 'Kode Produk',
            'barcode' => 'Barcode',
            'name' => 'Nama Produk',
            'description' => 'Deskripsi',
            'image' => 'Gambar',
            'purchase_price' => 'Harga Beli',
            'selling_price' => 'Harga Jual',
            'member_price' => 'Harga Member',
            'vip_price' => 'Harga VIP',
            'wholesale_price' => 'Harga Grosir',
            'tax_rate' => 'Tarif Pajak',
            'tax_type' => 'Tipe Pajak',
            'min_wholesale_qty' => 'Min Qty Grosir',
            'stock' => 'Stok',
            'category_id' => 'ID Kategori',
            'division_id' => 'ID Divisi',
            'rack_id' => 'ID Rak',
            'supplier_id' => 'ID Supplier',
            'unit' => 'Satuan',
            'weight' => 'Berat',
            'min_stock_alert' => 'Min Stok Alert',
            'max_stock_alert' => 'Max Stok Alert',
            'reorder' => 'Reorder',
        ];
    }

    public function collection()
    {
        // Load relationships for foreign keys
        $products = Product::with(['category', 'division', 'rack', 'supplier'])
            ->select($this->exportFields)
            ->get();

        // Transform data to include relationship names instead of IDs
        return $products->map(function ($product) {
            $data = $product->toArray();

            // Replace foreign key IDs with names
            $data['category_id'] = $product->category?->name ?? '';
            $data['division_id'] = $product->division?->name ?? '';
            $data['rack_id'] = $product->rack?->name ?? '';
            $data['supplier_id'] = $product->supplier?->name ?? '';

            return collect($data)->only($this->exportFields);
        });
    }

    public function headings(): array
    {
        return collect($this->headings)->only($this->exportFields)->values()->toArray();
    }
}
