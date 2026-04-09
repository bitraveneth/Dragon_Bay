<?php

namespace Database\Seeders\Control\Agents;

use App\Models\Agent;
use Illuminate\Database\Seeder;

/**
 * Seed data for:
 * - Agents (master records)
 */
class AgentsSeeder extends Seeder
{
    public function run(): void
    {
        // Dhaka North primary dealer
        Agent::firstOrCreate(
            ['name' => 'Dhaka North Dealer 01'],
            [
                'email'         => 'agent.dn01@demo.local',
                'phone'         => '01712-345678',
                'area'          => 'Mirpur',
                'zone'          => 'Dhaka North',
                'location_code' => 'DN-MIR-01',
                'credit_limit'  => 200000,
                'is_active'     => true,
            ]
        );

        // Dhaka South dealer
        Agent::firstOrCreate(
            ['name' => 'Dhaka South Dealer 01'],
            [
                'email'         => 'agent.ds01@demo.local',
                'phone'         => '01712-345679',
                'area'          => 'Jatrabari',
                'zone'          => 'Dhaka South',
                'location_code' => 'DS-JAT-01',
                'credit_limit'  => 150000,
                'is_active'     => true,
            ]
        );

        // Chattogram dealer
        Agent::firstOrCreate(
            ['name' => 'Chattogram Dealer 01'],
            [
                'email'         => 'agent.ctg01@demo.local',
                'phone'         => '01712-345680',
                'area'          => 'Agrabad',
                'zone'          => 'Chattogram',
                'location_code' => 'CTG-AGR-01',
                'credit_limit'  => 180000,
                'is_active'     => true,
            ]
        );

        // Sylhet dealer
        Agent::firstOrCreate(
            ['name' => 'Sylhet Dealer 01'],
            [
                'email'         => 'agent.syl01@demo.local',
                'phone'         => '01712-345681',
                'area'          => 'Zindabazar',
                'zone'          => 'Sylhet',
                'location_code' => 'SYL-ZIN-01',
                'credit_limit'  => 120000,
                'is_active'     => true,
            ]
        );

        // Corporate / institutional customer
        Agent::firstOrCreate(
            ['name' => 'Corporate Client 01'],
            [
                'email'         => 'corp.client01@demo.local',
                'phone'         => '01712-345682',
                'area'          => 'Gulshan',
                'zone'          => 'Dhaka Corporate',
                'location_code' => 'CORP-GUL-01',
                'credit_limit'  => 500000,
                'is_active'     => true,
            ]
        );
    }
}
