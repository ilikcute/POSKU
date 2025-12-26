<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerType;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customerTypes = CustomerType::all();

        // Create specific customers for each type
        foreach ($customerTypes as $type) {
            Customer::factory(3)->withType($type->id)->create();
        }

        // Create additional random customers with merged fields
        Customer::factory(20)->create();

        // Create some sample customers with specific data (with merged fields)
        $sampleCustomers = [
            [
                'customer_code' => 'CUST001',
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '081234567890',
                'address' => 'Jl. Sudirman No. 123, Jakarta',
                'customer_type_id' => CustomerType::where('code', 'REGULAR')->first()->id,
                'points' => 0,
                'photo' => null,
                'status' => 'active',
                'joined_at' => now(),
            ],
            [
                'customer_code' => 'CUST002',
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '081234567891',
                'address' => 'Jl. Thamrin No. 456, Jakarta',
                'customer_type_id' => CustomerType::where('code', 'MEMBER')->first()->id,
                'points' => 100,
                'photo' => null,
                'status' => 'active',
                'joined_at' => now(),
            ],
            [
                'customer_code' => 'CUST003',
                'name' => 'Bob Wilson',
                'email' => 'bob.wilson@example.com',
                'phone' => '081234567892',
                'address' => 'Jl. Gatot Subroto No. 789, Jakarta',
                'customer_type_id' => CustomerType::where('code', 'VIP')->first()->id,
                'points' => 0,
                'photo' => null,
                'status' => 'active',
                'joined_at' => now(),
            ],
            [
                'customer_code' => 'CUST004',
                'name' => 'PT. ABC Store',
                'email' => 'procurement@abcstore.com',
                'phone' => '081234567893',
                'address' => 'Jl. HR Rasuna Said No. 101, Jakarta',
                'customer_type_id' => CustomerType::where('code', 'WHOLESALE')->first()->id,
                'points' => 0,
                'photo' => null,
                'status' => 'active',
                'joined_at' => now(),
            ],
        ];

        foreach ($sampleCustomers as $customer) {
            Customer::updateOrCreate(
                ['customer_code' => $customer['customer_code']],
                $customer
            );
        }
    }
}
