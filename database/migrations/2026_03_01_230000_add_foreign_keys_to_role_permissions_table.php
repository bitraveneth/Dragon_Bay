<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('role_permissions') || ! Schema::hasTable('roles') || ! Schema::hasTable('permissions')) {
            return;
        }

        // Cleanup orphaned rows before adding FK constraints.
        DB::table('role_permissions')
            ->whereNotIn('role', function ($query) {
                $query->select('key')->from('roles');
            })
            ->delete();

        DB::table('role_permissions')
            ->whereNotIn('permission_name', function ($query) {
                $query->select('name')->from('permissions');
            })
            ->delete();

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->foreign('role', 'role_permissions_role_fk')
                ->references('key')
                ->on('roles')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('permission_name', 'role_permissions_permission_fk')
                ->references('name')
                ->on('permissions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('role_permissions')) {
            return;
        }

        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropForeign('role_permissions_role_fk');
            $table->dropForeign('role_permissions_permission_fk');
        });
    }
};
