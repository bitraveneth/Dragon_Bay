<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_warehouse_scopes')) {
            return;
        }

        Schema::create('user_warehouse_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'warehouse_id'], 'user_warehouse_scopes_user_warehouse_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_warehouse_scopes');
    }
};

