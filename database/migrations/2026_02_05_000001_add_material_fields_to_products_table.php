<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_type')->default('finished')->after('name');
            $table->string('uom')->nullable()->after('size');
            $table->decimal('standard_cost', 10, 2)->default(0)->after('base_price');
            $table->string('supplier_name')->nullable()->after('standard_cost');
            $table->boolean('is_active')->default(true)->after('supplier_name');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['product_type', 'uom', 'standard_cost', 'supplier_name', 'is_active']);
        });
    }
};

