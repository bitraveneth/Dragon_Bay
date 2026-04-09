<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tax_classes')) {
            return;
        }

        Schema::table('tax_classes', function (Blueprint $table) {
            $table->unique('name', 'tax_classes_name_unique');
            $table->unique('hsn_code', 'tax_classes_hsn_code_unique');
            $table->unique('local_tax_code', 'tax_classes_local_tax_code_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('tax_classes')) {
            return;
        }

        Schema::table('tax_classes', function (Blueprint $table) {
            $table->dropUnique('tax_classes_name_unique');
            $table->dropUnique('tax_classes_hsn_code_unique');
            $table->dropUnique('tax_classes_local_tax_code_unique');
        });
    }
};
