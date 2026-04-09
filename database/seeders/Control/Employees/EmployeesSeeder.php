<?php

namespace Database\Seeders\Control\Employees;

use App\Models\Employee;
use Illuminate\Database\Seeder;

/**
 * Seed data for:
 * - Employees (core employee records).
 */
class EmployeesSeeder extends Seeder
{
    public function run(): void
    {
        // Admin / backoffice user
        Employee::firstOrCreate(
            ['name' => 'Admin Staff'],
            [
                'work_email'   => 'admin.staff@demo.local',
                'work_phone'   => '02-000000',
                'work_mobile'  => '01712-000000',
                'department'   => 'Administration',
                'job_position' => 'System Admin',
                'work_zone'    => 'Head Office',
            ]
        );

        // Warehouse manager
        Employee::firstOrCreate(
            ['name' => 'Warehouse Manager'],
            [
                'work_email'   => 'warehouse@demo.local',
                'work_phone'   => '02-000001',
                'work_mobile'  => '01712-000001',
                'department'   => 'Inventory & Logistics',
                'job_position' => 'Warehouse Manager',
                'work_zone'    => 'Factory / Central Depot',
            ]
        );

        // Production manager
        Employee::firstOrCreate(
            ['name' => 'Production Manager'],
            [
                'work_email'   => 'production@demo.local',
                'work_phone'   => null,
                'work_mobile'  => '01712-000004',
                'department'   => 'Production',
                'job_position' => 'Production Manager',
                'work_zone'    => 'Factory',
            ]
        );

        // Sales manager
        Employee::firstOrCreate(
            ['name' => 'Sales Manager'],
            [
                'work_email'   => 'sales.manager@demo.local',
                'work_phone'   => null,
                'work_mobile'  => '01712-000005',
                'department'   => 'Sales & Marketing',
                'job_position' => 'Sales Manager',
                'work_zone'    => 'National',
            ]
        );

        // Field sales representative
        Employee::firstOrCreate(
            ['name' => 'Field Sales Rep 01'],
            [
                'work_email'   => 'salesrep01@demo.local',
                'work_phone'   => null,
                'work_mobile'  => '01712-000002',
                'department'   => 'Sales & Marketing',
                'job_position' => 'Sales Representative',
                'work_zone'    => 'Dhaka North',
            ]
        );

        // QC officer
        Employee::firstOrCreate(
            ['name' => 'QC Officer'],
            [
                'work_email'   => 'qc@demo.local',
                'work_phone'   => null,
                'work_mobile'  => '01712-000003',
                'department'   => 'Quality Control',
                'job_position' => 'QC Officer',
                'work_zone'    => 'Factory',
            ]
        );
    }
}
