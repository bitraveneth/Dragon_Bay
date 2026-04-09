<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_roles')) {
            return;
        }

        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role_key', 100);
            $table->timestamps();

            $table->unique(['user_id', 'role_key'], 'user_roles_user_role_unique');
            $table->index('role_key');
            $table->foreign('role_key', 'user_roles_role_key_fk')
                ->references('key')
                ->on('roles')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // Backfill from users.role so current users keep access after rollout.
        if (Schema::hasTable('users') && Schema::hasTable('roles')) {
            $now = now();
            $rows = DB::table('users')
                ->join('roles', 'users.role', '=', 'roles.key')
                ->select('users.id as user_id', 'users.role as role_key')
                ->whereNotNull('users.role')
                ->get()
                ->map(function ($row) use ($now) {
                    return [
                        'user_id' => $row->user_id,
                        'role_key' => $row->role_key,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                })
                ->all();

            if (! empty($rows)) {
                DB::table('user_roles')->insertOrIgnore($rows);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_roles')) {
            return;
        }

        Schema::table('user_roles', function (Blueprint $table) {
            $table->dropForeign('user_roles_role_key_fk');
        });

        Schema::dropIfExists('user_roles');
    }
};

