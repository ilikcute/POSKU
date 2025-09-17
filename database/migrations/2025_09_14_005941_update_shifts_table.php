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
        Schema::table('shifts', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->after('id');
            $table->foreignId('store_id')->constrained()->after('user_id');
            $table->timestamp('start_time')->after('store_id');
            $table->timestamp('end_time')->nullable()->after('start_time');
            $table->decimal('initial_cash', 15, 2)->after('end_time');
            $table->decimal('final_cash', 15, 2)->nullable()->after('initial_cash');
            $table->decimal('total_sales', 15, 2)->nullable()->after('final_cash');
            $table->decimal('variance', 15, 2)->nullable()->after('total_sales'); // Selisih
            $table->enum('status', ['open', 'closed'])->default('open')->after('variance');

            // Hapus kolom timestamp default yang tidak kita perlukan lagi
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            // Logika untuk rollback jika diperlukan
            $table->dropForeign(['user_id']);
            $table->dropForeign(['store_id']);
            $table->dropColumn([
                'user_id',
                'store_id',
                'start_time',
                'end_time',
                'initial_cash',
                'final_cash',
                'total_sales',
                'variance',
                'status',
            ]);
            $table->timestamps();
        });
    }
};
