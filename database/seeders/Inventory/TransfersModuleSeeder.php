<?php

namespace Database\Seeders\Inventory;

use App\Models\Order;
use App\Models\StockEntry;
use App\Models\StockMovement;
use Illuminate\Database\Seeder;

/**
 * Seed data for Inventory → Transfers.
 *
 * For now we create a simple internal transfer reflected as
 * stock movements on existing stock entries (if present).
 */
class TransfersModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Get any factory and depot stock entries for the demo product
        $factoryEntry = StockEntry::whereHas('warehouse', function ($q) {
                $q->where('name', 'Factory');
            })
            ->where('status', 'available')
            ->first();

        $depotEntry = StockEntry::whereHas('warehouse', function ($q) {
                $q->where('name', 'Central Depot');
            })
            ->where('status', 'available')
            ->first();

        if (! $factoryEntry || ! $depotEntry) {
            return;
        }

        $qty = 100; // cartons

        // Simple transfer‑out movement from factory
        StockMovement::firstOrCreate(
            [
                'stock_entry_id' => $factoryEntry->id,
                'type'           => 'transfer-out',
                'quantity'       => -1 * $qty,
            ],
            [
                'order_id' => null,
                'notes'    => 'Demo stock transfer from Factory to Central Depot',
            ]
        );

        // Matching transfer‑in movement to depot
        StockMovement::firstOrCreate(
            [
                'stock_entry_id' => $depotEntry->id,
                'type'           => 'transfer-in',
                'quantity'       => $qty,
            ],
            [
                'order_id' => null,
                'notes'    => 'Demo stock transfer from Factory to Central Depot',
            ]
        );
    }
}

