<?php

namespace Database\Seeders\Control\Suppliers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

/**
 * Seed data for:
 * - Purchase order cycle (draft / approved / partial / received)
 *
 * Uses existing suppliers and raw-material SKUs seeded by other modules.
 */
class PurchaseOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            'ABC Plastics' => Supplier::where('name', 'ABC Plastics')->first(),
            'CartonCo' => Supplier::where('name', 'CartonCo')->first(),
            'XYZ Labels' => Supplier::where('name', 'XYZ Labels')->first(),
        ];

        $products = [
            'RM-PET-500' => Product::where('sku', 'RM-PET-500')->first(),
            'RM-CAP-STD' => Product::where('sku', 'RM-CAP-STD')->first(),
            'RM-CARTON-12X500' => Product::where('sku', 'RM-CARTON-12X500')->first(),
            'RM-LABEL-500' => Product::where('sku', 'RM-LABEL-500')->first(),
        ];

        if (! $suppliers['ABC Plastics'] || ! $suppliers['CartonCo'] || ! $suppliers['XYZ Labels']) {
            return;
        }

        if (! $products['RM-PET-500'] || ! $products['RM-CAP-STD'] || ! $products['RM-CARTON-12X500'] || ! $products['RM-LABEL-500']) {
            return;
        }

        $this->seedOrder(
            number: 'PO-20260301-001',
            supplierId: $suppliers['ABC Plastics']->id,
            orderDate: '2026-02-20',
            expectedDate: '2026-02-27',
            status: 'approved',
            notes: 'Monthly PET + cap replenishment for 500ml line',
            items: [
                [
                    'product_id' => $products['RM-PET-500']->id,
                    'description' => 'PET Bottle 500ml',
                    'quantity' => 80000,
                    'unit_price' => 5.00,
                    'received_quantity' => 0,
                ],
                [
                    'product_id' => $products['RM-CAP-STD']->id,
                    'description' => 'Bottle Cap - Standard',
                    'quantity' => 80000,
                    'unit_price' => 0.80,
                    'received_quantity' => 0,
                ],
            ]
        );

        $this->seedOrder(
            number: 'PO-20260301-002',
            supplierId: $suppliers['CartonCo']->id,
            orderDate: '2026-02-18',
            expectedDate: '2026-02-24',
            status: 'partial_received',
            notes: 'Carton procurement for dispatch planning',
            items: [
                [
                    'product_id' => $products['RM-CARTON-12X500']->id,
                    'description' => 'Carton Box - 12 x 500ml',
                    'quantity' => 12000,
                    'unit_price' => 20.00,
                    'received_quantity' => 7000,
                ],
            ]
        );

        $this->seedOrder(
            number: 'PO-20260301-003',
            supplierId: $suppliers['XYZ Labels']->id,
            orderDate: '2026-02-10',
            expectedDate: '2026-02-15',
            status: 'received',
            notes: 'Label procurement completed',
            items: [
                [
                    'product_id' => $products['RM-LABEL-500']->id,
                    'description' => 'BOPP Label - 500ml Bottle',
                    'quantity' => 100000,
                    'unit_price' => 0.60,
                    'received_quantity' => 100000,
                ],
            ]
        );

        $this->seedOrder(
            number: 'PO-20260301-004',
            supplierId: $suppliers['ABC Plastics']->id,
            orderDate: '2026-03-01',
            expectedDate: '2026-03-08',
            status: 'draft',
            notes: 'Upcoming planning PO - waiting approval',
            items: [
                [
                    'product_id' => $products['RM-PET-500']->id,
                    'description' => 'PET Bottle 500ml',
                    'quantity' => 60000,
                    'unit_price' => 5.10,
                    'received_quantity' => 0,
                ],
            ]
        );
    }

    /**
     * Upsert PO header then refresh its items to keep deterministic demo data.
     */
    protected function seedOrder(
        string $number,
        int $supplierId,
        string $orderDate,
        ?string $expectedDate,
        string $status,
        ?string $notes,
        array $items
    ): void {
        $order = PurchaseOrder::updateOrCreate(
            ['number' => $number],
            [
                'supplier_id' => $supplierId,
                'order_date' => $orderDate,
                'expected_date' => $expectedDate,
                'status' => $status,
                'notes' => $notes,
            ]
        );

        // Keep rows stable across reseeding.
        PurchaseOrderItem::where('purchase_order_id', $order->id)->delete();

        foreach ($items as $row) {
            $quantity = (float) $row['quantity'];
            $unitPrice = isset($row['unit_price']) ? (float) $row['unit_price'] : null;

            PurchaseOrderItem::create([
                'purchase_order_id' => $order->id,
                'product_id' => $row['product_id'] ?? null,
                'description' => $row['description'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $unitPrice !== null ? ($quantity * $unitPrice) : null,
                'received_quantity' => (float) ($row['received_quantity'] ?? 0),
            ]);
        }
    }
}
