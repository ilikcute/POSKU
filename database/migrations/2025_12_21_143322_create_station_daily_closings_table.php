<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('station_daily_closings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')->constrained();
            $table->foreignId('station_id')->constrained();
            $table->date('business_date');

            $table->foreignId('closed_by')->constrained('users');
            $table->timestamp('closed_at');

            // Cash drawer
            $table->decimal('cash_counted', 15, 2)->default(0);

            $table->decimal('expected_cash', 15, 2)->default(0);

            $table->decimal('variance', 15, 2)->default(0);

            $table->decimal('cash_sales_total', 15, 2)->default(0);
            $table->decimal('cash_sales_return_total', 15, 2)->default(0);
            $table->decimal('cash_purchase_total', 15, 2)->default(0);
            $table->decimal('cash_purchase_return_total', 15, 2)->default(0);

            // Summary totals station (hari itu)
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->decimal('total_sales_return', 15, 2)->default(0);
            $table->decimal('total_purchase', 15, 2)->default(0);
            $table->decimal('total_purchase_return', 15, 2)->default(0);

            $table->decimal('total_discount', 15, 2)->default(0);
            $table->decimal('total_tax', 15, 2)->default(0);

            $table->unsignedInteger('sales_count')->default(0);
            $table->unsignedInteger('shift_count')->default(0);

            // Shift totals (modal awal akumulasi)
            $table->decimal('shifts_initial_cash_total', 15, 2)->default(0);

            $table->text('notes')->nullable();

            // Breakdown (mis: payment breakdown)
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['store_id', 'station_id', 'business_date'], 'uniq_station_daily_close');
            $table->index(['store_id', 'business_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('station_daily_closings');
    }
};
