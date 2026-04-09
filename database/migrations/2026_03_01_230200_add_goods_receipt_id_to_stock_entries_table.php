<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_entries') || ! Schema::hasTable('goods_receipts')) {
            return;
        }

        Schema::table('stock_entries', function (Blueprint $table) {
            $table->foreignId('goods_receipt_id')
                ->nullable()
                ->after('purchase_bill_id')
                ->constrained('goods_receipts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('stock_entries')) {
            return;
        }

        Schema::table('stock_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('goods_receipt_id');
        });
    }
};
