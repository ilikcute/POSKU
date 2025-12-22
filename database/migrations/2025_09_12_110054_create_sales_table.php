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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 100)->unique();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('store_id')->constrained();
            $table->foreignId('station_id')->nullable()->constrained('stations');
            $table->foreignId('customer_id')->nullable()->constrained();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('final_amount', 15, 2);
            $table->enum('payment_method', ['cash', 'card', 'transfer']);
            $table->decimal('amount_paid', 15, 2);
            $table->decimal('change_due', 15, 2)->default(0);
            $table->timestamp('transaction_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['store_id', 'station_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
