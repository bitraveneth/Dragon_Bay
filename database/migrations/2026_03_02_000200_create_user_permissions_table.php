<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_permissions')) {
            return;
        }

        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('permission_name', 100);
            $table->boolean('allowed')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'permission_name'], 'user_permissions_user_permission_unique');
            $table->index(['user_id', 'allowed'], 'user_permissions_user_allowed_index');
            $table->foreign('permission_name', 'user_permissions_permission_fk')
                ->references('name')
                ->on('permissions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_permissions')) {
            return;
        }

        Schema::table('user_permissions', function (Blueprint $table) {
            $table->dropForeign('user_permissions_permission_fk');
        });

        Schema::dropIfExists('user_permissions');
    }
};

