<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Migrate legacy role values used on users before dropping legacy roles.
        $legacyToModern = [
            'warehouse_manager' => 'warehouse_officer',
            'production_manager' => 'production_officer',
            'sales_manager' => 'sales_officer',
            'employee' => 'sales_officer',
        ];

        foreach ($legacyToModern as $legacy => $modern) {
            User::where('role', $legacy)->update(['role' => $modern]);
        }

        if (Schema::hasTable('user_roles')) {
            foreach ($legacyToModern as $legacy => $modern) {
                DB::table('user_roles')
                    ->where('role_key', $legacy)
                    ->update(['role_key' => $modern, 'updated_at' => now()]);
            }
        }

        $definitions = [
            // Core system roles
            ['key' => 'super_admin', 'label' => 'Super admin', 'is_system' => true],
            ['key' => 'admin', 'label' => 'Admin', 'is_system' => true],

            // Real-world business roles
            ['key' => 'purchase_executive', 'label' => 'Purchase executive', 'is_system' => true],
            ['key' => 'warehouse_officer', 'label' => 'Warehouse officer', 'is_system' => true],
            ['key' => 'production_officer', 'label' => 'Production officer', 'is_system' => true],
            ['key' => 'sales_officer', 'label' => 'Sales officer', 'is_system' => true],
            ['key' => 'delivery_coordinator', 'label' => 'Delivery coordinator', 'is_system' => true],
            ['key' => 'accounts_officer', 'label' => 'Accounts officer', 'is_system' => true],
            ['key' => 'qc_officer', 'label' => 'QC officer', 'is_system' => true],
            ['key' => 'client', 'label' => 'Client', 'is_system' => true],
        ];

        foreach ($definitions as $role) {
            Role::updateOrCreate(
                ['key' => $role['key']],
                ['label' => $role['label'], 'is_system' => $role['is_system']]
            );
        }

        // Remove legacy roles from roles table so only real-world roles remain.
        Role::whereIn('key', array_keys($legacyToModern))->delete();
    }
}
