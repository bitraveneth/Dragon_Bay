<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_audits')) {
            return;
        }

        DB::statement('ALTER TABLE stock_audits MODIFY system_quantity DECIMAL(12,2) NOT NULL');
        DB::statement('ALTER TABLE stock_audits MODIFY counted_quantity DECIMAL(12,2) NOT NULL');
        DB::statement('ALTER TABLE stock_audits MODIFY variance DECIMAL(12,2) NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('stock_audits')) {
            return;
        }

        DB::statement('ALTER TABLE stock_audits MODIFY system_quantity INT NOT NULL');
        DB::statement('ALTER TABLE stock_audits MODIFY counted_quantity INT NOT NULL');
        DB::statement('ALTER TABLE stock_audits MODIFY variance INT NOT NULL');
    }
};
