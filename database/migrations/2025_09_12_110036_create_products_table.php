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
            $table->string('image')->nullable();
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('selling_price', 15);
            $table->decimal('final_price', 15);
            $table->decimal('member_price', 15);
            $table->decimal('vip_price', 15);
            $table->decimal('wholesale_price', 15);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->enum('tax_type', ['Y', 'N'])->default('Y');
            $table->integer('min_wholesale_qty');
            $table->integer('min_order_qty')->default(1);
            $table->integer('stock')->default(0);
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('division_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('rack_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
            $table->string('unit', 50)->default('Pcs');
            $table->decimal('weight', 8, 2)->nullable();
            $table->integer('min_stock_alert')->default(5);
            $table->integer('max_stock_alert')->default(10);
            $table->string('reorder')->nullable();
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
