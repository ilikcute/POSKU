<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::create([
            'name' => 'PT. Sinar Jaya Abadi',
            'contact_person' => 'Bapak Budi',
            'phone' => '081234567890',
            'address' => 'Jl. Industri No. 123, Jakarta',
        ]);

        Supplier::factory(15)->create();
    }
}
