<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_runs', function (Blueprint $table) {
            $table->timestamp('stock_confirmed_at')->nullable()->after('approved_at');
            $table->foreignId('stock_confirmed_by')->nullable()->after('stock_confirmed_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('production_runs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stock_confirmed_by');
            $table->dropColumn('stock_confirmed_at');
        });
    }
};

