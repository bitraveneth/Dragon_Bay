<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_runs', function (Blueprint $table) {
            $table->string('order_number')->nullable()->after('id');
            $table->string('status')->default('confirmed')->after('quantity');
            $table->foreignId('supervisor_id')->nullable()->after('status')->constrained('employees')->nullOnDelete();
            $table->text('materials_reserved')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('production_runs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('supervisor_id');
            $table->dropColumn(['order_number', 'status', 'materials_reserved']);
        });
    }
};

