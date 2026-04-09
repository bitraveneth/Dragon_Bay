<?php

namespace Database\Seeders\Inventory;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\PurchaseBill;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockEntry;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed demo goods receipts (GRN) for the Inventory module.
 *
 * Creates a few realistic posted receipts tied to the purchase orders/bills
 * seeded by the Suppliers module, plus stock entries for approved lines.
 */
class GoodsReceiptsSeeder extends Seeder
{
    public function run(): void
    {
        $factory = Warehouse::where('name', 'Factory')->first();
        $centralDepot = Warehouse::where('name', 'Central Depot')->first();
        $factoryRackOne = WarehouseLocation::where('code', 'F1-R1-S1')->first();
        $factoryRackTwo = WarehouseLocation::where('code', 'F1-R2-S1')->first();
        $depotRack = WarehouseLocation::where('code', 'CD-R1-S1')->first();
        $creator = User::query()
            ->whereIn('role', ['super_admin', 'admin'])
            ->orderBy('id')
            ->first();

        $labelsPo = PurchaseOrder::where('number', 'PO-20260301-003')->with('items')->first();
        $cartonsPo = PurchaseOrder::where('number', 'PO-20260301-002')->with('items')->first();
        $labelsBill = PurchaseBill::where('number', 'PB-20260201-003')->first();
        $cartonsBill = PurchaseBill::where('number', 'PB-20260201-002')->first();
        $packagingBill = PurchaseBill::where('number', 'PB-20260201-004')->first();

        $labelProduct = Product::where('sku', 'RM-LABEL-500')->first();
        $cartonProduct = Product::where('sku', 'RM-CARTON-12X500')->first();
        $shrinkProduct = Product::where('sku', 'RM-SHRINK-CTN')->first();
        $packagingSupplier = Supplier::where('name', 'Packaging Ltd')->first();

        if (! $factory || ! $centralDepot || ! $labelProduct || ! $cartonProduct || ! $shrinkProduct) {
            return;
        }

        if (! $labelsPo || ! $cartonsPo || ! $packagingSupplier) {
            return;
        }

        DB::transaction(function () use (
            $creator,
            $factory,
            $centralDepot,
            $factoryRackOne,
            $factoryRackTwo,
            $depotRack,
            $labelsPo,
            $cartonsPo,
            $labelsBill,
            $cartonsBill,
            $packagingBill,
            $labelProduct,
            $cartonProduct,
            $shrinkProduct,
            $packagingSupplier
        ) {
            $this->seedReceipt(
                header: [
                    'grn_number' => 'GRN-20260301-001',
                    'purchase_order_id' => $labelsPo->id,
                    'purchase_bill_id' => $labelsBill?->id,
                    'supplier_id' => $labelsPo->supplier_id,
                    'warehouse_id' => $factory->id,
                    'created_by' => $creator?->id,
                    'received_at' => '2026-02-15 10:30:00',
                    'status' => 'posted',
                    'notes' => 'Complete label receipt for March finished-goods production planning.',
                ],
                lines: [
                    [
                        'purchase_order_item_id' => $labelsPo->items->first()?->id,
                        'product_id' => $labelProduct->id,
                        'warehouse_location_id' => $factoryRackOne?->id,
                        'quantity' => 100000,
                        'unit_cost' => 0.60,
                        'qc_status' => 'approved',
                        'remarks' => 'Labels received and cleared for production use.',
                    ],
                ]
            );

            $this->seedReceipt(
                header: [
                    'grn_number' => 'GRN-20260301-002',
                    'purchase_order_id' => $cartonsPo->id,
                    'purchase_bill_id' => $cartonsBill?->id,
                    'supplier_id' => $cartonsPo->supplier_id,
                    'warehouse_id' => $centralDepot->id,
                    'created_by' => $creator?->id,
                    'received_at' => '2026-02-24 15:00:00',
                    'status' => 'posted',
                    'notes' => 'First partial GRN for carton stock at central depot.',
                ],
                lines: [
                    [
                        'purchase_order_item_id' => $cartonsPo->items->first()?->id,
                        'product_id' => $cartonProduct->id,
                        'warehouse_location_id' => $depotRack?->id,
                        'quantity' => 7000,
                        'unit_cost' => 20.00,
                        'qc_status' => 'approved',
                        'remarks' => 'Partial supplier delivery accepted into depot stock.',
                    ],
                ]
            );

            $this->seedReceipt(
                header: [
                    'grn_number' => 'GRN-20260301-003',
                    'purchase_order_id' => null,
                    'purchase_bill_id' => $packagingBill?->id,
                    'supplier_id' => $packagingSupplier->id,
                    'warehouse_id' => $factory->id,
                    'created_by' => $creator?->id,
                    'received_at' => '2026-02-28 11:15:00',
                    'status' => 'posted',
                    'notes' => 'Manual GRN for shrink-wrap film received without a linked PO.',
                ],
                lines: [
                    [
                        'purchase_order_item_id' => null,
                        'product_id' => $shrinkProduct->id,
                        'warehouse_location_id' => $factoryRackTwo?->id,
                        'quantity' => 6000,
                        'unit_cost' => 0.20,
                        'qc_status' => 'approved',
                        'remarks' => 'Packing consumables received directly against supplier bill.',
                    ],
                ]
            );

            $this->syncPurchaseOrderReceiptState($labelsPo->fresh('items'));
            $this->syncPurchaseOrderReceiptState($cartonsPo->fresh('items'));
        });
    }

