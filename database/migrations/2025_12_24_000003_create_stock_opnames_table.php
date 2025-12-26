<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('docno', 20);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['I', 'E', 'A'])->default('I');
            $table->text('notes')->nullable();
            $table->timestamp('adjusted_at')->nullable();
            $table->timestamps();

            $table->unique(['docno'], 'stock_opnames_docno_unique');
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
