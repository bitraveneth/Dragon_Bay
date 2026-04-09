<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vehicles') || Schema::hasColumn('vehicles', 'is_active')) {
            return;
        }

        Schema::table('vehicles', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('capacity_crates');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('vehicles') || ! Schema::hasColumn('vehicles', 'is_active')) {
            return;
        }

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