    protected function seedReceipt(array $header, array $lines): void
    {
        $receipt = GoodsReceipt::firstOrCreate(
            ['grn_number' => $header['grn_number']],
            $header
        );

        $receipt->fill($header);
        $receipt->save();

        StockEntry::where('goods_receipt_id', $receipt->id)->delete();
        GoodsReceiptItem::where('goods_receipt_id', $receipt->id)->delete();

        foreach ($lines as $line) {
            $grnItem = GoodsReceiptItem::create([
                'goods_receipt_id' => $receipt->id,
                'purchase_order_item_id' => $line['purchase_order_item_id'] ?? null,
                'product_id' => $line['product_id'] ?? null,
                'batch_id' => $line['batch_id'] ?? null,
                'warehouse_location_id' => $line['warehouse_location_id'] ?? null,
                'quantity' => (float) $line['quantity'],
                'unit_cost' => isset($line['unit_cost']) ? (float) $line['unit_cost'] : null,
                'line_total' => isset($line['unit_cost']) ? ((float) $line['quantity'] * (float) $line['unit_cost']) : null,
                'qc_status' => $line['qc_status'] ?? 'approved',
                'remarks' => $line['remarks'] ?? null,
            ]);

            if ($grnItem->qc_status === 'approved' && $grnItem->product_id) {
                StockEntry::create([
                    'purchase_bill_id' => $receipt->purchase_bill_id,
                    'goods_receipt_id' => $receipt->id,
                    'warehouse_id' => $receipt->warehouse_id,
                    'warehouse_location_id' => $grnItem->warehouse_location_id,
                    'product_id' => $grnItem->product_id,
                    'batch_id' => $grnItem->batch_id,
                    'quantity' => (float) $grnItem->quantity,
                    'status' => 'available',
                ]);
            }
        }
    }

    protected function syncPurchaseOrderReceiptState(?PurchaseOrder $purchaseOrder): void
    {
        if (! $purchaseOrder) {
            return;
        }

        foreach ($purchaseOrder->items as $item) {
            $receivedQty = (float) GoodsReceiptItem::query()
                ->where('purchase_order_item_id', $item->id)
                ->sum('quantity');

            $item->received_quantity = $receivedQty;
            $item->save();
        }

        $purchaseOrder->refresh();
        $purchaseOrder->load('items');

        $allReceived = $purchaseOrder->items->every(function (PurchaseOrderItem $item) {
            return (float) $item->received_quantity >= (float) $item->quantity;
        });

        $hasAnyReceived = $purchaseOrder->items->contains(function (PurchaseOrderItem $item) {
            return (float) $item->received_quantity > 0;
        });

        if ($allReceived) {
            $purchaseOrder->status = 'received';
        } elseif ($hasAnyReceived) {
            $purchaseOrder->status = 'partial_received';
        } else {
            $purchaseOrder->status = 'approved';
        }

        $purchaseOrder->save();
    }
}
