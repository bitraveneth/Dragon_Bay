<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Extra demo data specifically to make analytics
 * charts (monthly sales, statistics, targets) look
 * meaningful for walkthroughs.
 *
 * Safe to run multiple times.
 * Runs only in local/testing unless SEED_DEMO_ANALYTICS=true.
 * Uses deterministic upserts to avoid duplicate demo rows.
 */
class DemoAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        $allowOnThisEnv = app()->environment(['local', 'testing'])
            || filter_var((string) env('SEED_DEMO_ANALYTICS', false), FILTER_VALIDATE_BOOLEAN);

        if (! $allowOnThisEnv) {
            return;
        }

        $product = Product::where('sku', 'SAF-500ML-CTN')->first();
        if (! $product) {
            return;
        }

        $agents = Agent::whereIn('name', [
            'Dhaka North Dealer 01',
            'Dhaka South Dealer 01',
        ])->get();

        if ($agents->isEmpty()) {
            return;
        }

        $unitPrice = $product->base_price ?? 550;
        $deterministicQty = static function (string $key, int $min, int $max): int {
            $range = max(1, $max - $min + 1);
            $hash = hexdec(substr(hash('sha256', $key), 0, 8));

            return $min + ($hash % $range);
        };

        // Seed one or two orders per month for the current year
        // (used by the monthly sales bar chart).
        $year = Carbon::today()->year;

        for ($month = 1; $month <= 12; $month++) {
            // Use a consistent mid-month delivery date.
            $deliveryDate = Carbon::create($year, $month, 15);

            foreach ($agents as $agent) {
                $tag = 'DEMO-MONTH-' . $year . '-' . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . '-A' . $agent->id;
                $quantity = $deterministicQty($tag, 200, 800);
                $total    = $quantity * $unitPrice;

                $order = Order::updateOrCreate([
                    'agent_id'      => $agent->id,
                    'order_type'    => 'regular',
                    'delivery_date' => $deliveryDate->toDateString(),
                    'notes'         => $tag,
                ], [
                    'agent_id'               => $agent->id,
                    'order_type'             => 'regular',
                    'agent_reference'        => $agent->location_code ?? null,
                    'delivery_date'          => $deliveryDate->toDateString(),
                    'delivery_contact_name'  => $agent->name,
                    'delivery_contact_phone' => $agent->phone,
                    'delivery_address'       => trim(($agent->area ?? '') . ', ' . ($agent->zone ?? '')),
                    'status'                 => 'confirmed',
                    'total'                  => $total,
                    'commission_total'       => 0,
                    'notes'                  => $tag,
                    'is_credit_used'         => false,
                    'payment_mode'           => 'bank_transfer',
                ]);

                OrderItem::updateOrCreate([
                    'order_id'          => $order->id,
                    'product_id'        => $product->id,
                    'order_type'        => 'regular',
                ], [
                    'quantity'          => $quantity,
                    'unit_price'        => $unitPrice,
                    'order_type'        => 'regular',
                    'commission_rate'   => 0,
                    'commission_amount' => 0,
                ]);

                // Simple invoice for this order
                $netTotal   = $total;
                $vatRate    = 0.15;
                $vatAmount  = round($netTotal * $vatRate, 2);
                $invoiceNum = 'DEMO-' . $year . str_pad((string) $month, 2, '0', STR_PAD_LEFT) . '-A' . $agent->id;

                $invoice = Invoice::updateOrCreate([
                    'number'     => $invoiceNum,
                ], [
                    'order_id'   => $order->id,
                    'number'     => $invoiceNum,
                    'issued_at'  => $deliveryDate->toDateString(),
                    'due_at'     => $deliveryDate->copy()->addDays(7)->toDateString(),
                    'net_total'  => $netTotal,
                    'vat_amount' => $vatAmount,
                    'withholding'=> 0,
                    'status'     => 'issued',
                ]);

                InvoiceItem::updateOrCreate([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $product->id,
                ], [
                    'description' => $product->name,
                    'quantity'    => $quantity,
                    'unit_price'  => $unitPrice,
                    'line_total'  => $netTotal,
                ]);

                // Roughly 70% of invoices get a receipt to make
                // receipts / targets charts look realistic.
                if ($deterministicQty('R-' . $tag, 1, 100) <= 70) {
                    $paidAmount = round($netTotal + $vatAmount, 2);

                    Receipt::updateOrCreate([
                        'invoice_id'    => $invoice->id,
                        'received_at'   => $deliveryDate->copy()->addDays(3)->toDateString(),
                    ], [
                        'amount'        => $paidAmount,
                        'payment_method'=> 'bank_transfer',
                        'received_at'   => $deliveryDate->copy()->addDays(3)->toDateString(),
                        'notes'         => 'Demo analytics receipt',
                    ]);
                }
            }
        }

        // Additionally seed orders / receipts for the last 7 days so
        // the daily statistics chart never looks empty in a fresh demo.
        $today = Carbon::today();

        foreach (range(6, 0) as $offset) {
            $deliveryDate = $today->copy()->subDays($offset);

            foreach ($agents as $agent) {
                $tag = 'DEMO-DAY-' . $deliveryDate->format('Ymd') . '-A' . $agent->id;
                $quantity = $deterministicQty($tag, 100, 300);
                $total    = $quantity * $unitPrice;

                $order = Order::updateOrCreate([
                    'agent_id'      => $agent->id,
                    'order_type'    => 'regular',
                    'delivery_date' => $deliveryDate->toDateString(),
                    'notes'         => $tag,
                ], [
                    'agent_id'               => $agent->id,
                    'order_type'             => 'regular',
                    'agent_reference'        => $agent->location_code ?? null,
                    'delivery_date'          => $deliveryDate->toDateString(),
                    'delivery_contact_name'  => $agent->name,
                    'delivery_contact_phone' => $agent->phone,
                    'delivery_address'       => trim(($agent->area ?? '') . ', ' . ($agent->zone ?? '')),
                    'status'                 => 'confirmed',
                    'total'                  => $total,
                    'commission_total'       => 0,
                    'notes'                  => $tag,
                    'is_credit_used'         => false,
                    'payment_mode'           => 'cash',
                ]);

                OrderItem::updateOrCreate([
                    'order_id'          => $order->id,
                    'product_id'        => $product->id,
                    'order_type'        => 'regular',
                ], [
                    'quantity'          => $quantity,
                    'unit_price'        => $unitPrice,
                    'order_type'        => 'regular',
                    'commission_rate'   => 0,
                    'commission_amount' => 0,
                ]);

                $netTotal   = $total;
                $vatRate    = 0.15;
                $vatAmount  = round($netTotal * $vatRate, 2);
                $invoiceNum = 'DEMO-DAY-' . $deliveryDate->format('Ymd') . '-A' . $agent->id;

                $invoice = Invoice::updateOrCreate([
                    'number'     => $invoiceNum,
                ], [
                    'order_id'   => $order->id,
                    'number'     => $invoiceNum,
                    'issued_at'  => $deliveryDate->toDateString(),
                    'due_at'     => $deliveryDate->copy()->addDays(7)->toDateString(),
                    'net_total'  => $netTotal,
                    'vat_amount' => $vatAmount,
                    'withholding'=> 0,
                    'status'     => 'issued',
                ]);

                InvoiceItem::updateOrCreate([
                    'invoice_id'  => $invoice->id,
                    'product_id'  => $product->id,
                ], [
                    'description' => $product->name,
                    'quantity'    => $quantity,
                    'unit_price'  => $unitPrice,
                    'line_total'  => $netTotal,
                ]);

                // Assume full payment for these recent invoices
                Receipt::updateOrCreate([
                    'invoice_id'    => $invoice->id,
                    'received_at'   => $deliveryDate->copy()->addDays(1)->toDateString(),
                ], [
                    'amount'        => round($netTotal + $vatAmount, 2),
                    'payment_method'=> 'cash',
                    'received_at'   => $deliveryDate->copy()->addDays(1)->toDateString(),
                    'notes'         => 'Demo daily analytics receipt',
                ]);
            }
        }
    }
}
