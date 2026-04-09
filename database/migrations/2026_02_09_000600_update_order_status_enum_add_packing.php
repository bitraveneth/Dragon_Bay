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

        // Update ENUM to include the new "packing" status between confirmed and packed
        DB::statement(
            "ALTER TABLE orders MODIFY COLUMN status ENUM('draft','confirmed','packing','packed','dispatched','delivered') NOT NULL DEFAULT 'draft'"
        );
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        // Revert back to the original ENUM without "packing"
        DB::statement(
            "ALTER TABLE orders MODIFY COLUMN status ENUM('draft','confirmed','packed','dispatched','delivered') NOT NULL DEFAULT 'draft'"
        );
    }
};

