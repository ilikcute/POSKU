<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('daily_closings', function (Blueprint $table) {
            $table->id();

            $table->date('business_date');

            $table->foreignId('finalized_by')->constrained('users');
            $table->timestamp('finalized_at');

            // total 1 toko (sum semua station)
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->decimal('total_sales_return', 15, 2)->default(0);
            $table->decimal('total_purchase', 15, 2)->default(0);
            $table->decimal('total_purchase_return', 15, 2)->default(0);

            $table->decimal('total_discount', 15, 2)->default(0);
            $table->decimal('total_tax', 15, 2)->default(0);

            $table->decimal('cash_counted_total', 15, 2)->default(0);
            $table->decimal('expected_cash_total', 15, 2)->default(0);
            $table->decimal('variance_total', 15, 2)->default(0);

            $table->unsignedInteger('sales_count')->default(0);
            $table->unsignedInteger('station_count')->default(0);
            $table->unsignedInteger('shift_count')->default(0);

            $table->json('meta')->nullable(); // detail stations summary dll
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['business_date'], 'uniq_store_daily_close');
            $table->index(['business_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_closings');
    }
};
