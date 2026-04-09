<?php

namespace Database\Seeders\Accounting;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed data for Accounting → Customer invoices.
 *
 * For the demo we take the first confirmed sales order
 * for the demo SKU and generate a simple invoice plus
 * matching ledger entries:
 *
 * - DR Accounts Receivable
 * - CR Sales Revenue
 * - CR VAT Payable
 */
class CustomerInvoicesModuleSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::where('sku', 'SAF-500ML-CTN')->first();

        if (! $product) {
            return;
        }

        $orders = Order::where('status', 'confirmed')
            ->whereHas('items', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->with('items')
            ->orderBy('id')
            ->limit(5)
            ->get();

        if ($orders->isEmpty()) {
            return;
        }

        foreach ($orders as $order) {
            // Avoid double seeding if an invoice is already linked.
            if (Invoice::where('order_id', $order->id)->exists()) {
                continue;
            }

            DB::transaction(function () use ($order, $product): void {
                if (Invoice::where('order_id', $order->id)->exists()) {
                    return;
                }

                $item = $order->items()->where('product_id', $product->id)->first();
                if (! $item) {
                    return;
                }

                $netTotal  = $item->quantity * $item->unit_price;
                $vatRate   = 0.15; // 15% demo VAT
                $vatAmount = round($netTotal * $vatRate, 2);

                $issuedAt = now()->copy()->subDays(max(0, 5 - $order->id))->toDateString();

                $invoice = Invoice::create([
                    'order_id'    => $order->id,
                    'number'      => 'INV-' . str_pad((string) ($order->id), 6, '0', STR_PAD_LEFT),
                    'issued_at'   => $issuedAt,
                    'due_at'      => now()->addDays(7)->toDateString(),
                    'net_total'   => $netTotal,
                    'vat_amount'  => $vatAmount,
                    'withholding' => 0,
                    'status'      => 'issued',
                ]);

                InvoiceItem::create([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $product->id,
                    'description' => $product->name,
                    'quantity'    => $item->quantity,
                    'unit_price'  => $item->unit_price,
                    'line_total'  => $netTotal,
                ]);

                $gross = $netTotal + $vatAmount;

                LedgerEntry::create([
                    'account'     => 'Accounts Receivable',
                    'description' => 'Invoice ' . $invoice->number,
                    'debit'       => $gross,
                    'credit'      => 0,
                    'order_id'    => $order->id,
                    'invoice_id'  => $invoice->id,
                ]);

                LedgerEntry::create([
                    'account'     => 'Sales Revenue',
                    'description' => 'Invoice ' . $invoice->number,
                    'debit'       => 0,
                    'credit'      => $netTotal,
                    'order_id'    => $order->id,
                    'invoice_id'  => $invoice->id,
                ]);

                if ($vatAmount > 0) {
                    LedgerEntry::create([
                        'account'     => 'VAT Payable',
                        'description' => 'VAT on ' . $invoice->number,
                        'debit'       => 0,
                        'credit'      => $vatAmount,
                        'order_id'    => $order->id,
                        'invoice_id'  => $invoice->id,
                    ]);
                }
            });
        }
    }
}
