<?php

namespace Database\Seeders\Control\Employees;

use App\Models\Badge;
use App\Models\Employee;
use App\Models\EmployeeBadge;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seed data for:
 * - Employee badges.
 */
class BadgesSeeder extends Seeder
{
    public function run(): void
    {
        $topPerformer = Badge::firstOrCreate(
            ['code' => 'TOP-PERFORMER'],
            [
                'name'        => 'Top performer',
                'description' => 'Awarded for outstanding sales performance.',
                'color'       => '#2563eb', // blue
                'is_active'   => true,
            ]
        );

        $onTimeDelivery = Badge::firstOrCreate(
            ['code' => 'ON-TIME-DELIVERY'],
            [
                'name'        => 'On‑time delivery',
                'description' => 'Consistently on‑time delivery to agents.',
                'color'       => '#16a34a', // green
                'is_active'   => true,
            ]
        );

        $salesRep01       = Employee::where('name', 'Field Sales Rep 01')->first();
        $warehouseManager = Employee::where('name', 'Warehouse Manager')->first();

        if ($salesRep01 && $topPerformer) {
            EmployeeBadge::firstOrCreate(
                [
                    'employee_id' => $salesRep01->id,
                    'badge_id'    => $topPerformer->id,
                    'granted_at'  => Carbon::today()->copy()->subMonth(),
                ],
                [
                    'granted_by' => 'Admin Staff',
                    'note'       => 'Exceeded monthly sales target.',
                ]
            );
        }

        if ($warehouseManager && $onTimeDelivery) {
            EmployeeBadge::firstOrCreate(
                [
                    'employee_id' => $warehouseManager->id,
                    'badge_id'    => $onTimeDelivery->id,
                    'granted_at'  => Carbon::today()->copy()->subWeeks(2),
                ],
                [
                    'granted_by' => 'Admin Staff',
                    'note'       => 'Maintained zero delayed dispatches this month.',
                ]
            );
        }
    }
}

