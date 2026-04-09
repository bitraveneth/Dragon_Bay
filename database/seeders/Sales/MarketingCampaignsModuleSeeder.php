<?php

namespace Database\Seeders\Sales;

use App\Models\Campaign;
use Illuminate\Database\Seeder;

/**
 * Seed data for Sales → Marketing campaigns.
 */
class MarketingCampaignsModuleSeeder extends Seeder
{
    public function run(): void
    {
        Campaign::firstOrCreate(
            ['name' => 'Dhaka North Launch – Facebook'],
            [
                'platform'      => 'facebook',
                'start_date'    => now()->subDays(10)->toDateString(),
                'end_date'      => now()->addDays(20)->toDateString(),
                'reach'         => 50000,
                'impressions'   => 150000,
                'cost'          => 25000,
                'status'        => 'running',
                'campaign_code' => 'FB-DN-LAUNCH-01',
                'notes'         => 'Seeded demo campaign for Dhaka North dealers',
            ]
        );

        Campaign::firstOrCreate(
            ['name' => 'Ramadan Offer – Google Ads'],
            [
                'platform'      => 'google-ads',
                'start_date'    => now()->subDays(30)->toDateString(),
                'end_date'      => now()->subDays(5)->toDateString(),
                'reach'         => 80000,
                'impressions'   => 220000,
                'cost'          => 40000,
                'status'        => 'completed',
                'campaign_code' => 'GA-RAMADAN-01',
                'notes'         => 'Demo historical campaign used in reports',
            ]
        );
    }
}
