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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('shift_code');
            $table->string('name');
            $table->foreignId('store_id')->constrained();
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable();
            $table->string('total_struk');
            $table->decimal('initial_cash', 15, 2);
            $table->decimal('final_cash', 15, 2)->nullable();
            $table->decimal('total_sales', 15, 2)->nullable();
            $table->decimal('total_dicount', 15, 2)->nullable();
            $table->decimal('total_tax', 15, 2)->nullable();
            $table->decimal('total_purchase', 15, 2)->nullable();
            $table->decimal('variance', 15, 2)->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
