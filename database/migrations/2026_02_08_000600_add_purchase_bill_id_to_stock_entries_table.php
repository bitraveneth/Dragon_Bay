<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_entries', function (Blueprint $table) {
            $table->foreignId('purchase_bill_id')
                ->nullable()
                ->after('id')
                ->constrained('purchase_bills');
        });
    }

    public function down(): void
    {
        Schema::table('stock_entries', function (Blueprint $table) {
            $table->dropForeign(['purchase_bill_id']);
            $table->dropColumn('purchase_bill_id');
        });
    }
};

