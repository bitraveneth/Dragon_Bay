<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        // Migrate any existing "packing" statuses to "picked" before changing the enum
        DB::table('orders')
            ->where('status', 'packing')
            ->update(['status' => 'picked']);

        DB::statement(
            "ALTER TABLE orders MODIFY COLUMN status ENUM('draft','confirmed','picked','packed','dispatched','delivered') NOT NULL DEFAULT 'draft'"
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        // Convert picked back to packing when rolling back
        DB::table('orders')
            ->where('status', 'picked')
            ->update(['status' => 'packing']);

        DB::statement(
            "ALTER TABLE orders MODIFY COLUMN status ENUM('draft','confirmed','packing','packed','dispatched','delivered') NOT NULL DEFAULT 'draft'"
        );
    }
};
