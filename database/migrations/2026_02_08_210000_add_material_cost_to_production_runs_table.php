<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_runs', function (Blueprint $table) {
            $table->decimal('material_unit_cost', 14, 4)->nullable()->after('materials_reserved');
            $table->decimal('material_total_cost', 14, 2)->nullable()->after('material_unit_cost');
        });
    }

    public function down(): void
    {
        Schema::table('production_runs', function (Blueprint $table) {
            $table->dropColumn(['material_unit_cost', 'material_total_cost']);
        });
    }
};

