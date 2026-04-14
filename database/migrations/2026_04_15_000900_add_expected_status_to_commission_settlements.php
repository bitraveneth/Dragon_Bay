<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL ENUM modification: add 'expected' between 'open' and 'approved'
        DB::statement("ALTER TABLE agent_commission_settlements MODIFY COLUMN status ENUM('open','expected','approved','paid') NOT NULL DEFAULT 'open'");
    }

    public function down(): void
    {
        // Revert 'expected' back to 'open' before removing the enum value
        DB::statement("UPDATE agent_commission_settlements SET status = 'open' WHERE status = 'expected'");
        DB::statement("ALTER TABLE agent_commission_settlements MODIFY COLUMN status ENUM('open','approved','paid') NOT NULL DEFAULT 'open'");
    }
};
