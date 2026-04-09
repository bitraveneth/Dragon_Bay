<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_location_logs', function (Blueprint $table) {
            $table->string('event_type', 50)
                ->nullable()
                ->after('employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('employee_location_logs', function (Blueprint $table) {
            $table->dropColumn('event_type');
        });
    }
};

