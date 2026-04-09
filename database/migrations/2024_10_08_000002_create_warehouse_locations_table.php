<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained();
            $table->string('code');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::table('stock_entries', function (Blueprint $table) {
            $table->foreignId('warehouse_location_id')->nullable()->after('warehouse_id')->constrained('warehouse_locations');
        });
    }

    public function down(): void
    {
        Schema::table('stock_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warehouse_location_id');
        });

        Schema::dropIfExists('warehouse_locations');
    }
};

