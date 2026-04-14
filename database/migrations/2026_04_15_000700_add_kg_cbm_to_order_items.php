<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('weight_kg', 10, 3)->nullable()->after('commission_amount');
            $table->decimal('length_cm', 10, 2)->nullable()->after('weight_kg');
            $table->decimal('width_cm', 10, 2)->nullable()->after('length_cm');
            $table->decimal('height_cm', 10, 2)->nullable()->after('width_cm');
            $table->unsignedInteger('pieces')->default(1)->after('height_cm');
            $table->decimal('cbm', 10, 4)->nullable()->after('pieces');
            $table->decimal('chargeable_weight_kg', 10, 3)->nullable()->after('cbm');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn([
                'weight_kg', 'length_cm', 'width_cm', 'height_cm',
                'pieces', 'cbm', 'chargeable_weight_kg',
            ]);
        });
    }
};
