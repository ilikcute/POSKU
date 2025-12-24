<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_month_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');

            $table->integer('opening_qty')->default(0);
            $table->decimal('opening_value_dpp', 15, 2)->default(0);
            $table->decimal('opening_value_final', 15, 2)->default(0);

            $table->integer('in_qty_purchase')->default(0);
            $table->integer('in_qty_sales_return')->default(0);
            $table->integer('out_qty_sale')->default(0);
            $table->integer('out_qty_purchase_return')->default(0);
            $table->integer('adjustment_qty')->default(0);

            $table->integer('closing_qty')->default(0);
            $table->decimal('closing_value_dpp', 15, 2)->default(0);
            $table->decimal('closing_value_final', 15, 2)->default(0);

            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'product_id', 'year', 'month'], 'stock_month_closings_unique');
            $table->index(['store_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_month_closings');
    }
};
