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
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('store_id')->constrained();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('final_amount', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamp('return_date');
            $table->timestamps();
            $table->softDeletes();
            $table->index('purchase_id');
            $table->index('user_id');
            $table->index('store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
