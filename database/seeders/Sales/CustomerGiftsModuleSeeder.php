<?php

namespace Database\Seeders\Sales;

use App\Models\Agent;
use App\Models\CustomerGift;
use App\Models\Employee;
use Illuminate\Database\Seeder;

/**
 * Seed data for Sales → Customer gifts.
 *
 * Creates a couple of demo gifts so that the CRM / marketing
 * screens have realistic rows to display.
 */
class CustomerGiftsModuleSeeder extends Seeder
{
    public function run(): void
    {
            $agent = Agent::where('name', 'Dhaka North Dealer 01')->first();
            $employee = Employee::where('name', 'Field Sales Rep 01')->first();

            if (! $agent) {
                return;
            }

            CustomerGift::firstOrCreate(
                [
                    'agent_id'   => $agent->id,
                    'date'       => now()->toDateString(),
                    'gift_type'  => 'Fridge',
                ],
                [
                    'employee_id' => $employee?->id,
                    'occasion'    => 'Yearly',
                    'description' => 'Demo fridge gift for Dhaka North Dealer 01',
                    'amount'      => 15000,
                    'campaign_code' => 'CG-2026-001',
                    'status'        => 'given',
                ]
            );

            CustomerGift::firstOrCreate(
                [
                    'agent_id'  => $agent->id,
                    'date'      => now()->copy()->addDays(7)->toDateString(),
                    'gift_type' => 'Banner',
                ],
                [
                    'employee_id' => $employee?->id,
                    'occasion'    => 'Festival',
                    'description' => 'Demo banner and POSM',
                    'amount'      => 2000,
                    'campaign_code' => 'CG-2026-002',
                    'status'        => 'planned',
                ]
            );
    }
}
