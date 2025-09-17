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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code', 50)->unique();
            $table->string('barcode', 100)->unique()->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->decimal('member_price', 15, 2);
            $table->decimal('vip_price', 15, 2);
            $table->decimal('wholesale_price', 15, 2);
            $table->integer('min_wholesale_qty');
            $table->integer('stock')->default(0);
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('division_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('rack_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
            $table->string('unit', 50)->default('Pcs'); // Pcs, Kg, Box
            $table->integer('min_stock_alert')->default(5);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
