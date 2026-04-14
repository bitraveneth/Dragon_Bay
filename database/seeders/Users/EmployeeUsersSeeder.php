<?php

namespace Database\Seeders\Users;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Seed login users that are linked to employee profiles.
 */
class EmployeeUsersSeeder extends Seeder
{
    public function run(): void
    {
        $seedPassword = (string) env('SEED_USER_PASSWORD', '');
        if (app()->environment('production') && $seedPassword === '') {
            return;
        }
        $seedPassword = $seedPassword !== '' ? $seedPassword : 'password';
        $this->migrateLegacyEmails();

        // Warehouse Manager login
        $warehouseManager = Employee::where('name', 'Warehouse Manager')->first();

        if ($warehouseManager) {
            $user = User::updateOrCreate(
                ['email' => 'warehouse@dragonbay.com'],
                [
                    'name'        => 'Demo Warehouse Manager',
                    'password'    => Hash::make($seedPassword),
                    'role'        => 'warehouse_officer',
                    'employee_id' => $warehouseManager->id,
                ]
            );
            $this->syncPrimaryRole($user);
        }

        // Production Manager login
        $productionManager = Employee::where('name', 'Production Manager')->first();

        if ($productionManager) {
            $user = User::updateOrCreate(
                ['email' => 'production@dragonbay.com'],
                [
                    'name'        => 'Demo Production Manager',
                    'password'    => Hash::make($seedPassword),
                    'role'        => 'production_officer',
                    'employee_id' => $productionManager->id,
                ]
            );
            $this->syncPrimaryRole($user);
        }

        // Field Sales Rep login
        $salesRep01 = Employee::where('name', 'Field Sales Rep 01')->first();

        if ($salesRep01) {
            $user = User::updateOrCreate(
                ['email' => 'employee@dragonbay.com'],
                [
                    'name'        => 'Demo Sales Rep',
                    'password'    => Hash::make($seedPassword),
                    'role'        => 'sales_officer',
                    'employee_id' => $salesRep01->id,
                ]
            );
            $this->syncPrimaryRole($user);
        }

        // Sales Manager login
        $salesManager = Employee::where('name', 'Sales Manager')->first();

        if ($salesManager) {
            $user = User::updateOrCreate(
                ['email' => 'sales.manager@dragonbay.com'],
                [
                    'name'        => 'Demo Sales Manager',
                    'password'    => Hash::make($seedPassword),
                    'role'        => 'delivery_coordinator',
                    'employee_id' => $salesManager->id,
                ]
            );
            $this->syncPrimaryRole($user);
        }

        // QC Officer login
        $qcOfficer = Employee::where('name', 'QC Officer')->first();

        if ($qcOfficer) {
            $user = User::updateOrCreate(
                ['email' => 'qc@dragonbay.com'],
                [
                    'name'        => 'Demo QC Officer',
                    'password'    => Hash::make($seedPassword),
                    'role'        => 'qc_officer',
                    'employee_id' => $qcOfficer->id,
                ]
            );
            $this->syncPrimaryRole($user);
        }

        // Real-world additional office roles (not necessarily linked to employee profiles)
        $purchase = User::updateOrCreate(
            ['email' => 'purchase@dragonbay.com'],
            [
                'name'     => 'Demo Purchase Executive',
                'password' => Hash::make($seedPassword),
                'role'     => 'purchase_executive',
            ]
        );
        $this->syncPrimaryRole($purchase);

        $accounts = User::updateOrCreate(
            ['email' => 'accounts@dragonbay.com'],
            [
                'name'     => 'Demo Accounts Officer',
                'password' => Hash::make($seedPassword),
                'role'     => 'accounts_officer',
            ]
        );
        $this->syncPrimaryRole($accounts);

        // Remove old legacy test account if it exists.
        User::where('email', 'legacy.employee@dragonbay.com')->delete();
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
            'warehouse@saferpv.local' => 'warehouse@dragonbay.com',
            'production@saferpv.local' => 'production@dragonbay.com',
            'employee@saferpv.local' => 'employee@dragonbay.com',
            'sales.manager@saferpv.local' => 'sales.manager@dragonbay.com',
            'qc@saferpv.local' => 'qc@dragonbay.com',
            'purchase@saferpv.local' => 'purchase@dragonbay.com',
            'accounts@saferpv.local' => 'accounts@dragonbay.com',
            'legacy.employee@saferpv.local' => 'legacy.employee@dragonbay.com',
        ];

        foreach ($emailMap as $legacy => $current) {
            if (User::where('email', $legacy)->exists() && ! User::where('email', $current)->exists()) {
                User::where('email', $legacy)->update(['email' => $current]);
            }
        }
    }
}
