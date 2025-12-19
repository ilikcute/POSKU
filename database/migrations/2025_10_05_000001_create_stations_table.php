<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('device_identifier')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('station_id')->nullable()->constrained('stations');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('station_id')->nullable()->constrained('stations');
        });

        Schema::table('sales_returns', function (Blueprint $table) {
            $table->foreignId('station_id')->nullable()->constrained('stations');
        });

        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->foreignId('station_id')->nullable()->constrained('stations');
        });

        Schema::table('shifts', function (Blueprint $table) {
            $table->foreignId('station_id')->nullable()->constrained('stations');
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('station_id');
        });

        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('station_id');
        });

        Schema::table('sales_returns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('station_id');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('station_id');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('station_id');
        });

        Schema::dropIfExists('stations');
    }
};
