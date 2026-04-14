<?php

namespace Database\Seeders\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Seed core admin / system level users.
 */
class AdminUsersSeeder extends Seeder
{
    public function run(): void
    {
        $seedPassword = (string) env('SEED_USER_PASSWORD', '');
        if (app()->environment('production') && $seedPassword === '') {
            return;
        }
        $seedPassword = $seedPassword !== '' ? $seedPassword : 'password';
        $this->migrateLegacyEmails();

        // Primary super admin – full control over roles & permissions
        $superAdmin = User::updateOrCreate(
            ['email' => 'super@dragonbay.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make($seedPassword),
                'role'     => 'super_admin',
            ]
        );
        $this->syncPrimaryRole($superAdmin);

        // Normal admin account for day-to-day configuration
        $admin = User::updateOrCreate(
            ['email' => 'admin@dragonbay.com'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make($seedPassword),
                'role'     => 'admin',
            ]
        );
        $this->syncPrimaryRole($admin);
    }

    protected function syncPrimaryRole(User $user): void
    {
        if (! Schema::hasTable('user_roles') || empty($user->role)) {
            return;
        }

        DB::table('user_roles')->updateOrInsert(
            ['user_id' => $user->id, 'role_key' => $user->role],
            ['updated_at' => now(), 'created_at' => now()]
        );
    }

    protected function migrateLegacyEmails(): void
    {
        $emailMap = [
            'super@saferpv.local' => 'super@dragonbay.com',
            'admin@saferpv.local' => 'admin@dragonbay.com',
        ];

        foreach ($emailMap as $legacy => $current) {
            if (User::where('email', $legacy)->exists() && ! User::where('email', $current)->exists()) {
                User::where('email', $legacy)->update(['email' => $current]);
            }
        }
    }
}
