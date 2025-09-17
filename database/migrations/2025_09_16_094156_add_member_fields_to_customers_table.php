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
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('member_code')->nullable()->unique()->after('customer_code');
            $table->integer('points')->default(0)->after('address');
            $table->string('photo')->nullable()->after('points');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('photo');
            $table->timestamp('joined_at')->nullable()->after('status');
            $table->softDeletes();
            $table->index(['store_id', 'member_code']);
            $table->index(['store_id', 'status']);
            $table->index('name');
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn([
                'store_id',
                'member_code',
                'points',
                'photo',
                'status',
                'joined_at',
            ]);
            $table->dropSoftDeletes();
            $table->dropIndex(['store_id', 'member_code']);
            $table->dropIndex(['store_id', 'status']);
            $table->dropIndex(['name']);
            $table->dropIndex(['phone']);
        });
    }
};
