<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_distributions', function (Blueprint $table) {
            $table->unique(
                ['employee_id', 'period_start', 'period_end'],
                'salary_distributions_employee_period_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('salary_distributions', function (Blueprint $table) {
            $table->dropUnique('salary_distributions_employee_period_unique');
        });
    }
};
