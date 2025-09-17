<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Data kategori yang umum
        $categories = [
            ['name' => 'Makanan & Minuman'],
            ['name' => 'ATK (Alat Tulis Kantor)'],
            ['name' => 'Kebutuhan Rumah Tangga'],
            ['name' => 'Produk Kebersihan'],
            ['name' => 'Elektronik'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Tambah beberapa kategori random
        Category::factory(5)->create();
    }
}
