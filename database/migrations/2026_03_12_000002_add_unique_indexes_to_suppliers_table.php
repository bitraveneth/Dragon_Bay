<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->unique('name', 'suppliers_name_unique');
            $table->unique('email', 'suppliers_email_unique');
            $table->unique('tax_id', 'suppliers_tax_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropUnique('suppliers_name_unique');
            $table->dropUnique('suppliers_email_unique');
            $table->dropUnique('suppliers_tax_id_unique');
        });
    }
};
