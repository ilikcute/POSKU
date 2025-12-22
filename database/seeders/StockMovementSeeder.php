<?php

namespace Database\Seeders;

use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stocks = Stock::all();
        $adminUser = User::first();

        DB::transaction(function () use ($stocks, $adminUser) {
            foreach ($stocks as $stock) {
                // Check if this stock already has movements
                if ($stock->stockMovements()->count() === 0) {
                    // Create an initial movement to match current quantity
                    if ($stock->quantity > 0) {
                        $stock->stockMovements()->create([
                            'user_id' => $adminUser->id ?? 1,
                            'movement_type' => 'in',
                            'quantity' => $stock->quantity,
                            'reason' => 'Initial Stock Seeding',
                            'reference_type' => 'seeder',
                            'reference_id' => null,
                            'old_quantity' => 0,
                            'new_quantity' => $stock->quantity,
                            'created_at' => now()->subDays(rand(1, 30)), // Random date in past
                        ]);
                    }
                } else {
                    // If stock exists, maybe add some more "Purchase" movements to be safe/fun
                    // This creates logical movements that INCREASE quantity
                    $addQty = rand(10, 50);
                    $stock->addStock(
                        $addQty, 
                        'Restock Barang (Seeder)', 
                        'purchase', 
                        null, 
                        $adminUser->id ?? 1
                    );
                }
            }
        });
    }
}
