<?php

namespace Database\Seeders;

use App\Models\CustomerType;
use Illuminate\Database\Seeder;

class CustomerTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customerTypes = [
            [
                'name' => 'Regular Customer',
                'code' => 'REGULAR',
                'discount_percentage' => 0,
                'description' => 'Customer biasa tanpa diskon khusus',
                'is_active' => true,
            ],
            [
                'name' => 'Member',
                'code' => 'MEMBER',
                'discount_percentage' => 5,
                'description' => 'Member dengan diskon 5%',
                'is_active' => true,
            ],
            [
                'name' => 'VIP Customer',
                'code' => 'VIP',
                'discount_percentage' => 10,
                'description' => 'VIP customer dengan diskon 10%',
                'is_active' => true,
            ],
            [
                'name' => 'Wholesaler',
                'code' => 'WHOLESALE',
                'discount_percentage' => 15,
                'description' => 'Customer grosir dengan diskon 15%',
                'is_active' => true,
            ],
            [
                'name' => 'Corporate',
                'code' => 'CORPORATE',
                'discount_percentage' => 12,
                'description' => 'Corporate customer dengan diskon 12%',
                'is_active' => true,
            ],
        ];

        foreach ($customerTypes as $type) {
            CustomerType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }

        // Create additional random customer types
        CustomerType::factory(5)->create();
    }
}
