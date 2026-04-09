<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->unique(['employee_id', 'period_start', 'period_end'], 'sales_targets_employee_period_unique');
            $table->unique(['agent_id', 'period_start', 'period_end'], 'sales_targets_agent_period_unique');
        });
    }

    public function down(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->dropUnique('sales_targets_employee_period_unique');
            $table->dropUnique('sales_targets_agent_period_unique');
        });
    }
};
