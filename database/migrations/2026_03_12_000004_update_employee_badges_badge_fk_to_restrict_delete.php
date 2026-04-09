<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('employee_badges') || ! Schema::hasTable('badges')) {
            return;
        }

        Schema::table('employee_badges', function (Blueprint $table) {
            $table->dropForeign(['badge_id']);
            $table->foreign('badge_id')->references('id')->on('badges')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('employee_badges') || ! Schema::hasTable('badges')) {
            return;
        }

        Schema::table('employee_badges', function (Blueprint $table) {
            $table->dropForeign(['badge_id']);
            $table->foreign('badge_id')->references('id')->on('badges')->cascadeOnDelete();
        });
    }
};
