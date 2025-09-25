<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promotion_bundles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buy_product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('buy_quantity')->comment('Quantity to buy');
            $table->foreignId('get_product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('get_quantity')->comment('Quantity to get free');
            $table->decimal('get_price', 15, 2)->default(0)->comment('Price for the free product, usually 0');
            $table->timestamps();

            $table->unique(['promotion_id', 'buy_product_id', 'get_product_id'], 'promo_bundles_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_bundles');
    }
};
