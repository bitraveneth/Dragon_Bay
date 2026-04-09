<?php

namespace Database\Seeders\Sales;

use Illuminate\Database\Seeder;

/**
 * Seed data for Sales → Picking lists.
 */
class PickingListsModuleSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * At the moment picking lists are rendered from orders and
         * stock, there is no dedicated `picking_lists` table.
         *
         * We keep this seeder as a no-op so that the module structure
         * mirrors the sidebar, but there is nothing to insert in the
         * database here.
         */
        return;
    }
}
