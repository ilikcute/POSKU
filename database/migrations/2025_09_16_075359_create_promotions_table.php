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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('promotion_type', ['product_discount', 'buy_get', 'cashback', 'shipping_discount'])->default('product_discount');
            $table->enum('discount_type', ['percentage', 'fixed_amount'])->default('percentage');
            $table->decimal('discount_value', 15, 2);
            $table->decimal('min_purchase_amount', 15, 2)->nullable()->comment('Minimal pembelian');
            $table->decimal('max_discount_amount', 15, 2)->nullable()->comment('Maksimal potongan');
            $table->integer('min_quantity')->default(1)->comment('Minimal qty produk');
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->boolean('is_stackable')->default(false)->comment('Bisa digabung dengan promosi lain');
            $table->boolean('is_active')->default(true);
            $table->integer('usage_limit')->nullable()->comment('Batas penggunaan total');
            $table->integer('usage_count')->default(0)->comment('Jumlah sudah digunakan');
            $table->timestamps();

            $table->index(['start_date', 'end_date', 'is_active'], 'idx_promotion_dates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
