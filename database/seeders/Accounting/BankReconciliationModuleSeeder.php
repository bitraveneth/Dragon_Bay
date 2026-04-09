<?php

namespace Database\Seeders\Accounting;

use App\Models\Receipt;
use App\Models\Invoice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seed data for Accounting → Bank reconciliation.
 */
class BankReconciliationModuleSeeder extends Seeder
{
    public function run(): void
    {
        // Never mutate existing receipts in a seeder.
        // If real data exists, exit safely.
        if (Receipt::query()->exists()) {
            return;
        }

        // Otherwise, create some demo invoices + receipts so the Bank
        // Reconciliation screen looks alive after seeding.
        $startOfMonth = Carbon::now()->startOfMonth();

        // Reuse an existing invoice if present, otherwise create a simple demo invoice.
        $baseInvoice = Invoice::query()->first();

        if (! $baseInvoice) {
            $baseInvoice = Invoice::create([
                'order_id'   => null,
                'number'     => 'INV-DEMO-0001',
                'issued_at'  => $startOfMonth->copy()->addDay(),
                'due_at'     => $startOfMonth->copy()->addDays(15),
                'net_total'  => 250000,
                'vat_amount' => 37500,
                'withholding'=> 0,
                'status'     => 'paid',
            ]);
        }

        $invoiceId = $baseInvoice->id;

        $demoReceipts = [
            [
                'amount'         => 43500,
                'payment_method' => 'bank_transfer',
                'received_at'    => $startOfMonth->copy()->addDays(2),
                'notes'          => 'Demo receipt – February salary run',
                'reconciled'     => true,
            ],
            [
                'amount'         => 125000,
                'payment_method' => 'bank_deposit',
                'received_at'    => $startOfMonth->copy()->addDays(5),
                'notes'          => 'Demo receipt – Corporate client payment',
                'reconciled'     => true,
            ],
            [
                'amount'         => 28000,
                'payment_method' => 'online_payment',
                'received_at'    => $startOfMonth->copy()->addDays(10),
                'notes'          => 'Demo receipt – Pending bank confirmation',
                'reconciled'     => false,
            ],
            [
                'amount'         => 54000,
                'payment_method' => 'bank_transfer',
                'received_at'    => $startOfMonth->copy()->addDays(15),
                'notes'          => 'Demo receipt – Regional distributor collection',
                'reconciled'     => false,
            ],
        ];

        foreach ($demoReceipts as $data) {
            // Create the base receipt (invoice is optional for demo).
            $receipt = Receipt::create([
                'invoice_id'     => $invoiceId,
                'amount'         => $data['amount'],
                'payment_method' => $data['payment_method'],
                'received_at'    => $data['received_at'],
                'notes'          => $data['notes'],
            ]);

            $receipt->reconciled = $data['reconciled'];
            $receipt->save();
        }
    }
}
