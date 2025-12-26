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
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('station_id')->nullable()->constrained('stations');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('final_amount', 15, 2);
            $table->enum('payment_method', ['cash', 'debit', 'credit', 'transfer'])
                ->default('cash');
            $table->text('notes')->nullable();
            $table->timestamp('return_date');
            $table->timestamps();
            $table->softDeletes();
            $table->index('sale_id');
            $table->index('user_id');

            $table->index(['station_id', 'return_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_returns');
    }
};
