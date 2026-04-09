<?php

namespace Database\Seeders\Sales;

use App\Models\Agent;
use App\Models\Employee;
use App\Models\SalesTarget;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SalesTargetsModuleSeeder extends Seeder
{
    public function run(): void
    {
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();

        $agentNorth = Agent::where('name', 'Dhaka North Dealer 01')->first();
        $agentSouth = Agent::where('name', 'Dhaka South Dealer 01')->first();
        $salesEmployee = Employee::query()
            ->where(function ($query) {
                $query->where('job_position', 'like', '%sales%')
                    ->orWhere('department', 'like', '%sales%');
            })
            ->orderBy('name')
            ->first();

        if ($agentNorth) {
            SalesTarget::updateOrCreate(
                [
                    'agent_id' => $agentNorth->id,
                    'employee_id' => null,
                    'period_start' => $currentMonthStart->toDateString(),
                    'period_end' => $currentMonthEnd->toDateString(),
                ],
                [
                    'target_value' => 3500000,
                ]
            );
        }

        if ($agentSouth) {
            SalesTarget::updateOrCreate(
                [
                    'agent_id' => $agentSouth->id,
                    'employee_id' => null,
                    'period_start' => $currentMonthStart->toDateString(),
                    'period_end' => $currentMonthEnd->toDateString(),
                ],
                [
                    'target_value' => 2800000,
                ]
            );
        }

        if ($salesEmployee) {
            SalesTarget::updateOrCreate(
                [
                    'employee_id' => $salesEmployee->id,
                    'agent_id' => null,
                    'period_start' => $currentMonthStart->toDateString(),
                    'period_end' => $currentMonthEnd->toDateString(),
                ],
                [
                    'target_value' => 4200000,
                ]
            );
        }
    }
}
