<?php

namespace Database\Seeders\Users;

use App\Models\Agent;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Seed demo Client records and their portal User accounts.
 */
class ClientUsersSeeder extends Seeder
{
    public function run(): void
    {
        $seedPassword = (string) env('SEED_USER_PASSWORD', '');
        if (app()->environment('production') && $seedPassword === '') {
            return;
        }
        $seedPassword = $seedPassword !== '' ? $seedPassword : 'password';

        $this->migrateLegacyEmails();

        $demoClients = [
            [
                'client_name'   => 'Corporate Client 01',
                'company_name'  => 'Dragon Bay Corporate Ltd.',
                'email'         => 'corp.client@dragonbay.com',
                'phone'         => '+8801700000001',
                'currency'      => 'BDT',
                'credit_limit'  => 500000.00,
                'agent_name'    => null,   // set to an Agent name if you want auto-assignment
                'portal_email'  => 'client@dragonbay.com',
                'portal_name'   => 'Corporate Client Portal',
            ],
            [
                'client_name'   => 'Dhaka North Dealer 01',
                'company_name'  => 'DN Trading Co.',
                'email'         => 'dn01@dntrading.com',
                'phone'         => '+8801700000002',
                'currency'      => 'BDT',
                'credit_limit'  => 200000.00,
                'agent_name'    => null,
                'portal_email'  => 'dealer.dn01@dragonbay.com',
                'portal_name'   => 'Dhaka North Portal',
            ],
        ];

        foreach ($demoClients as $demo) {
            // Resolve optional agent
            $agentId = null;
            if ($demo['agent_name']) {
                $agentId = Agent::where('name', $demo['agent_name'])->value('id');
            }

            // Create or update the Client record
            $client = Client::updateOrCreate(
                ['email' => $demo['client_name']],   // use name as stable lookup key
                [
                    'name'          => $demo['client_name'],
                    'company_name'  => $demo['company_name'],
                    'email'         => $demo['email'],
                    'phone'         => $demo['phone'],
                    'currency'      => $demo['currency'],
                    'credit_limit'  => $demo['credit_limit'],
                    'agent_id'      => $agentId,
                    'is_active'     => true,
                ]
            );

            // Re-lookup cleanly using primary key
            $client = Client::where('name', $demo['client_name'])->first();

            if (! $client) {
                continue;
            }

            // Create or update the portal User linked to this client
            $user = User::updateOrCreate(
                ['email' => $demo['portal_email']],
                [
                    'name'        => $demo['portal_name'],
                    'password'    => Hash::make($seedPassword),
                    'role'        => 'client',
                    'client_id'   => $client->id,
                    'agent_id'    => null,
                    'employee_id' => null,
                ]
            );

            $this->syncPrimaryRole($user);
        }
    }

    protected function syncPrimaryRole(?User $user): void
    {
        if (! $user || ! Schema::hasTable('user_roles') || empty($user->role)) {
            return;
        }

        if (Schema::hasTable('roles') && ! DB::table('roles')->where('key', $user->role)->exists()) {
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
            'client@saferpv.local'     => 'client@dragonbay.com',
            'dealer.dn01@saferpv.local' => 'dealer.dn01@dragonbay.com',
        ];

        foreach ($emailMap as $legacy => $current) {
            if (User::where('email', $legacy)->exists() && ! User::where('email', $current)->exists()) {
                User::where('email', $legacy)->update(['email' => $current]);
            }
        }
    }
}
