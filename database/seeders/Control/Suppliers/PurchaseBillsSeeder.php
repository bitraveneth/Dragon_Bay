<?php

namespace Database\Seeders\Control\Suppliers;

use App\Models\Product;
use App\Models\PurchaseBill;
use App\Models\PurchaseBillItem;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

/**
 * Seed data for:
 * - Purchase bills and their line items.
 *
 * These bills do NOT yet post to stock_entries. They are intended
 * to give the Purchase / Supplier UIs something realistic to show.
 */
class PurchaseBillsSeeder extends Seeder
{
    public function run(): void
    {
        $abcPlastics = Supplier::where('name', 'ABC Plastics')->first();
        $cartonCo    = Supplier::where('name', 'CartonCo')->first();
        $xyzLabels   = Supplier::where('name', 'XYZ Labels')->first();
        $packaging   = Supplier::where('name', 'Packaging Ltd')->first();
        $waterSupp   = Supplier::where('name', 'Local Water Provider')->first();
        $johnLabour  = Supplier::where('name', 'John Contractor')->first();

        $petBottle   = Product::where('sku', 'RM-PET-500')->first();
        $cap         = Product::where('sku', 'RM-CAP-STD')->first();
        $carton      = Product::where('sku', 'RM-CARTON-12X500')->first();
        $label       = Product::where('sku', 'RM-LABEL-500')->first();
        $shrink      = Product::where('sku', 'RM-SHRINK-CTN')->first();
        $water       = Product::where('sku', 'RM-RO-WATER')->first();
        $labour      = Product::where('sku', 'SV-LAB-FACT')->first();

        // Bill 1 – PET bottles + caps from ABC Plastics
        if ($abcPlastics && $petBottle && $cap) {
            $bill1 = PurchaseBill::firstOrCreate(
                ['number' => 'PB-20260201-001'],
                [
                    'supplier_id' => $abcPlastics->id,
                    'bill_date'   => now()->subDays(7)->toDateString(),
                    'due_date'    => now()->addDays(7)->toDateString(),
                    'net_total'   => 120000,
                    'vat_amount'  => 0,
                    'status'      => 'open',
                ]
            );

            if ($bill1->wasRecentlyCreated || $bill1->items()->count() === 0) {
                $bill1->items()->delete();

                PurchaseBillItem::create([
                    'purchase_bill_id' => $bill1->id,
                    'product_id'       => $petBottle->id,
                    'description'      => 'PET Bottle 500ml',
                    'quantity'         => 50000,
                    'unit_price'       => 0.12,
                    'line_total'       => 50000 * 0.12,
                ]);

                PurchaseBillItem::create([
                    'purchase_bill_id' => $bill1->id,
                    'product_id'       => $cap->id,
                    'description'      => 'Bottle Cap – Standard',
                    'quantity'         => 50000,
                    'unit_price'       => 0.02,
                    'line_total'       => 50000 * 0.02,
                ]);
            }
        }

        // Bill 2 – Carton boxes from CartonCo
        if ($cartonCo && $carton) {
            $bill2 = PurchaseBill::firstOrCreate(
                ['number' => 'PB-20260201-002'],
                [
                    'supplier_id' => $cartonCo->id,
                    'bill_date'   => now()->subDays(5)->toDateString(),
                    'due_date'    => now()->addDays(10)->toDateString(),
                    'net_total'   => 48000,
                    'vat_amount'  => 0,
                    'status'      => 'part_paid',
                ]
            );

            if ($bill2->wasRecentlyCreated || $bill2->items()->count() === 0) {
                $bill2->items()->delete();

                PurchaseBillItem::create([
                    'purchase_bill_id' => $bill2->id,
                    'product_id'       => $carton->id,
                    'description'      => 'Carton Box – 12 x 500ml',
                    'quantity'         => 6000,
                    'unit_price'       => 0.80,
                    'line_total'       => 6000 * 0.80,
                ]);
            }
        }

        // Bill 3 – Labels from XYZ Labels
        if ($xyzLabels && $label) {
            $bill3 = PurchaseBill::firstOrCreate(
                ['number' => 'PB-20260201-003'],
                [
                    'supplier_id' => $xyzLabels->id,
                    'bill_date'   => now()->subDays(4)->toDateString(),
                    'due_date'    => now()->addDays(12)->toDateString(),
                    'net_total'   => 18000,
                    'vat_amount'  => 0,
                    'status'      => 'open',
                ]
            );

            if ($bill3->wasRecentlyCreated || $bill3->items()->count() === 0) {
                $bill3->items()->delete();

                PurchaseBillItem::create([
                    'purchase_bill_id' => $bill3->id,
                    'product_id'       => $label->id,
                    'description'      => 'BOPP Label – 500ml Bottle',
                    'quantity'         => 60000,
                    'unit_price'       => 0.03,
                    'line_total'       => 60000 * 0.03,
                ]);
            }
        }

        // Bill 4 – Shrink wrap from Packaging Ltd
        if ($packaging && $shrink) {
            $bill4 = PurchaseBill::firstOrCreate(
                ['number' => 'PB-20260201-004'],
                [
                    'supplier_id' => $packaging->id,
                    'bill_date'   => now()->subDays(3)->toDateString(),
                    'due_date'    => now()->addDays(15)->toDateString(),
                    'net_total'   => 12000,
                    'vat_amount'  => 0,
                    'status'      => 'open',
                ]
            );

            if ($bill4->wasRecentlyCreated || $bill4->items()->count() === 0) {
                $bill4->items()->delete();

                PurchaseBillItem::create([
                    'purchase_bill_id' => $bill4->id,
                    'product_id'       => $shrink->id,
                    'description'      => 'Shrink Wrap Film – Carton',
                    'quantity'         => 6000,
                    'unit_price'       => 0.20,
                    'line_total'       => 6000 * 0.20,
                ]);
            }
        }

        // Bill 5 – RO water + labour from Local Water Provider / John Contractor
        if ($waterSupp && $water && $johnLabour && $labour) {
            $bill5 = PurchaseBill::firstOrCreate(
                ['number' => 'PB-20260201-005'],
                [
                    'supplier_id' => $waterSupp->id,
                    'bill_date'   => now()->subDays(2)->toDateString(),
                    'due_date'    => now()->addDays(20)->toDateString(),
                    'net_total'   => 15000,
                    'vat_amount'  => 0,
                    'status'      => 'open',
                ]
            );

            if ($bill5->wasRecentlyCreated || $bill5->items()->count() === 0) {
                $bill5->items()->delete();

                PurchaseBillItem::create([
                    'purchase_bill_id' => $bill5->id,
                    'product_id'       => $water->id,
                    'description'      => 'Treated RO Water',
                    'quantity'         => 200000,
                    'unit_price'       => 0.05,
                    'line_total'       => 200000 * 0.05,
                ]);

                PurchaseBillItem::create([
                    'purchase_bill_id' => $bill5->id,
                    'product_id'       => $labour->id,
                    'description'      => 'Labour – Factory Line',
                    'quantity'         => 10,
                    'unit_price'       => 15.00,
                    'line_total'       => 10 * 15.00,
                ]);
            }
        }
    }
}
