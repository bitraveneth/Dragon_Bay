<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\CommissionReportController;
use App\Http\Controllers\Admin\CommissionSettlementController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PurchaseBillController;
use App\Http\Controllers\Admin\ProductionController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SalesTargetController;
use App\Models\Agent;
use App\Models\AgentAdvance;
use App\Models\AgentCommissionSettlement;
use App\Models\AgentCommissionRule;
use App\Models\AgentPriceList;
use App\Models\Campaign;
use App\Models\CreditNote;
use App\Models\CustomerGift;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductionRun;
use App\Models\PurchaseBill;
use App\Models\Receipt;
use App\Models\SalesTarget;
use App\Models\SalaryDistribution;
use App\Models\StockEntry;
use App\Models\Supplier;
use App\Notifications\SystemAlertNotification;
use App\Support\DatabaseBackupManager;
use App\Models\TaxClass;
use App\Models\User;
use App\Services\ErpNotificationService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class SystemCalculationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Automated calculation tests require pdo_sqlite (in-memory test database).');
        }

        $this->bootInMemorySqlite();
        $this->createMinimalSchema();
    }

    public function test_agent_order_calculates_total_commission_and_reserves_stock(): void
    {
        $agent = Agent::create([
            'name' => 'Agent One',
            'credit_limit' => 50000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Agent User',
            'email' => 'agent@example.test',
            'password' => 'secret',
            'role' => 'sales_officer',
            'agent_id' => $agent->id,
        ]);

        $product = Product::create([
            'sku' => 'SKU-500',
            'name' => 'Bottle 500ml',
            'base_price' => 100,
        ]);

        AgentPriceList::create([
            'agent_id' => $agent->id,
            'product_id' => $product->id,
            'price' => 120,
        ]);

        AgentCommissionRule::create([
            'agent_id' => $agent->id,
            'sku' => null,
            'type' => 'percentage',
            'value' => 5,
            'order_type' => 'regular',
            'frequency' => 'per_order',
        ]);

        StockEntry::create([
            'warehouse_id' => 1,
            'product_id' => $product->id,
            'quantity' => 6,
            'status' => 'available',
        ]);
        StockEntry::create([
            'warehouse_id' => 1,
            'product_id' => $product->id,
            'quantity' => 5,
            'status' => 'available',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/agent/orders', [
                'order_type' => 'regular',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 10,
                    ],
                ],
            ]);

        $response->assertCreated();

        $orderId = (int) $response->json('id');
        $this->assertGreaterThan(0, $orderId);

        $order = Order::findOrFail($orderId);
        $this->assertEquals(1200.0, (float) $order->total);
        $this->assertEquals(60.0, (float) $order->commission_total);
        $this->assertEquals('confirmed', $order->status);

        $item = OrderItem::where('order_id', $orderId)->firstOrFail();
        $this->assertEquals(120.0, (float) $item->unit_price);
        $this->assertEquals(60.0, (float) $item->commission_amount);
        $this->assertEquals(5.0, round((float) $item->commission_rate, 2));

        $availableQty = (float) StockEntry::where('product_id', $product->id)
            ->where('status', 'available')
            ->sum('quantity');
        $reservedQty = (float) StockEntry::where('order_id', $orderId)
            ->where('status', 'reserved')
            ->sum('quantity');

        $this->assertEquals(1.0, $availableQty);
        $this->assertEquals(10.0, $reservedQty);

        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $orderId,
            'status' => 'confirmed',
        ]);
    }

    public function test_invoice_generation_vat_withholding_and_ledger_posting(): void
    {
        $agent = Agent::create([
            'name' => 'Agent VAT',
            'credit_limit' => 100000,
            'withholding_rate' => 5,
            'is_active' => true,
        ]);

        $tax = TaxClass::create([
            'name' => 'Standard VAT',
            'rate' => 15,
        ]);

        $product = Product::create([
            'sku' => 'SKU-1L',
            'name' => 'Bottle 1L',
            'tax_class_id' => $tax->id,
            'base_price' => 20,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 20,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        $controller = app(FinanceController::class);
        $controller->createFromOrder($order->fresh());

        $invoice = Invoice::where('order_id', $order->id)->firstOrFail();

        $this->assertEquals(200.0, (float) $invoice->net_total);
        $this->assertEquals(30.0, (float) $invoice->vat_amount);
        $this->assertEquals(11.5, (float) $invoice->withholding);
        $this->assertEquals('issued', $invoice->status);

        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Accounts Receivable',
            'debit' => 230,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Sales Revenue',
            'debit' => 0,
            'credit' => 200,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'VAT Payable',
            'debit' => 0,
            'credit' => 30,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Withholding Tax Receivable',
            'debit' => 11.5,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Accounts Receivable',
            'debit' => 0,
            'credit' => 11.5,
        ]);
    }

    public function test_withholding_update_posts_balancing_ledger_adjustment(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-withholding-update@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Agent Withholding Update',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $tax = TaxClass::create([
            'name' => 'Withholding Update VAT',
            'rate' => 15,
        ]);

        $product = Product::create([
            'sku' => 'SKU-WHT-UPD-1',
            'name' => 'Withholding Update Product',
            'tax_class_id' => $tax->id,
            'base_price' => 100,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        $invoice = app(FinanceController::class)->ensureInvoiceForOrder($order->fresh());
        $this->assertNotNull($invoice);
        $this->assertEquals(115.0, (float) $invoice->gross_total);
        $this->assertEquals(115.0, (float) $invoice->outstanding);

        $this->actingAs($admin);
        app(FinanceController::class)->updateWithholding(new Request([
            'withholding' => 20,
        ]), $invoice->fresh());

        $invoice = $invoice->fresh();

        $this->assertEquals(20.0, (float) $invoice->withholding);
        $this->assertEquals(95.0, (float) $invoice->cash_total);
        $this->assertEquals(95.0, (float) $invoice->outstanding);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Withholding Tax Receivable',
            'debit' => 20,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Accounts Receivable',
            'debit' => 0,
            'credit' => 20,
        ]);
    }

    public function test_invoice_outstanding_status_and_finance_alert_calculation(): void
    {
        $invoice = Invoice::create([
            'number' => 'INV-TEST-001',
            'issued_at' => now()->toDateString(),
            'net_total' => 1000,
            'vat_amount' => 150,
            'withholding' => 100,
            'status' => 'issued',
        ]);

        Receipt::create([
            'invoice_id' => $invoice->id,
            'amount' => 200,
            'received_at' => now()->toDateString(),
        ]);

        CreditNote::create([
            'invoice_id' => $invoice->id,
            'number' => 'CN-TEST-001',
            'issued_at' => now()->toDateString(),
            'amount' => 300,
        ]);

        $invoice->recalculateStatus();
        $invoice->refresh();

        $this->assertEquals(1150.0, $invoice->gross_total);
        $this->assertEquals(1050.0, $invoice->cash_total);
        $this->assertEquals(200.0, $invoice->receipts_total);
        $this->assertEquals(300.0, $invoice->credits_total);
        $this->assertEquals(550.0, $invoice->outstanding);
        $this->assertEquals('adjusted', $invoice->status);

        // Add one more receipt to settle outstanding and verify paid status.
        Receipt::create([
            'invoice_id' => $invoice->id,
            'amount' => 550,
            'received_at' => now()->toDateString(),
        ]);
        $invoice->recalculateStatus();
        $invoice->refresh();

        $this->assertEquals(0.0, $invoice->outstanding);
        $this->assertEquals('paid', $invoice->status);

        // Finance alert should include outstanding of other open invoices.
        $open = Invoice::create([
            'number' => 'INV-TEST-002',
            'issued_at' => now()->toDateString(),
            'net_total' => 300,
            'vat_amount' => 45,
            'withholding' => 15,
            'status' => 'issued',
        ]);
        Receipt::create([
            'invoice_id' => $open->id,
            'amount' => 30,
            'received_at' => now()->toDateString(),
        ]);

        $alerts = app(ErpNotificationService::class)->buildSystemAlerts();
        $financeAlert = collect($alerts)->first(fn (array $alert) => ($alert['source'] ?? null) === 'Finance');

        $this->assertNotNull($financeAlert);
        $this->assertSame('Outstanding receivables of BDT 300.00', $financeAlert['message']);
    }

    public function test_start_to_end_calculation_pipeline(): void
    {
        // 1) Master + procurement stock ready (simulated by available stock entries).
        $agent = Agent::create([
            'name' => 'Agent EndToEnd',
            'credit_limit' => 200000,
            'withholding_rate' => 5,
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Agent EndToEnd User',
            'email' => 'agent-e2e@example.test',
            'password' => 'secret',
            'role' => 'sales_officer',
            'agent_id' => $agent->id,
        ]);

        $tax = TaxClass::create([
            'name' => 'BD Standard VAT',
            'rate' => 15,
        ]);

        $product = Product::create([
            'sku' => 'SKU-E2E-1',
            'name' => 'Bottle 1L E2E',
            'tax_class_id' => $tax->id,
            'base_price' => 100,
        ]);

        AgentPriceList::create([
            'agent_id' => $agent->id,
            'product_id' => $product->id,
            'price' => 120,
        ]);

        AgentCommissionRule::create([
            'agent_id' => $agent->id,
            'sku' => null,
            'type' => 'percentage',
            'value' => 5,
            'order_type' => 'regular',
            'frequency' => 'per_order',
        ]);

        StockEntry::create([
            'warehouse_id' => 1,
            'product_id' => $product->id,
            'quantity' => 50,
            'status' => 'available',
        ]);

        // 2) Sales order with commission + stock reservation.
        $orderResponse = $this->actingAs($user, 'sanctum')
            ->postJson('/api/agent/orders', [
                'order_type' => 'regular',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 20],
                ],
            ]);
        $orderResponse->assertCreated();

        $order = Order::findOrFail((int) $orderResponse->json('id'));
        $this->assertEquals(2400.0, (float) $order->total);
        $this->assertEquals(120.0, (float) $order->commission_total);

        $this->assertEquals(30.0, (float) StockEntry::where('product_id', $product->id)->where('status', 'available')->sum('quantity'));
        $this->assertEquals(20.0, (float) StockEntry::where('order_id', $order->id)->where('status', 'reserved')->sum('quantity'));

        // 3) Delivery finalized (simulate delivered), then invoice from delivered qty.
        $order->update(['status' => 'delivered']);
        app(FinanceController::class)->createFromOrder($order->fresh());
        $invoice = Invoice::where('order_id', $order->id)->firstOrFail();

        // Invoice math:
        // net = 20 * 120 = 2400
        // vat = 15% = 360
        // gross = 2760
        // withholding = 5% of gross = 138
        // cash total = 2622
        $this->assertEquals(2400.0, (float) $invoice->net_total);
        $this->assertEquals(360.0, (float) $invoice->vat_amount);
        $this->assertEquals(138.0, (float) $invoice->withholding);
        $this->assertEquals(2760.0, (float) $invoice->gross_total);
        $this->assertEquals(2622.0, (float) $invoice->cash_total);

        // 4) Collection phase: receipt + adjustment via credit note.
        Receipt::create([
            'invoice_id' => $invoice->id,
            'amount' => 1000,
            'received_at' => now()->toDateString(),
        ]);
        CreditNote::create([
            'invoice_id' => $invoice->id,
            'order_id' => $order->id,
            'number' => 'CN-E2E-1',
            'issued_at' => now()->toDateString(),
            'amount' => 200,
        ]);
        $invoice->recalculateStatus();
        $invoice->refresh();

        // outstanding = 2622 - 1000 - 200 = 1422
        $this->assertEquals(1422.0, (float) $invoice->outstanding);
        $this->assertEquals('adjusted', $invoice->status);

        // 5) Commission settlement generation (month-end cycle).
        app(CommissionSettlementController::class)->generate(
            new Request(['month' => now()->format('Y-m')])
        );

        $settlement = AgentCommissionSettlement::where('agent_id', $agent->id)->firstOrFail();
        $this->assertEquals(2400.0, (float) $settlement->sales_total);
        $this->assertEquals(120.0, (float) $settlement->commission_total);
        $this->assertEquals('open', $settlement->status);

        // 6) Final settlement of receivable.
        Receipt::create([
            'invoice_id' => $invoice->id,
            'amount' => 1422,
            'received_at' => now()->toDateString(),
        ]);
        $invoice->recalculateStatus();
        $invoice->refresh();

        $this->assertEquals(0.0, (float) $invoice->outstanding);
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_invoice_generation_uses_delivered_quantity_when_delivery_items_exist(): void
    {
        $agent = Agent::create([
            'name' => 'Agent Delivered Qty',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $tax = TaxClass::create([
            'name' => 'Standard VAT',
            'rate' => 15,
        ]);

        $product = Product::create([
            'sku' => 'SKU-DLV-1',
            'name' => 'Bottle Delivered Qty',
            'tax_class_id' => $tax->id,
            'base_price' => 20,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 20,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'status' => 'delivered',
        ]);

        DeliveryItem::create([
            'delivery_id' => $delivery->id,
            'order_item_id' => $orderItem->id,
            'product_id' => $product->id,
            'qty_dispatched' => 10,
            'qty_delivered' => 7,
            'qty_short' => 3,
            'qty_damaged' => 0,
        ]);

        app(FinanceController::class)->createFromOrder($order->fresh());

        $invoice = Invoice::where('order_id', $order->id)->firstOrFail();
        $invoiceItem = $invoice->items()->firstOrFail();

        $this->assertEquals(140.0, (float) $invoice->net_total);
        $this->assertEquals(21.0, (float) $invoice->vat_amount);
        $this->assertEquals(7, (int) $invoiceItem->quantity);
        $this->assertEquals(140.0, (float) $invoiceItem->line_total);
    }

    public function test_split_delivery_items_for_one_order_line_are_aggregated_everywhere(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-split-delivery@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Agent Split Delivery',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $warehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'Returns Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $tax = TaxClass::create([
            'name' => 'Split Delivery VAT',
            'rate' => 15,
        ]);

        $product = Product::create([
            'sku' => 'SKU-DLV-SPLIT-1',
            'name' => 'Split Delivery Product',
            'tax_class_id' => $tax->id,
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 20,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 20,
            'order_type' => 'regular',
            'commission_rate' => 10,
            'commission_amount' => 20,
        ]);

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'status' => 'delivered',
        ]);

        DeliveryItem::create([
            'delivery_id' => $delivery->id,
            'order_item_id' => $orderItem->id,
            'product_id' => $product->id,
            'qty_dispatched' => 6,
            'qty_delivered' => 4,
            'qty_short' => 2,
            'qty_damaged' => 0,
        ]);

        DeliveryItem::create([
            'delivery_id' => $delivery->id,
            'order_item_id' => $orderItem->id,
            'product_id' => $product->id,
            'qty_dispatched' => 4,
            'qty_delivered' => 3,
            'qty_short' => 1,
            'qty_damaged' => 0,
        ]);

        app(FinanceController::class)->createFromOrder($order->fresh());

        $invoice = Invoice::where('order_id', $order->id)->firstOrFail();
        $invoiceItem = $invoice->items()->firstOrFail();

        $this->assertEquals(140.0, (float) $invoice->net_total);
        $this->assertEquals(21.0, (float) $invoice->vat_amount);
        $this->assertEquals(7, (int) $invoiceItem->quantity);
        $this->assertEquals(140.0, (float) $invoiceItem->line_total);

        app(CommissionSettlementController::class)->generate(
            new Request(['month' => now()->format('Y-m')])
        );

        $settlement = AgentCommissionSettlement::where('agent_id', $agent->id)->firstOrFail();

        $this->assertEquals(140.0, (float) $settlement->sales_total);
        $this->assertEquals(14.0, (float) $settlement->commission_total);

        $this->actingAs($admin);
        app(\App\Http\Controllers\Admin\CustomerReturnController::class)->store(new Request([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
            'quantity' => 7,
            'notes' => null,
        ]));

        $this->assertDatabaseHas('stock_movements', [
            'order_id' => $order->id,
            'type' => 'customer-return',
            'quantity' => 7,
        ]);

        app(\App\Http\Controllers\Admin\CustomerReturnController::class)->store(new Request([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
            'quantity' => 1,
            'notes' => null,
        ]));

        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_delivery_update_rejects_split_items_that_exceed_order_quantity_in_total(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-delivery-overage@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Agent Delivery Overage',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-DLV-OVER-1',
            'name' => 'Delivery Overage Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 20,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'picked',
            'total' => 0,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 20,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.deliveries.edit', $delivery))
            ->patch(route('admin.deliveries.update', $delivery), [
                'status' => 'scheduled',
                'items' => [
                    [
                        'order_item_id' => $orderItem->id,
                        'product_id' => $product->id,
                        'qty_dispatched' => 6,
                        'qty_delivered' => 6,
                        'qty_short' => 0,
                        'qty_damaged' => 0,
                    ],
                    [
                        'order_item_id' => $orderItem->id,
                        'product_id' => $product->id,
                        'qty_dispatched' => 6,
                        'qty_delivered' => 6,
                        'qty_short' => 0,
                        'qty_damaged' => 0,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('delivery_items', 0);
    }

    public function test_commission_settlement_only_counts_delivered_and_invoiced_orders(): void
    {
        $agent = Agent::create([
            'name' => 'Agent Settled',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-COM-1',
            'name' => 'Commission Product',
            'base_price' => 10,
        ]);

        $deliveredOrder = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $deliveredOrder->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_price' => 10,
            'order_type' => 'regular',
            'commission_amount' => 5,
        ]);

        app(FinanceController::class)->createFromOrder($deliveredOrder->fresh());

        $openOrder = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'confirmed',
            'total' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        OrderItem::create([
            'order_id' => $openOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 10,
            'order_type' => 'regular',
            'commission_amount' => 20,
        ]);

        app(CommissionSettlementController::class)->generate(
            new Request(['month' => now()->format('Y-m')])
        );

        $settlement = AgentCommissionSettlement::where('agent_id', $agent->id)->firstOrFail();

        $this->assertEquals(50.0, (float) $settlement->sales_total);
        $this->assertEquals(5.0, (float) $settlement->commission_total);
    }

    public function test_commission_reports_use_realized_delivery_quantities(): void
    {
        $agent = Agent::create([
            'name' => 'Agent Realized',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-REALIZED-1',
            'name' => 'Realized Quantity Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 20,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 20,
            'order_type' => 'regular',
            'commission_rate' => 10,
            'commission_amount' => 20,
        ]);

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'status' => 'delivered',
        ]);

        DeliveryItem::create([
            'delivery_id' => $delivery->id,
            'order_item_id' => $orderItem->id,
            'product_id' => $product->id,
            'qty_dispatched' => 10,
            'qty_delivered' => 7,
            'qty_short' => 3,
            'qty_damaged' => 0,
        ]);

        app(FinanceController::class)->createFromOrder($order->fresh());
        app(CommissionSettlementController::class)->generate(
            new Request(['month' => now()->format('Y-m')])
        );

        $settlement = AgentCommissionSettlement::where('agent_id', $agent->id)->firstOrFail();

        $this->assertEquals(140.0, (float) $settlement->sales_total);
        $this->assertEquals(14.0, (float) $settlement->commission_total);

        $response = app(CommissionReportController::class)->export(
            Request::create('/admin/commissions/export', 'GET', ['month' => now()->format('Y-m')])
        );

        ob_start();
        $response->sendContent();
        $csv = ob_get_clean();

        $this->assertStringContainsString('140.00', $csv);
        $this->assertStringContainsString('14.00', $csv);
    }

    public function test_monthly_tiered_commission_uses_highest_matching_rule(): void
    {
        $agent = Agent::create([
            'name' => 'Agent Tiered',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-TIER-1',
            'name' => 'Tier Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 20,
        ]);

        AgentCommissionRule::create([
            'agent_id' => $agent->id,
            'sku' => null,
            'type' => 'percentage',
            'value' => 3,
            'order_type' => 'regular',
            'frequency' => 'monthly',
            'threshold_min' => 0,
            'threshold_max' => 999.99,
        ]);

        AgentCommissionRule::create([
            'agent_id' => $agent->id,
            'sku' => null,
            'type' => 'percentage',
            'value' => 5,
            'order_type' => 'regular',
            'frequency' => 'monthly',
            'threshold_min' => 1000,
            'threshold_max' => null,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 50,
            'unit_price' => 20,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        app(FinanceController::class)->createFromOrder($order->fresh());
        app(CommissionSettlementController::class)->generate(
            new Request(['month' => now()->format('Y-m')])
        );

        $settlement = AgentCommissionSettlement::where('agent_id', $agent->id)->firstOrFail();

        $this->assertEquals(1000.0, (float) $settlement->sales_total);
        $this->assertEquals(50.0, (float) $settlement->commission_total);
    }

    public function test_agent_advance_auto_applies_to_invoice_and_reduces_outstanding(): void
    {
        $agent = Agent::create([
            'name' => 'Agent Advance',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        app(\App\Http\Controllers\Admin\AgentAdvanceController::class)->store(new Request([
            'agent_id' => $agent->id,
            'amount' => 120,
            'advanced_at' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
            'reference' => 'ADV-120',
        ]));

        $advance = AgentAdvance::firstOrFail();
        $this->assertEquals(120.0, (float) $advance->available_amount);
        $this->assertDatabaseHas('ledger_entries', [
            'account' => 'Agent Advances',
            'credit' => 120,
        ]);

        $product = Product::create([
            'sku' => 'SKU-ADV-1',
            'name' => 'Advance Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 30,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 30,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        $invoice = app(FinanceController::class)->ensureInvoiceForOrder($order->fresh());

        $this->assertNotNull($invoice);
        $this->assertEquals(120.0, (float) $invoice->advances_applied_total);
        $this->assertEquals(180.0, (float) $invoice->outstanding);
        $this->assertDatabaseHas('agent_advance_applications', [
            'invoice_id' => $invoice->id,
            'amount' => 120,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Agent Advances',
            'debit' => 120,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Accounts Receivable',
            'credit' => 120,
        ]);
    }

    public function test_future_dated_agent_advance_posts_on_effective_date_and_does_not_apply_early(): void
    {
        $agent = Agent::create([
            'name' => 'Future Advance Agent',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $advanceDate = now()->addDay()->toDateString();

        app(\App\Http\Controllers\Admin\AgentAdvanceController::class)->store(new Request([
            'agent_id' => $agent->id,
            'amount' => 150,
            'advanced_at' => $advanceDate,
            'payment_method' => 'bank_transfer',
            'reference' => 'ADV-FUTURE-150',
        ]));

        $advance = AgentAdvance::firstOrFail();

        $ledgerEntry = DB::table('ledger_entries')
            ->where('account', 'Bank')
            ->where('description', 'like', 'Agent advance #' . $advance->id . '%')
            ->first();

        $this->assertNotNull($ledgerEntry);
        $this->assertSame($advanceDate, substr((string) $ledgerEntry->created_at, 0, 10));

        $product = Product::create([
            'sku' => 'SKU-ADV-FUTURE-1',
            'name' => 'Future Advance Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 30,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 30,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        $invoice = app(FinanceController::class)->ensureInvoiceForOrder($order->fresh())->fresh();

        $this->assertNotNull($invoice);
        $this->assertEquals(0.0, (float) $invoice->advances_applied_total);
        $this->assertEquals(300.0, (float) $invoice->outstanding);
        $this->assertDatabaseCount('agent_advance_applications', 0);
    }

    public function test_special_order_types_apply_sample_return_and_bulk_rules(): void
    {
        $agent = Agent::create([
            'name' => 'Agent Order Types',
            'credit_limit' => 1000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-ORDER-TYPES',
            'name' => 'Order Type Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 50,
        ]);

        StockEntry::create([
            'warehouse_id' => 1,
            'product_id' => $product->id,
            'quantity' => 20,
            'status' => 'available',
        ]);

        $controller = app(OrderController::class);

        $controller->store(new Request([
            'agent_id' => $agent->id,
            'order_type' => 'sample',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 50],
            ],
        ]));

        $sampleOrder = Order::latest('id')->firstOrFail();
        $this->assertEquals('sample', $sampleOrder->order_type);
        $this->assertEquals(0.0, (float) $sampleOrder->total);
        $this->assertEquals(0.0, (float) $sampleOrder->commission_total);
        $this->assertEquals(2.0, (float) StockEntry::where('order_id', $sampleOrder->id)->sum('quantity'));
        $sampleOrder->update(['status' => 'delivered']);
        $this->assertNull(app(FinanceController::class)->ensureInvoiceForOrder($sampleOrder->fresh()));

        $controller->store(new Request([
            'agent_id' => $agent->id,
            'order_type' => 'return',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 50],
            ],
        ]));

        $returnOrder = Order::latest('id')->firstOrFail();
        $this->assertEquals('return', $returnOrder->order_type);
        $this->assertEquals(0.0, (float) $returnOrder->total);
        $this->assertEquals(0.0, (float) StockEntry::where('order_id', $returnOrder->id)->sum('quantity'));

        $controller->store(new Request([
            'agent_id' => $agent->id,
            'order_type' => 'bulk',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4, 'unit_price' => 50],
            ],
        ]));

        $bulkOrder = Order::latest('id')->firstOrFail();
        $this->assertEquals('bulk', $bulkOrder->order_type);
        $this->assertSame('credit', $bulkOrder->payment_mode);
        $this->assertTrue((bool) $bulkOrder->is_credit_used);
    }

    public function test_delivery_completion_auto_creates_invoice_for_billable_orders(): void
    {
        $agent = Agent::create([
            'name' => 'Agent Delivery Invoice',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $tax = TaxClass::create([
            'name' => 'Delivery VAT',
            'rate' => 15,
        ]);

        $product = Product::create([
            'sku' => 'SKU-DELIVERY-AUTO',
            'name' => 'Delivery Invoice Product',
            'tax_class_id' => $tax->id,
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 20,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'picked',
            'total' => 100,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_price' => 20,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        StockEntry::create([
            'order_id' => $order->id,
            'warehouse_id' => 1,
            'product_id' => $product->id,
            'quantity' => 5,
            'status' => 'reserved',
        ]);

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'status' => 'scheduled',
        ]);

        app(DeliveryController::class)->update(new Request([
            'status' => 'delivered',
            'items' => [
                [
                    'order_item_id' => $orderItem->id,
                    'product_id' => $product->id,
                    'qty_dispatched' => 5,
                    'qty_delivered' => 5,
                    'qty_short' => 0,
                    'qty_damaged' => 0,
                ],
            ],
        ]), $delivery);

        $invoice = Invoice::where('order_id', $order->id)->first();

        $this->assertNotNull($invoice);
        $this->assertEquals(100.0, (float) $invoice->net_total);
        $this->assertEquals(15.0, (float) $invoice->vat_amount);
    }

    public function test_purchase_bill_records_input_vat_and_accounts_payable_total(): void
    {
        $supplier = Supplier::create([
            'name' => 'VAT Supplier',
        ]);

        $tax = TaxClass::create([
            'name' => 'Input VAT 15',
            'rate' => 15,
        ]);

        $product = Product::create([
            'sku' => 'SKU-BILL-VAT',
            'name' => 'Purchase VAT Product',
            'tax_class_id' => $tax->id,
            'product_type' => 'raw_material',
            'base_price' => 100,
        ]);

        app(PurchaseBillController::class)->store(new Request([
            'supplier_id' => $supplier->id,
            'bill_date' => now()->toDateString(),
            'items' => [
                [
                    'description' => 'Raw material',
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'unit_price' => 100,
                    'vat_rate' => 15,
                ],
            ],
        ]));

        $bill = \App\Models\PurchaseBill::firstOrFail();
        $billItem = $bill->items()->firstOrFail();

        $this->assertEquals(1000.0, (float) $bill->net_total);
        $this->assertEquals(150.0, (float) $bill->vat_amount);
        $this->assertEquals(15.0, (float) $billItem->vat_rate);
        $this->assertEquals(150.0, (float) $billItem->vat_amount);
        $this->assertDatabaseHas('ledger_entries', [
            'account' => 'Purchases',
            'debit' => 1000,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'account' => 'Input VAT',
            'debit' => 150,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'account' => 'Accounts Payable',
            'credit' => 1150,
        ]);
    }

    public function test_purchase_bill_persists_warehouse_reference_on_create_and_update(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-bill-warehouse@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $supplier = Supplier::create([
            'name' => 'Warehouse Supplier',
        ]);

        $warehouseOne = DB::table('warehouses')->insertGetId([
            'name' => 'Bill Warehouse 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $warehouseTwo = DB::table('warehouses')->insertGetId([
            'name' => 'Bill Warehouse 2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.bills.store'), [
                'supplier_id' => $supplier->id,
                'bill_date' => now()->toDateString(),
                'warehouse_id' => $warehouseOne,
                'items' => [
                    [
                        'description' => 'Warehouse-bound item',
                        'quantity' => 2,
                        'unit_price' => 50,
                        'vat_rate' => 0,
                    ],
                ],
            ]);

        $response->assertSessionHasNoErrors();

        $bill = PurchaseBill::firstOrFail();
        $this->assertSame($warehouseOne, (int) $bill->warehouse_id);

        $updateResponse = $this->actingAs($admin)
            ->put(route('admin.bills.update', $bill), [
                'supplier_id' => $supplier->id,
                'bill_date' => now()->toDateString(),
                'warehouse_id' => $warehouseTwo,
                'items' => [
                    [
                        'description' => 'Warehouse-bound item updated',
                        'quantity' => 2,
                        'unit_price' => 60,
                        'vat_rate' => 0,
                    ],
                ],
            ]);

        $updateResponse->assertSessionHasNoErrors();
        $this->assertSame($warehouseTwo, (int) $bill->fresh()->warehouse_id);
    }

    public function test_commission_payout_accounting_posts_accrual_and_payment_entries(): void
    {
        $agent = Agent::create([
            'name' => 'Agent Settlement Pay',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $settlement = AgentCommissionSettlement::create([
            'agent_id' => $agent->id,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'sales_total' => 1000,
            'commission_total' => 100,
            'status' => 'open',
        ]);

        $controller = app(CommissionSettlementController::class);
        $controller->updateStatus(new Request(['status' => 'approved']), $settlement->fresh());
        $controller->updateStatus(new Request(['status' => 'paid', 'payment_method' => 'bank_transfer', 'payment_reference' => 'PAYOUT-1']), $settlement->fresh());

        $settlement->refresh();

        $this->assertSame('paid', $settlement->status);
        $this->assertNotNull($settlement->accrued_at);
        $this->assertNotNull($settlement->paid_at);
        $this->assertDatabaseHas('ledger_entries', [
            'account' => 'Commission Expense',
            'debit' => 100,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'account' => 'Commission Payable',
            'credit' => 100,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'account' => 'Commission Payable',
            'debit' => 100,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'account' => 'Bank',
            'credit' => 100,
        ]);
    }

    public function test_credit_note_reverses_output_vat_and_vat_report_totals(): void
    {
        $agent = Agent::create([
            'name' => 'Agent Credit VAT',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $tax = TaxClass::create([
            'name' => 'Output VAT 15',
            'rate' => 15,
        ]);

        $product = Product::create([
            'sku' => 'SKU-CN-VAT',
            'name' => 'Credit VAT Product',
            'tax_class_id' => $tax->id,
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 10,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 10,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        $invoice = app(FinanceController::class)->ensureInvoiceForOrder($order->fresh());
        $this->assertNotNull($invoice);

        app(FinanceController::class)->storeCreditNote(new Request([
            'amount' => 57.5,
            'reason' => 'Partial return',
        ]), $invoice->fresh());

        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Sales Returns',
            'debit' => 50,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'VAT Payable',
            'debit' => 7.5,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Accounts Receivable',
            'credit' => 57.5,
        ]);

        $view = app(ReportController::class)->vat(
            Request::create('/admin/reports/vat', 'GET', ['month' => now()->format('Y-m')])
        );

        $this->assertEquals(7.5, (float) $view->getData()['outputVat']);
        $this->assertEquals(50.0, (float) $view->getData()['totals']['taxable']);
        $this->assertEquals(7.5, (float) $view->getData()['totals']['vat']);
    }

    public function test_cross_period_credit_notes_do_not_reduce_earlier_period_reports_targets_or_dashboard(): void
    {
        Carbon::setTestNow('2026-04-06 12:00:00');

        try {
            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin-cross-period-credit@example.test',
                'password' => 'secret',
                'role' => 'admin',
            ]);

            $agent = Agent::create([
                'name' => 'Agent Cross Period',
                'credit_limit' => 100000,
                'withholding_rate' => 0,
                'is_active' => true,
            ]);

            $order = Order::create([
                'agent_id' => $agent->id,
                'order_type' => 'regular',
                'status' => 'delivered',
                'total' => 0,
            ]);

            $invoice = Invoice::create([
                'order_id' => $order->id,
                'number' => 'INV-PERIOD-001',
                'issued_at' => '2026-03-15',
                'net_total' => 100,
                'vat_amount' => 15,
                'withholding' => 0,
                'status' => 'issued',
            ]);

            CreditNote::create([
                'invoice_id' => $invoice->id,
                'order_id' => $order->id,
                'number' => 'CN-PERIOD-APRIL-001',
                'issued_at' => '2026-04-02',
                'amount' => 57.5,
            ]);

            SalesTarget::create([
                'agent_id' => $agent->id,
                'employee_id' => null,
                'period_start' => '2026-03-01',
                'period_end' => '2026-03-31',
                'target_value' => 200,
            ]);

            $vatView = app(ReportController::class)->vat(
                Request::create('/admin/reports/vat', 'GET', ['month' => '2026-03'])
            );

            $this->assertEquals(100.0, (float) $vatView->getData()['totals']['taxable']);
            $this->assertEquals(15.0, (float) $vatView->getData()['totals']['vat']);

            $agentPerformanceView = app(ReportController::class)->agentPerformance(
                Request::create('/admin/reports/agents', 'GET', [
                    'from' => '2026-03-01',
                    'to' => '2026-03-31',
                ])
            );

            $agentRow = $agentPerformanceView->getData()['rows']->first();

            $this->assertEquals(115.0, (float) $agentRow['invoiced']);
            $this->assertEquals(0.0, (float) $agentRow['credits']);
            $this->assertEquals(115.0, (float) $agentRow['net_sales']);
            $this->assertEquals(115.0, (float) $agentRow['outstanding']);

            $productionView = app(ReportController::class)->productionSummary(
                Request::create('/admin/reports/production', 'GET', [
                    'from' => '2026-03-01',
                    'to' => '2026-03-31',
                ])
            );

            $this->assertEquals(100.0, (float) $productionView->getData()['salesTotal']);

            $targetView = app(SalesTargetController::class)->index(
                Request::create('/admin/sales-targets', 'GET', ['month' => '2026-03'])
            );

            $targetRow = $targetView->getData()['targets']->items()[0];

            $this->assertEquals(100.0, (float) $targetRow['achieved']);
            $this->assertEquals(100.0, (float) $targetRow['remaining']);

            $dashboardResponse = $this->actingAs($admin)->get(route('admin.dashboard', [
                'target_month' => '2026-03',
                'sales_range' => 3,
            ]));

            $dashboardResponse->assertOk();
            $dashboardResponse->assertViewHas('monthlyAchieved', 100.0);
            $dashboardResponse->assertViewHas('monthlyRevenue', function (array $monthlyRevenue) {
                $values = array_map('floatval', $monthlyRevenue);

                return $values === [0.0, 100.0, 0.0];
            });
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_reports_dashboard_profit_estimate_includes_cogs_and_commissions(): void
    {
        Carbon::setTestNow('2026-04-06 12:00:00');

        try {
            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin-reports-dashboard@example.test',
                'password' => 'secret',
                'role' => 'admin',
            ]);

            $agent = Agent::create([
                'name' => 'Agent Dashboard Profit',
                'credit_limit' => 100000,
                'withholding_rate' => 0,
                'is_active' => true,
            ]);

            $product = Product::create([
                'sku' => 'SKU-DASH-PROFIT',
                'name' => 'Dashboard Profit Product',
                'base_price' => 10,
                'product_type' => 'finished',
                'is_active' => true,
            ]);

            $order = Order::create([
                'agent_id' => $agent->id,
                'order_type' => 'regular',
                'status' => 'delivered',
                'total' => 0,
            ]);

            $invoice = Invoice::create([
                'order_id' => $order->id,
                'number' => 'INV-DASH-001',
                'issued_at' => '2026-04-02',
                'net_total' => 100,
                'vat_amount' => 15,
                'withholding' => 0,
                'status' => 'issued',
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $product->id,
                'description' => 'Dashboard Profit Product',
                'quantity' => 10,
                'unit_price' => 10,
                'line_total' => 100,
            ]);

            ProductionRun::create([
                'product_id' => $product->id,
                'warehouse_id' => 1,
                'quantity' => 10,
                'material_unit_cost' => 6,
                'qc_status' => 'approved',
                'created_at' => '2026-04-01 08:00:00',
                'updated_at' => '2026-04-01 08:00:00',
            ]);

            LedgerEntry::create([
                'account' => 'Commission Expense',
                'description' => 'Dashboard commission accrual',
                'debit' => 8,
                'credit' => 0,
                'created_at' => '2026-04-03 09:00:00',
                'updated_at' => '2026-04-03 09:00:00',
            ]);

            Expense::create([
                'date' => '2026-04-04',
                'category' => 'general',
                'amount' => 7,
                'status' => Expense::STATUS_RECORDED,
            ]);

            SalaryDistribution::create([
                'employee_id' => null,
                'period_start' => '2026-04-01',
                'period_end' => '2026-04-30',
                'base_salary' => 9,
                'bonus' => 0,
                'ta_allowances' => 0,
                'da_allowances' => 0,
                'commission' => 0,
            ]);

            $response = $this->actingAs($admin)->get(route('admin.reports.dashboard'));

            $response->assertOk();
            $response->assertViewHas('grossRevenue', 100.0);
            $response->assertViewHas('cogsEstimate', 60.0);
            $response->assertViewHas('commissionsTotal', 8.0);
            $response->assertViewHas('totalExpenses', 7.0);
            $response->assertViewHas('totalPayroll', 9.0);
            $response->assertViewHas('netProfitEstimate', 16.0);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_sales_targets_show_achieved_value_from_invoiced_sales(): void
    {
        $agent = Agent::create([
            'name' => 'Agent Target',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-TARGET-1',
            'name' => 'Target Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 25,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 20,
            'unit_price' => 25,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        app(FinanceController::class)->createFromOrder($order->fresh());

        SalesTarget::create([
            'agent_id' => $agent->id,
            'employee_id' => null,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'target_value' => 1000,
        ]);

        $view = app(SalesTargetController::class)->index(
            Request::create('/admin/sales-targets', 'GET', ['month' => now()->format('Y-m')])
        );

        $rows = $view->getData()['targets'];
        $first = $rows->items()[0];

        $this->assertEquals(500.0, $first['achieved']);
        $this->assertEquals(500.0, $first['remaining']);
    }

    public function test_sales_targets_use_completed_visit_plans_before_zone_and_net_credit_notes(): void
    {
        $employee = Employee::create([
            'name' => 'Target Employee',
            'work_zone' => 'North',
        ]);

        $plannedAgent = Agent::create([
            'name' => 'Planned Agent',
            'zone' => 'North',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $otherZoneAgent = Agent::create([
            'name' => 'Other Zone Agent',
            'zone' => 'North',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-TARGET-ZONE',
            'name' => 'Target Zone Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 10,
        ]);

        $plannedOrder = Order::create([
            'agent_id' => $plannedAgent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $plannedOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 10,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        $plannedInvoice = app(FinanceController::class)->ensureInvoiceForOrder($plannedOrder->fresh());
        $this->assertNotNull($plannedInvoice);

        CreditNote::create([
            'invoice_id' => $plannedInvoice->id,
            'order_id' => $plannedOrder->id,
            'number' => 'CN-TARGET-001',
            'issued_at' => now()->toDateString(),
            'amount' => 40,
        ]);

        $otherOrder = Order::create([
            'agent_id' => $otherZoneAgent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $otherOrder->id,
            'product_id' => $product->id,
            'quantity' => 20,
            'unit_price' => 10,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        app(FinanceController::class)->ensureInvoiceForOrder($otherOrder->fresh());

        DB::table('visit_plans')->insert([
            'employee_id' => $employee->id,
            'agent_id' => $plannedAgent->id,
            'date' => now()->toDateString(),
            'status' => 'completed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        SalesTarget::create([
            'employee_id' => $employee->id,
            'agent_id' => null,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'target_value' => 500,
        ]);

        $view = app(SalesTargetController::class)->index(
            Request::create('/admin/sales-targets', 'GET', ['month' => now()->format('Y-m')])
        );

        $rows = $view->getData()['targets'];
        $first = $rows->items()[0];

        $this->assertEquals(60.0, (float) $first['achieved']);
        $this->assertEquals(440.0, (float) $first['remaining']);
    }

    public function test_sales_targets_ignore_planned_visits_when_visit_plans_exist(): void
    {
        $employee = Employee::create([
            'name' => 'Planned Visit Employee',
            'work_zone' => 'North',
        ]);

        $plannedAgent = Agent::create([
            'name' => 'Planned Only Agent',
            'zone' => 'North',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $otherZoneAgent = Agent::create([
            'name' => 'Zone Fallback Agent',
            'zone' => 'North',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-TARGET-PLANNED',
            'name' => 'Planned Visit Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 10,
        ]);

        $plannedOrder = Order::create([
            'agent_id' => $plannedAgent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $plannedOrder->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 10,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        $plannedInvoice = app(FinanceController::class)->ensureInvoiceForOrder($plannedOrder->fresh());
        $this->assertNotNull($plannedInvoice);

        CreditNote::create([
            'invoice_id' => $plannedInvoice->id,
            'order_id' => $plannedOrder->id,
            'number' => 'CN-TARGET-PLANNED-001',
            'issued_at' => now()->toDateString(),
            'amount' => 40,
        ]);

        $otherOrder = Order::create([
            'agent_id' => $otherZoneAgent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $otherOrder->id,
            'product_id' => $product->id,
            'quantity' => 20,
            'unit_price' => 10,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        app(FinanceController::class)->ensureInvoiceForOrder($otherOrder->fresh());

        DB::table('visit_plans')->insert([
            'employee_id' => $employee->id,
            'agent_id' => $plannedAgent->id,
            'date' => now()->toDateString(),
            'status' => 'planned',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        SalesTarget::create([
            'employee_id' => $employee->id,
            'agent_id' => null,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'target_value' => 500,
        ]);

        $view = app(SalesTargetController::class)->index(
            Request::create('/admin/sales-targets', 'GET', ['month' => now()->format('Y-m')])
        );

        $rows = $view->getData()['targets'];
        $first = $rows->items()[0];

        $this->assertEquals(0.0, (float) $first['achieved']);
        $this->assertEquals(500.0, (float) $first['remaining']);
    }

    public function test_sales_target_unique_indexes_block_duplicate_owner_period_records(): void
    {
        $agent = Agent::create([
            'name' => 'Duplicate Target Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        SalesTarget::create([
            'agent_id' => $agent->id,
            'employee_id' => null,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'target_value' => 1000,
        ]);

        $this->expectException(QueryException::class);

        SalesTarget::create([
            'agent_id' => $agent->id,
            'employee_id' => null,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'target_value' => 2000,
        ]);
    }

    public function test_sales_target_store_rejects_overlapping_periods_for_same_owner(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-target-overlap@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Overlap Target Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        SalesTarget::create([
            'agent_id' => $agent->id,
            'employee_id' => null,
            'period_start' => '2026-03-01',
            'period_end' => '2026-03-31',
            'target_value' => 1000,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.sales-targets.create'))
            ->post(route('admin.sales-targets.store'), [
                'agent_id' => $agent->id,
                'period_start' => '2026-03-15',
                'period_end' => '2026-04-15',
                'target_value' => 2000,
            ]);

        $response->assertSessionHasErrors('target_value');
        $this->assertDatabaseCount('sales_targets', 1);
    }

    public function test_agent_pricing_validation_failure_preserves_existing_configuration(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-agent-pricing@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Agent Pricing Guard',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $sellableProduct = Product::create([
            'sku' => 'SKU-SELLABLE-1',
            'name' => 'Finished Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 50,
        ]);

        $serviceProduct = Product::create([
            'sku' => 'SKU-SERVICE-1',
            'name' => 'Service Product',
            'product_type' => 'service',
            'is_active' => true,
            'base_price' => 25,
        ]);

        AgentPriceList::create([
            'agent_id' => $agent->id,
            'product_id' => $sellableProduct->id,
            'price' => 75,
        ]);

        AgentCommissionRule::create([
            'agent_id' => $agent->id,
            'sku' => $sellableProduct->sku,
            'type' => 'percentage',
            'value' => 5,
            'order_type' => 'regular',
            'frequency' => 'per_order',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.agents.pricing.edit', $agent))
            ->patch(route('admin.agents.pricing.update', $agent), [
                'prices' => [
                    (string) $serviceProduct->id => 99,
                ],
                'commissions' => [
                    [
                        'sku' => 'MISSING-SKU',
                        'type' => 'percentage',
                        'value' => 5,
                        'order_type' => 'regular',
                        'frequency' => 'per_order',
                    ],
                ],
            ]);

        $response->assertSessionHasErrors([
            'prices.' . $serviceProduct->id,
            'commissions.0.sku',
        ]);

        $this->assertDatabaseHas('agent_price_lists', [
            'agent_id' => $agent->id,
            'product_id' => $sellableProduct->id,
            'price' => 75,
        ]);
        $this->assertDatabaseHas('agent_commission_rules', [
            'agent_id' => $agent->id,
            'sku' => $sellableProduct->sku,
            'type' => 'percentage',
            'value' => 5,
        ]);
        $this->assertDatabaseCount('agent_price_lists', 1);
        $this->assertDatabaseCount('agent_commission_rules', 1);
    }

    public function test_planned_campaign_and_gift_spend_are_excluded_from_ledger_and_profit_reports(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-spend@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Gift Agent',
            'credit_limit' => 100000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $campaignResponse = $this->actingAs($admin)
            ->from(route('admin.campaigns.index'))
            ->post(route('admin.campaigns.store'), [
                'name' => 'Planned Summer Campaign',
                'platform' => 'facebook',
                'start_date' => now()->toDateString(),
                'cost' => 100,
                'status' => Campaign::STATUS_PLANNED,
            ]);

        $campaignResponse->assertSessionHasNoErrors();

        $giftResponse = $this->actingAs($admin)
            ->from(route('admin.gifts.index'))
            ->post(route('admin.gifts.store'), [
                'agent_id' => $agent->id,
                'date' => now()->toDateString(),
                'amount' => 50,
                'status' => CustomerGift::STATUS_PLANNED,
            ]);

        $giftResponse->assertSessionHasNoErrors();

        $expenseResponse = $this->actingAs($admin)
            ->from(route('admin.expenses.index'))
            ->post(route('admin.expenses.store'), [
                'date' => now()->toDateString(),
                'category' => 'general',
                'amount' => 30,
                'status' => Expense::STATUS_RECORDED,
            ]);

        $expenseResponse->assertSessionHasNoErrors();

        $campaign = Campaign::firstOrFail();
        $gift = CustomerGift::firstOrFail();
        $expense = Expense::firstOrFail();

        $this->assertDatabaseMissing('ledger_entries', [
            'description' => 'Campaign expense #' . $campaign->id,
        ]);
        $this->assertDatabaseMissing('ledger_entries', [
            'description' => 'Customer gift #' . $gift->id,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'description' => 'Expense #' . $expense->id,
            'account' => 'Selling & Distribution Expense',
            'debit' => 30,
            'credit' => 0,
        ]);
        $this->assertDatabaseCount('ledger_entries', 2);

        $view = app(ReportController::class)->profitAndLoss(
            Request::create('/admin/reports/profit-and-loss', 'GET', [
                'from' => now()->startOfMonth()->toDateString(),
                'to' => now()->endOfMonth()->toDateString(),
            ])
        );

        $this->assertEquals(30.0, (float) $view->getData()['otherExpenses']);
    }

    public function test_supplier_creation_rejects_duplicate_name_email_and_tax_id(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-supplier@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        DB::table('suppliers')->insert([
            'name' => 'Supplier Prime',
            'contact_person' => 'Owner One',
            'email' => 'supplier@example.test',
            'phone' => '0123456789',
            'address' => 'Test Address',
            'tax_id' => 'TAX-001',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.suppliers.index'))
            ->post(route('admin.suppliers.store'), [
                'name' => 'Supplier Prime',
                'contact_person' => 'Owner Two',
                'email' => 'SUPPLIER@example.test',
                'phone' => '9876543210',
                'address' => 'Another Address',
                'tax_id' => ' TAX-001 ',
            ]);

        $response->assertSessionHasErrors(['name', 'email', 'tax_id']);
        $this->assertDatabaseCount('suppliers', 1);
    }

    public function test_receipt_cannot_exceed_invoice_outstanding(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $invoice = Invoice::create([
            'number' => 'INV-OVERPAY-001',
            'issued_at' => now()->toDateString(),
            'net_total' => 100,
            'vat_amount' => 0,
            'withholding' => 0,
            'status' => 'issued',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.finance.receipts.store', $invoice), [
                'amount' => 150,
            ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('receipts', 0);
    }

    public function test_employee_login_creation_requires_system_settings_permission(): void
    {
        $employeeId = DB::table('employees')->insertGetId([
            'name' => 'Employee One',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $hrUser = User::create([
            'name' => 'HR User',
            'email' => 'hr@example.test',
            'password' => 'secret',
            'role' => 'hr_officer',
        ]);

        DB::table('role_permissions')->insert([
            'role' => 'hr_officer',
            'permission_name' => 'control.employees',
        ]);

        $response = $this->actingAs($hrUser)->post(route('admin.employees.user.store', $employeeId), [
            'name' => 'Linked User',
            'email' => 'linked@example.test',
            'role' => 'sales_officer',
            'password' => 'secret123',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', [
            'email' => 'linked@example.test',
        ]);
    }

    public function test_employee_login_creation_supports_custom_roles_in_form_and_store(): void
    {
        $employeeId = DB::table('employees')->insertGetId([
            'name' => 'Employee Custom Role',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('roles')->insert([
            'key' => 'field_auditor',
            'label' => 'Field Auditor',
            'is_system' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-custom-role@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $createResponse = $this->actingAs($admin)
            ->get(route('admin.employees.user.create', $employeeId));

        $createResponse->assertOk();
        $createResponse->assertSee('Field Auditor');

        $storeResponse = $this->actingAs($admin)
            ->post(route('admin.employees.user.store', $employeeId), [
                'name' => 'Field Auditor User',
                'email' => 'field-auditor@example.test',
                'role' => 'field_auditor',
                'password' => 'secret123',
            ]);

        $storeResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'email' => 'field-auditor@example.test',
            'role' => 'field_auditor',
            'employee_id' => $employeeId,
        ]);
        $this->assertDatabaseHas('user_roles', [
            'role_key' => 'field_auditor',
        ]);
    }

    public function test_stock_audit_store_and_delete_respect_warehouse_scope(): void
    {
        $warehouseOne = DB::table('warehouses')->insertGetId([
            'name' => 'Main Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $warehouseTwo = DB::table('warehouses')->insertGetId([
            'name' => 'Remote Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'sku' => 'SKU-AUDIT-1',
            'name' => 'Audit Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 10,
        ]);

        $user = User::create([
            'name' => 'Scoped Auditor',
            'email' => 'auditor@example.test',
            'password' => 'secret',
            'role' => 'warehouse_officer',
        ]);

        DB::table('role_permissions')->insert([
            'role' => 'warehouse_officer',
            'permission_name' => 'inventory.manage',
        ]);
        DB::table('user_warehouse_scopes')->insert([
            'user_id' => $user->id,
            'warehouse_id' => $warehouseOne,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $storeResponse = $this->actingAs($user)->post(route('admin.stock.audit.store'), [
            'warehouse_id' => $warehouseTwo,
            'product_id' => $product->id,
            'counted_quantity' => 5,
        ]);

        $storeResponse->assertForbidden();
        $this->assertDatabaseCount('stock_audits', 0);

        $auditId = DB::table('stock_audits')->insertGetId([
            'warehouse_id' => $warehouseTwo,
            'product_id' => $product->id,
            'system_quantity' => 0,
            'counted_quantity' => 1,
            'variance' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $deleteResponse = $this->actingAs($user)->delete(route('admin.stock.audit.destroy', $auditId));

        $deleteResponse->assertForbidden();
        $this->assertDatabaseHas('stock_audits', [
            'id' => $auditId,
        ]);
    }

    public function test_purchase_order_creation_rejects_non_stock_products(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-po@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => 'Supplier One',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $serviceProduct = Product::create([
            'sku' => 'SKU-SVC-1',
            'name' => 'Service Item',
            'product_type' => 'service',
            'is_active' => true,
            'base_price' => 50,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.purchase-orders.store'), [
            'supplier_id' => $supplierId,
            'order_date' => now()->toDateString(),
            'items' => [
                [
                    'description' => 'Non stock service',
                    'product_id' => $serviceProduct->id,
                    'quantity' => 2,
                    'unit_price' => 50,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('items.0.product_id');
        $this->assertDatabaseCount('purchase_orders', 0);
    }

    public function test_batch_creation_rejects_non_finished_products(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-batch@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $serviceProduct = Product::create([
            'sku' => 'SKU-BATCH-SVC',
            'name' => 'Service Batch Product',
            'product_type' => 'service',
            'is_active' => true,
            'base_price' => 20,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.batches.store'), [
            'product_id' => $serviceProduct->id,
            'batch_code' => 'BATCH-SVC-001',
            'production_date' => now()->toDateString(),
            'qc_status' => 'pending',
        ]);

        $response->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('batches', 0);
    }

    public function test_bank_reconciliation_only_updates_receipts_within_selected_period(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-recon@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $invoiceJan = Invoice::create([
            'number' => 'INV-RECON-001',
            'issued_at' => '2026-01-05',
            'net_total' => 100,
            'vat_amount' => 0,
            'withholding' => 0,
            'status' => 'issued',
        ]);
        $invoiceFeb = Invoice::create([
            'number' => 'INV-RECON-002',
            'issued_at' => '2026-02-05',
            'net_total' => 100,
            'vat_amount' => 0,
            'withholding' => 0,
            'status' => 'issued',
        ]);

        $janReceipt = Receipt::create([
            'invoice_id' => $invoiceJan->id,
            'amount' => 50,
            'received_at' => '2026-01-10',
            'reconciled' => false,
        ]);
        $febReceipt = Receipt::create([
            'invoice_id' => $invoiceFeb->id,
            'amount' => 50,
            'received_at' => '2026-02-10',
            'reconciled' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.finance.reconciliation.update'), [
            'from' => '2026-01-01',
            'to' => '2026-01-31',
            'reconciled' => [$janReceipt->id, $febReceipt->id],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue($janReceipt->fresh()->reconciled);
        $this->assertFalse($febReceipt->fresh()->reconciled);
    }

    public function test_warehouse_location_delete_preserves_goods_receipt_history(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-location@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $warehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'History Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $locationId = DB::table('warehouse_locations')->insertGetId([
            'warehouse_id' => $warehouseId,
            'code' => 'A-01',
            'description' => 'Rack A1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('goods_receipt_items')->insert([
            'warehouse_location_id' => $locationId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.warehouse-locations.destroy', $locationId));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('warehouse_locations', [
            'id' => $locationId,
        ]);
    }

    public function test_delivery_creation_rejects_route_vehicle_mismatch_and_duplicate_order_delivery(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-delivery@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Delivery Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'picked',
            'total' => 0,
        ]);

        $routeVehicleId = DB::table('vehicles')->insertGetId([
            'name' => 'Truck A',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherVehicleId = DB::table('vehicles')->insertGetId([
            'name' => 'Truck B',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $routeId = DB::table('delivery_routes')->insertGetId([
            'name' => 'North Route',
            'vehicle_id' => $routeVehicleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mismatchResponse = $this->actingAs($admin)->post(route('admin.deliveries.store'), [
            'order_id' => $order->id,
            'route_id' => $routeId,
            'vehicle_id' => $otherVehicleId,
            'status' => 'scheduled',
        ]);

        $mismatchResponse->assertSessionHasErrors('vehicle_id');
        $this->assertDatabaseCount('deliveries', 0);

        $createResponse = $this->actingAs($admin)->post(route('admin.deliveries.store'), [
            'order_id' => $order->id,
            'route_id' => $routeId,
            'status' => 'scheduled',
        ]);

        $createResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('deliveries', [
            'order_id' => $order->id,
            'route_id' => $routeId,
            'vehicle_id' => $routeVehicleId,
        ]);

        $duplicateResponse = $this->actingAs($admin)->post(route('admin.deliveries.store'), [
            'order_id' => $order->id,
            'route_id' => $routeId,
            'status' => 'scheduled',
        ]);

        $duplicateResponse->assertSessionHasErrors('order_id');
        $this->assertDatabaseCount('deliveries', 1);
    }

    public function test_vehicle_schedule_uses_route_default_vehicle_and_rejects_mismatch(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-schedule@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $routeVehicleId = DB::table('vehicles')->insertGetId([
            'name' => 'Route Truck',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherVehicleId = DB::table('vehicles')->insertGetId([
            'name' => 'Spare Truck',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $routeId = DB::table('delivery_routes')->insertGetId([
            'name' => 'East Route',
            'vehicle_id' => $routeVehicleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mismatchResponse = $this->actingAs($admin)->post(route('admin.vehicle-schedule.store'), [
            'route_id' => $routeId,
            'vehicle_id' => $otherVehicleId,
            'scheduled_date' => '2026-03-20',
        ]);

        $mismatchResponse->assertSessionHasErrors('vehicle_id');
        $this->assertDatabaseCount('vehicle_schedules', 0);

        $createResponse = $this->actingAs($admin)->post(route('admin.vehicle-schedule.store'), [
            'route_id' => $routeId,
            'scheduled_date' => '2026-03-21',
        ]);

        $createResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('vehicle_schedules', [
            'route_id' => $routeId,
            'vehicle_id' => $routeVehicleId,
            'scheduled_date' => '2026-03-21 00:00:00',
        ]);
    }

    public function test_stock_transfer_requires_destination_rules_and_preserves_location(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-transfer@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $warehouseOne = DB::table('warehouses')->insertGetId([
            'name' => 'Source Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $warehouseTwo = DB::table('warehouses')->insertGetId([
            'name' => 'Destination Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $sourceLocationId = DB::table('warehouse_locations')->insertGetId([
            'warehouse_id' => $warehouseOne,
            'code' => 'SRC-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $destinationLocationId = DB::table('warehouse_locations')->insertGetId([
            'warehouse_id' => $warehouseTwo,
            'code' => 'DST-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'sku' => 'SKU-TRANSFER-1',
            'name' => 'Transfer Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 25,
        ]);

        $entry = StockEntry::create([
            'warehouse_id' => $warehouseOne,
            'warehouse_location_id' => $sourceLocationId,
            'product_id' => $product->id,
            'quantity' => 10,
            'status' => 'available',
        ]);

        $sameWarehouseResponse = $this->actingAs($admin)
            ->from(route('admin.stock.transfers'))
            ->post(route('admin.stock.transfers.store'), [
                'entry_id' => $entry->id,
                'destination_warehouse_id' => $warehouseOne,
                'destination_warehouse_location_id' => $sourceLocationId,
                'quantity' => 2,
            ]);

        $sameWarehouseResponse->assertSessionHasErrors('destination_warehouse_id');

        $missingLocationResponse = $this->actingAs($admin)
            ->from(route('admin.stock.transfers'))
            ->post(route('admin.stock.transfers.store'), [
                'entry_id' => $entry->id,
                'destination_warehouse_id' => $warehouseTwo,
                'quantity' => 4,
            ]);

        $missingLocationResponse->assertSessionHasErrors('destination_warehouse_location_id');

        $transferResponse = $this->actingAs($admin)->post(route('admin.stock.transfers.store'), [
            'entry_id' => $entry->id,
            'destination_warehouse_id' => $warehouseTwo,
            'destination_warehouse_location_id' => $destinationLocationId,
            'quantity' => 4,
            'notes' => 'Move to overflow storage',
        ]);

        $transferResponse->assertSessionHasNoErrors();
        $this->assertEquals(6.0, (float) $entry->fresh()->quantity);
        $this->assertDatabaseHas('stock_entries', [
            'warehouse_id' => $warehouseTwo,
            'warehouse_location_id' => $destinationLocationId,
            'product_id' => $product->id,
            'quantity' => 4,
            'status' => 'available',
        ]);
        $this->assertDatabaseCount('stock_movements', 2);
        $this->assertDatabaseHas('stock_movements', [
            'stock_entry_id' => $entry->id,
            'type' => 'transfer-out',
            'quantity' => -4,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'type' => 'transfer-in',
            'quantity' => 4,
        ]);
    }

    public function test_goods_receipt_posts_stock_entry_and_goods_receipt_movement(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-grn@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $supplier = Supplier::create([
            'name' => 'GRN Supplier',
        ]);

        $warehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'GRN Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $locationId = DB::table('warehouse_locations')->insertGetId([
            'warehouse_id' => $warehouseId,
            'code' => 'GRN-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'sku' => 'SKU-GRN-1',
            'name' => 'GRN Product',
            'product_type' => 'raw',
            'is_active' => true,
            'base_price' => 10,
        ]);

        $this->actingAs($admin);

        app(\App\Http\Controllers\Admin\GoodsReceiptController::class)->store(new Request([
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouseId,
            'received_at' => now()->toDateTimeString(),
            'items' => [[
                'product_id' => $product->id,
                'warehouse_location_id' => $locationId,
                'quantity' => 12,
                'unit_cost' => 5,
                'qc_status' => 'approved',
            ]],
        ]));

        $entry = StockEntry::firstOrFail();

        $this->assertDatabaseHas('stock_entries', [
            'id' => $entry->id,
            'warehouse_id' => $warehouseId,
            'warehouse_location_id' => $locationId,
            'product_id' => $product->id,
            'quantity' => 12,
            'status' => 'available',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'stock_entry_id' => $entry->id,
            'type' => 'goods-receipt',
            'quantity' => 12,
        ]);
    }

    public function test_production_confirm_posts_consumption_and_output_movements(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-production-movements@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $warehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'Production Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $finishedProduct = Product::create([
            'sku' => 'SKU-FG-1',
            'name' => 'Finished Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 20,
        ]);

        $rawMaterial = Product::create([
            'sku' => 'SKU-RM-1',
            'name' => 'Raw Material',
            'product_type' => 'raw',
            'is_active' => true,
            'base_price' => 5,
        ]);

        $finishedBatchId = DB::table('batches')->insertGetId([
            'product_id' => $finishedProduct->id,
            'batch_code' => 'FG-001',
            'production_date' => now()->toDateString(),
            'qc_status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rawBatchId = DB::table('batches')->insertGetId([
            'product_id' => $rawMaterial->id,
            'batch_code' => 'RM-001',
            'production_date' => now()->subDay()->toDateString(),
            'qc_status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('bill_of_materials')->insert([
            'id' => 1,
            'product_id' => $finishedProduct->id,
            'name' => 'FG BOM',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('bom_items')->insert([
            'bill_of_materials_id' => 1,
            'component_product_id' => $rawMaterial->id,
            'quantity' => 2,
            'unit_cost' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rawEntry = StockEntry::create([
            'warehouse_id' => $warehouseId,
            'product_id' => $rawMaterial->id,
            'batch_id' => $rawBatchId,
            'quantity' => 20,
            'status' => 'available',
        ]);

        $run = \App\Models\ProductionRun::create([
            'order_number' => 'PO-TEST-001',
            'product_id' => $finishedProduct->id,
            'batch_id' => $finishedBatchId,
            'warehouse_id' => $warehouseId,
            'quantity' => 10,
            'status' => 'confirmed',
            'qc_status' => 'approved',
        ]);

        $this->actingAs($admin);

        app(ProductionController::class)->confirmStock(new Request(), $run->fresh());

        $finishedEntry = StockEntry::where('product_id', $finishedProduct->id)->firstOrFail();

        $this->assertEquals(0.0, (float) $rawEntry->fresh()->quantity);
        $this->assertEquals('sold', $rawEntry->fresh()->status);
        $this->assertEquals(10.0, (float) $finishedEntry->quantity);
        $this->assertDatabaseHas('stock_movements', [
            'stock_entry_id' => $rawEntry->id,
            'type' => 'production-consumption',
            'quantity' => -20,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'stock_entry_id' => $finishedEntry->id,
            'type' => 'production-output',
            'quantity' => 10,
        ]);
    }

    public function test_order_reservation_and_delivery_post_stock_movements(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-order-movements@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Movement Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $warehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'Sales Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'sku' => 'SKU-SALES-1',
            'name' => 'Sales Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 10,
        ]);

        $batchId = DB::table('batches')->insertGetId([
            'product_id' => $product->id,
            'batch_code' => 'SALES-BATCH-1',
            'production_date' => now()->toDateString(),
            'qc_status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $availableEntry = StockEntry::create([
            'warehouse_id' => $warehouseId,
            'product_id' => $product->id,
            'batch_id' => $batchId,
            'quantity' => 10,
            'status' => 'available',
        ]);

        $this->actingAs($admin);

        app(OrderController::class)->store(new Request([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'items' => [[
                'product_id' => $product->id,
                'quantity' => 10,
                'unit_price' => 10,
            ]],
        ]));

        $order = Order::latest('id')->firstOrFail();
        $orderItem = OrderItem::where('order_id', $order->id)->firstOrFail();
        $reservedEntry = StockEntry::where('order_id', $order->id)
            ->where('status', 'reserved')
            ->firstOrFail();

        $this->assertDatabaseHas('stock_movements', [
            'stock_entry_id' => $availableEntry->id,
            'order_id' => $order->id,
            'type' => 'reservation-out',
            'quantity' => -10,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'stock_entry_id' => $reservedEntry->id,
            'order_id' => $order->id,
            'type' => 'reservation-in',
            'quantity' => 10,
        ]);

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'status' => 'scheduled',
        ]);

        app(DeliveryController::class)->update(new Request([
            'status' => 'delivered',
            'items' => [[
                'order_item_id' => $orderItem->id,
                'product_id' => $product->id,
                'batch_id' => $batchId,
                'qty_dispatched' => 10,
                'qty_delivered' => 10,
                'qty_short' => 0,
                'qty_damaged' => 0,
            ]],
        ]), $delivery->fresh());

        $this->assertDatabaseHas('stock_movements', [
            'stock_entry_id' => $reservedEntry->id,
            'order_id' => $order->id,
            'type' => 'delivery',
            'quantity' => -10,
        ]);
        $this->assertEquals(0.0, (float) $reservedEntry->fresh()->quantity);
        $this->assertEquals('sold', $reservedEntry->fresh()->status);
    }

    public function test_customer_return_reuses_batched_available_entry_and_logs_movement(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-return-batch@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Return Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $warehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'Return Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'sku' => 'SKU-RET-1',
            'name' => 'Return Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 10,
        ]);

        $batchId = DB::table('batches')->insertGetId([
            'product_id' => $product->id,
            'batch_code' => 'RET-BATCH-1',
            'production_date' => now()->toDateString(),
            'qc_status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 10,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'status' => 'delivered',
        ]);

        DeliveryItem::create([
            'delivery_id' => $delivery->id,
            'order_item_id' => $orderItem->id,
            'product_id' => $product->id,
            'batch_id' => $batchId,
            'qty_dispatched' => 10,
            'qty_delivered' => 10,
            'qty_short' => 0,
            'qty_damaged' => 0,
        ]);

        $availableEntry = StockEntry::create([
            'warehouse_id' => $warehouseId,
            'product_id' => $product->id,
            'batch_id' => $batchId,
            'quantity' => 85,
            'status' => 'available',
        ]);

        StockEntry::create([
            'warehouse_id' => $warehouseId,
            'product_id' => $product->id,
            'batch_id' => $batchId,
            'quantity' => 10,
            'status' => 'reserved',
        ]);

        $this->actingAs($admin);

        app(\App\Http\Controllers\Admin\CustomerReturnController::class)->store(new Request([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
            'quantity' => 5,
            'notes' => 'Damaged / QA test',
        ]));

        $this->assertEquals(90.0, (float) $availableEntry->fresh()->quantity);
        $this->assertSame(0, StockEntry::where('warehouse_id', $warehouseId)
            ->where('product_id', $product->id)
            ->whereNull('batch_id')
            ->where('status', 'available')
            ->count());
        $this->assertDatabaseHas('stock_movements', [
            'stock_entry_id' => $availableEntry->id,
            'order_id' => $order->id,
            'type' => 'customer-return',
            'quantity' => 5,
        ]);
    }

    public function test_order_deletion_rejects_unlinked_legacy_reserved_stock(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-order-delete@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Delete Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $product = Product::create([
            'sku' => 'SKU-DELETE-1',
            'name' => 'Reserved Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 10,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'confirmed',
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_price' => 10,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        $legacyReservedEntry = StockEntry::create([
            'warehouse_id' => 1,
            'product_id' => $product->id,
            'quantity' => 5,
            'status' => 'reserved',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.orders.index'))
            ->delete(route('admin.orders.destroy', $order));

        $response->assertSessionHasErrors('order');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
        ]);
        $this->assertDatabaseHas('stock_entries', [
            'id' => $legacyReservedEntry->id,
            'order_id' => null,
            'quantity' => 5,
            'status' => 'reserved',
        ]);
        $this->assertDatabaseMissing('stock_entries', [
            'product_id' => $product->id,
            'status' => 'available',
        ]);
    }

    public function test_employee_deletion_is_blocked_when_history_exists(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-employee-delete@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $employeeId = DB::table('employees')->insertGetId([
            'name' => 'History Employee',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('salary_distributions')->insert([
            'employee_id' => $employeeId,
            'period_start' => '2026-03-01',
            'period_end' => '2026-03-31',
            'base_salary' => 1000,
            'bonus' => 0,
            'ta_allowances' => 0,
            'da_allowances' => 0,
            'commission' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.employees.index'))
            ->delete(route('admin.employees.destroy', $employeeId));

        $response->assertSessionHasErrors('employee');
        $this->assertDatabaseHas('employees', [
            'id' => $employeeId,
        ]);
    }

    public function test_stock_confirmed_production_run_cannot_be_deleted(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-production-delete@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $warehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'Factory Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'sku' => 'SKU-PROD-LOCK',
            'name' => 'Production Locked Product',
            'product_type' => 'finished',
            'is_active' => true,
            'base_price' => 10,
        ]);

        $runId = DB::table('production_runs')->insertGetId([
            'product_id' => $product->id,
            'warehouse_id' => $warehouseId,
            'quantity' => 25,
            'status' => 'completed',
            'qc_status' => 'approved',
            'stock_confirmed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.production.index'))
            ->delete(route('admin.production.destroy', $runId));

        $response->assertSessionHasErrors('production');
        $this->assertDatabaseHas('production_runs', [
            'id' => $runId,
        ]);
    }

    public function test_purchase_bill_with_goods_receipt_cannot_be_deleted(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-bill-delete@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => 'Supplier Delete Guard',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $warehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'Receiving Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $billId = DB::table('purchase_bills')->insertGetId([
            'supplier_id' => $supplierId,
            'number' => 'PB-LOCK-001',
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'net_total' => 500,
            'vat_amount' => 0,
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('goods_receipts')->insert([
            'purchase_bill_id' => $billId,
            'supplier_id' => $supplierId,
            'warehouse_id' => $warehouseId,
            'grn_number' => 'GRN-LOCK-001',
            'received_at' => now(),
            'status' => 'posted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.bills.index'))
            ->delete(route('admin.bills.destroy', $billId));

        $response->assertSessionHasErrors('bill');
        $this->assertDatabaseHas('purchase_bills', [
            'id' => $billId,
        ]);
    }

    public function test_delivery_with_operational_activity_cannot_be_deleted(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-delivery-delete@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Delivery Delete Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'status' => 'delivered',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.deliveries.index'))
            ->delete(route('admin.deliveries.destroy', $delivery));

        $response->assertSessionHasErrors('delivery');
        $this->assertDatabaseHas('deliveries', [
            'id' => $delivery->id,
        ]);
    }

    public function test_role_manager_clears_stale_secondary_roles(): void
    {
        DB::table('roles')->insert([
            ['key' => 'admin', 'label' => 'Admin', 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'sales_officer', 'label' => 'Sales Officer', 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'warehouse_officer', 'label' => 'Warehouse Officer', 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'accounts_officer', 'label' => 'Accounts Officer', 'is_system' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-role-manager@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $user = User::create([
            'name' => 'Role Target',
            'email' => 'role-target@example.test',
            'password' => 'secret',
            'role' => 'sales_officer',
        ]);

        DB::table('user_roles')->insert([
            [
                'user_id' => $user->id,
                'role_key' => 'sales_officer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user->id,
                'role_key' => 'warehouse_officer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.roles.update', $user), [
                'role' => 'accounts_officer',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('accounts_officer', $user->fresh()->role);
        $this->assertDatabaseHas('user_roles', [
            'user_id' => $user->id,
            'role_key' => 'accounts_officer',
        ]);
        $this->assertDatabaseMissing('user_roles', [
            'user_id' => $user->id,
            'role_key' => 'sales_officer',
        ]);
        $this->assertDatabaseMissing('user_roles', [
            'user_id' => $user->id,
            'role_key' => 'warehouse_officer',
        ]);
    }

    public function test_salary_distribution_unique_constraint_blocks_duplicate_employee_period_rows(): void
    {
        $employeeId = DB::table('employees')->insertGetId([
            'name' => 'Payroll Employee',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('salary_distributions')->insert([
            'employee_id' => $employeeId,
            'period_start' => '2026-03-01',
            'period_end' => '2026-03-31',
            'base_salary' => 1000,
            'bonus' => 0,
            'ta_allowances' => 0,
            'da_allowances' => 0,
            'commission' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(QueryException::class);

        DB::table('salary_distributions')->insert([
            'employee_id' => $employeeId,
            'period_start' => '2026-03-01',
            'period_end' => '2026-03-31',
            'base_salary' => 1200,
            'bonus' => 0,
            'ta_allowances' => 0,
            'da_allowances' => 0,
            'commission' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_badge_with_employee_grants_cannot_be_deleted(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-badge-delete@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $employeeId = DB::table('employees')->insertGetId([
            'name' => 'Badge Employee',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $badgeId = DB::table('badges')->insertGetId([
            'name' => 'Safety Champion',
            'code' => 'SAFE-01',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employee_badges')->insert([
            'employee_id' => $employeeId,
            'badge_id' => $badgeId,
            'granted_at' => now()->toDateString(),
            'granted_by' => 'HR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.badges.index'))
            ->delete(route('admin.badges.destroy', $badgeId));

        $response->assertSessionHasErrors('badge');
        $this->assertDatabaseHas('badges', [
            'id' => $badgeId,
        ]);
    }

    public function test_badge_grants_reject_inactive_badges_and_duplicate_same_day_grants(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-badge-grant@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $employeeId = DB::table('employees')->insertGetId([
            'name' => 'Badge Grant Employee',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $inactiveBadgeId = DB::table('badges')->insertGetId([
            'name' => 'Retired Badge',
            'code' => 'RET-01',
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $activeBadgeId = DB::table('badges')->insertGetId([
            'name' => 'Active Badge',
            'code' => 'ACT-01',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $inactiveResponse = $this->actingAs($admin)
            ->from(route('admin.employees.badges.grant-form', $employeeId))
            ->post(route('admin.employees.badges.grant', $employeeId), [
                'badge_id' => $inactiveBadgeId,
                'granted_at' => '2026-03-12',
            ]);

        $inactiveResponse->assertSessionHasErrors('badge_id');
        $this->assertDatabaseCount('employee_badges', 0);

        $firstGrantResponse = $this->actingAs($admin)
            ->from(route('admin.employees.badges.grant-form', $employeeId))
            ->post(route('admin.employees.badges.grant', $employeeId), [
                'badge_id' => $activeBadgeId,
                'granted_at' => '2026-03-12',
            ]);

        $firstGrantResponse->assertSessionHasNoErrors();
        $this->assertTrue(DB::table('employee_badges')
            ->where('employee_id', $employeeId)
            ->where('badge_id', $activeBadgeId)
            ->whereDate('granted_at', '2026-03-12')
            ->exists());

        $duplicateResponse = $this->actingAs($admin)
            ->from(route('admin.employees.badges.grant-form', $employeeId))
            ->post(route('admin.employees.badges.grant', $employeeId), [
                'badge_id' => $activeBadgeId,
                'granted_at' => '2026-03-12',
            ]);

        $duplicateResponse->assertSessionHasErrors('granted_at');
        $this->assertDatabaseCount('employee_badges', 1);
    }

    public function test_sales_dashboard_keeps_same_named_agents_and_products_separate(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-sales-dashboard@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $firstAgent = Agent::create([
            'name' => 'Shared Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);
        $secondAgent = Agent::create([
            'name' => 'Shared Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $firstProduct = Product::create([
            'sku' => 'SKU-SHARED-1',
            'name' => 'Shared Product',
            'product_type' => 'finished',
            'base_price' => 10,
            'is_active' => true,
        ]);
        $secondProduct = Product::create([
            'sku' => 'SKU-SHARED-2',
            'name' => 'Shared Product',
            'product_type' => 'finished',
            'base_price' => 20,
            'is_active' => true,
        ]);

        $firstOrder = Order::create([
            'agent_id' => $firstAgent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 100,
        ]);
        $secondOrder = Order::create([
            'agent_id' => $secondAgent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 200,
        ]);

        $firstInvoice = Invoice::create([
            'order_id' => $firstOrder->id,
            'number' => 'INV-SALES-DASH-1',
            'issued_at' => now()->toDateString(),
            'net_total' => 100,
            'vat_amount' => 0,
            'withholding' => 0,
            'status' => 'issued',
        ]);
        $secondInvoice = Invoice::create([
            'order_id' => $secondOrder->id,
            'number' => 'INV-SALES-DASH-2',
            'issued_at' => now()->toDateString(),
            'net_total' => 200,
            'vat_amount' => 0,
            'withholding' => 0,
            'status' => 'issued',
        ]);

        DB::table('invoice_items')->insert([
            [
                'invoice_id' => $firstInvoice->id,
                'product_id' => $firstProduct->id,
                'description' => 'Shared Product',
                'quantity' => 5,
                'unit_price' => 20,
                'line_total' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'invoice_id' => $secondInvoice->id,
                'product_id' => $secondProduct->id,
                'description' => 'Shared Product',
                'quantity' => 10,
                'unit_price' => 20,
                'line_total' => 200,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.sales.dashboard'));

        $response->assertOk();
        $response->assertViewHas('topAgents', function ($agents) {
            $netSales = $agents->pluck('net_sales')->map(fn ($value) => (float) $value)->sort()->values()->all();

            return $agents->count() === 2
                && $agents->pluck('agent_name')->unique()->values()->all() === ['Shared Agent']
                && $netSales === [100.0, 200.0];
        });
        $response->assertViewHas('topProducts', function ($products) {
            $netSales = $products->pluck('net')->map(fn ($value) => (float) $value)->sort()->values()->all();

            return $products->count() === 2
                && $products->pluck('product_name')->unique()->values()->all() === ['Shared Product']
                && $netSales === [100.0, 200.0];
        });
    }

    public function test_sales_and_accounting_dashboards_net_same_period_credit_notes(): void
    {
        Carbon::setTestNow('2026-04-06 12:00:00');

        try {
            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin-dashboard-credits@example.test',
                'password' => 'secret',
                'role' => 'admin',
            ]);

            $agent = Agent::create([
                'name' => 'Dashboard Agent',
                'credit_limit' => 10000,
                'withholding_rate' => 0,
                'is_active' => true,
            ]);

            $product = Product::create([
                'sku' => 'SKU-DASH-CREDIT-1',
                'name' => 'Dashboard Product',
                'product_type' => 'finished',
                'base_price' => 10,
                'is_active' => true,
            ]);

            $order = Order::create([
                'agent_id' => $agent->id,
                'order_type' => 'regular',
                'status' => 'delivered',
                'total' => 100,
            ]);

            $invoice = Invoice::create([
                'order_id' => $order->id,
                'number' => 'INV-DASH-CREDIT-001',
                'issued_at' => '2026-04-03',
                'net_total' => 100,
                'vat_amount' => 15,
                'withholding' => 0,
                'status' => 'issued',
            ]);

            DB::table('invoice_items')->insert([
                'invoice_id' => $invoice->id,
                'product_id' => $product->id,
                'description' => 'Dashboard Product',
                'quantity' => 10,
                'unit_price' => 10,
                'line_total' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            CreditNote::create([
                'invoice_id' => $invoice->id,
                'order_id' => $order->id,
                'number' => 'CN-DASH-CREDIT-001',
                'issued_at' => '2026-04-05',
                'amount' => 57.5,
            ]);

            Receipt::create([
                'invoice_id' => $invoice->id,
                'amount' => 20,
                'received_at' => '2026-04-04',
            ]);

            Receipt::create([
                'invoice_id' => $invoice->id,
                'amount' => 40,
                'received_at' => '2026-04-20',
            ]);

            $advance = AgentAdvance::create([
                'agent_id' => $agent->id,
                'amount' => 15,
                'applied_amount' => 15,
                'advanced_at' => '2026-04-05',
                'status' => 'applied',
            ]);

            DB::table('agent_advance_applications')->insert([
                [
                    'agent_advance_id' => $advance->id,
                    'invoice_id' => $invoice->id,
                    'amount' => 10,
                    'applied_at' => '2026-04-05',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'agent_advance_id' => $advance->id,
                    'invoice_id' => $invoice->id,
                    'amount' => 5,
                    'applied_at' => '2026-04-22',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            Invoice::create([
                'order_id' => null,
                'number' => 'INV-DASH-CREDIT-FUTURE',
                'issued_at' => '2026-04-20',
                'net_total' => 200,
                'vat_amount' => 30,
                'withholding' => 0,
                'status' => 'issued',
            ]);

            $salesResponse = $this->actingAs($admin)->get(route('admin.sales.dashboard'));

            $salesResponse->assertOk();
            $salesResponse->assertViewHas('totalInvoices', 1);
            $salesResponse->assertViewHas('netSales', 50.0);
            $salesResponse->assertViewHas('vatTotal', 7.5);
            $salesResponse->assertViewHas('collected', 20.0);
            $salesResponse->assertViewHas('outstanding', 27.5);
            $salesResponse->assertViewHas('topAgents', function ($agents) {
                return $agents->count() === 1
                    && (float) $agents->first()['net_sales'] === 50.0;
            });
            $salesResponse->assertViewHas('topProducts', function ($products) {
                return $products->count() === 1
                    && (float) $products->first()['net'] === 50.0;
            });

            $accountingResponse = $this->actingAs($admin)->get(route('admin.accounting.dashboard', [
                'range' => 'month',
            ]));

            $accountingResponse->assertOk();
            $accountingResponse->assertViewHas('totalInvoices', 1);
            $accountingResponse->assertViewHas('netSales', 50.0);
            $accountingResponse->assertViewHas('vatTotal', 7.5);
            $accountingResponse->assertViewHas('collected', 20.0);
            $accountingResponse->assertViewHas('outstanding', 27.5);
            $accountingResponse->assertViewHas('netProfitEstimate', 50.0);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_manufacturing_dashboard_keeps_same_named_products_separate(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-manufacturing-dashboard@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $warehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'Factory',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $firstProduct = Product::create([
            'sku' => 'SKU-MFG-SHARED-1',
            'name' => 'Shared Output',
            'product_type' => 'finished',
            'base_price' => 10,
            'is_active' => true,
        ]);
        $secondProduct = Product::create([
            'sku' => 'SKU-MFG-SHARED-2',
            'name' => 'Shared Output',
            'product_type' => 'finished',
            'base_price' => 10,
            'is_active' => true,
        ]);

        DB::table('production_runs')->insert([
            [
                'product_id' => $firstProduct->id,
                'warehouse_id' => $warehouseId,
                'quantity' => 12,
                'qc_status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => $secondProduct->id,
                'warehouse_id' => $warehouseId,
                'quantity' => 18,
                'qc_status' => 'approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.manufacturing.dashboard'));

        $response->assertOk();
        $response->assertViewHas('topProducts', function ($products) {
            $quantities = $products->pluck('qty')->map(fn ($value) => (float) $value)->sort()->values()->all();

            return $products->count() === 2
                && $products->pluck('product_name')->unique()->values()->all() === ['Shared Output']
                && $quantities === [12.0, 18.0];
        });
    }

    public function test_reports_dashboard_counts_only_active_agents(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-reports-dashboard@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        Agent::create([
            'name' => 'Active Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);
        Agent::create([
            'name' => 'Inactive Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.dashboard'));

        $response->assertOk();
        $response->assertViewHas('activeAgents', 1);
    }

    public function test_reports_dashboard_is_year_to_date_and_excludes_future_records(): void
    {
        Carbon::setTestNow('2026-04-06 12:00:00');

        try {
            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin-reports-ytd@example.test',
                'password' => 'secret',
                'role' => 'admin',
            ]);

            $agent = Agent::create([
                'name' => 'Reports Agent',
                'credit_limit' => 10000,
                'withholding_rate' => 0,
                'is_active' => true,
            ]);

            $order = Order::create([
                'agent_id' => $agent->id,
                'order_type' => 'regular',
                'status' => 'delivered',
                'total' => 0,
            ]);

            $invoice = Invoice::create([
                'order_id' => $order->id,
                'number' => 'INV-REPORTS-YTD-001',
                'issued_at' => '2026-03-10',
                'net_total' => 100,
                'vat_amount' => 15,
                'withholding' => 0,
                'status' => 'issued',
            ]);

            CreditNote::create([
                'invoice_id' => $invoice->id,
                'order_id' => $order->id,
                'number' => 'CN-REPORTS-YTD-001',
                'issued_at' => '2026-04-02',
                'amount' => 57.5,
            ]);

            Receipt::create([
                'invoice_id' => $invoice->id,
                'amount' => 20,
                'received_at' => '2026-04-04',
            ]);

            Receipt::create([
                'invoice_id' => $invoice->id,
                'amount' => 40,
                'received_at' => '2026-05-05',
            ]);

            $advance = AgentAdvance::create([
                'agent_id' => $agent->id,
                'amount' => 15,
                'applied_amount' => 15,
                'advanced_at' => '2026-04-05',
                'status' => 'applied',
            ]);

            DB::table('agent_advance_applications')->insert([
                [
                    'agent_advance_id' => $advance->id,
                    'invoice_id' => $invoice->id,
                    'amount' => 10,
                    'applied_at' => '2026-04-05',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'agent_advance_id' => $advance->id,
                    'invoice_id' => $invoice->id,
                    'amount' => 5,
                    'applied_at' => '2026-05-06',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            Invoice::create([
                'number' => 'INV-REPORTS-YTD-FUTURE',
                'issued_at' => '2026-05-01',
                'net_total' => 300,
                'vat_amount' => 45,
                'withholding' => 0,
                'status' => 'issued',
            ]);

            Expense::create([
                'date' => '2026-03-15',
                'category' => 'General',
                'amount' => 30,
                'status' => Expense::STATUS_RECORDED,
            ]);

            Expense::create([
                'date' => '2026-05-15',
                'category' => 'General',
                'amount' => 90,
                'status' => Expense::STATUS_RECORDED,
            ]);

            DB::table('production_runs')->insert([
                [
                    'quantity' => 12,
                    'qc_status' => 'approved',
                    'created_at' => '2026-04-01 10:00:00',
                    'updated_at' => '2026-04-01 10:00:00',
                ],
                [
                    'quantity' => 30,
                    'qc_status' => 'approved',
                    'created_at' => '2026-05-01 10:00:00',
                    'updated_at' => '2026-05-01 10:00:00',
                ],
            ]);

            $response = $this->actingAs($admin)->get(route('admin.reports.dashboard'));

            $response->assertOk();
            $response->assertViewHas('grossRevenue', 50.0);
            $response->assertViewHas('totalCollections', 20.0);
            $response->assertViewHas('outstanding', 27.5);
            $response->assertViewHas('totalExpenses', 30.0);
            $response->assertViewHas('netProfitEstimate', 20.0);
            $response->assertViewHas('productionQty', 12.0);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_system_alerts_include_users_with_secondary_admin_roles(): void
    {
        Notification::fake();

        $primaryAdmin = User::create([
            'name' => 'Primary Admin',
            'email' => 'primary-admin-alert@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);
        $secondaryAdmin = User::create([
            'name' => 'Secondary Admin',
            'email' => 'secondary-admin-alert@example.test',
            'password' => 'secret',
            'role' => 'sales_officer',
        ]);
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'regular-user-alert@example.test',
            'password' => 'secret',
            'role' => 'sales_officer',
        ]);

        DB::table('user_roles')->insert([
            'user_id' => $secondaryAdmin->id,
            'role_key' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(ErpNotificationService::class)->publishSystemAlerts([
            [
                'key' => 'system_alert_test',
                'title' => 'System alert',
                'message' => 'Test alert',
                'source' => 'System',
                'variant' => 'info',
            ],
        ], ['admin']);

        Notification::assertSentTo($primaryAdmin, SystemAlertNotification::class);
        Notification::assertSentTo($secondaryAdmin, SystemAlertNotification::class);
        Notification::assertNotSentTo($regularUser, SystemAlertNotification::class);
    }

    public function test_database_restore_is_blocked_outside_safe_environments(): void
    {
        $manager = app(DatabaseBackupManager::class);
        $originalEnvironment = $this->app['env'];
        $this->app['env'] = 'production';

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('disabled outside local/testing');

            $manager->restore('unsafe.sql');
        } finally {
            $this->app['env'] = $originalEnvironment;
        }
    }

    public function test_permission_manager_accepts_dotted_keys_and_protects_live_permissions(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super-admin-permissions@example.test',
            'password' => 'secret',
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($superAdmin)
            ->post(route('admin.permissions.store'), [
                'name' => 'finance.invoice.issue',
                'label' => 'Issue invoices',
                'group' => 'Accounting & Finance',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('permissions', [
            'name' => 'finance.invoice.issue',
        ]);

        $permission = Permission::create([
            'name' => 'system.settings',
            'label' => 'System settings',
            'group' => 'System',
        ]);

        $deleteResponse = $this->actingAs($superAdmin)
            ->from(route('admin.permissions.index'))
            ->delete(route('admin.permissions.destroy', $permission));

        $deleteResponse->assertSessionHasErrors('permission');
        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
        ]);
    }

    public function test_bulk_permission_update_only_changes_submitted_roles(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super-admin-matrix@example.test',
            'password' => 'secret',
            'role' => 'super_admin',
        ]);

        Permission::create([
            'name' => 'inventory.manage',
            'label' => 'Manage inventory',
            'group' => 'Inventory & Stock',
        ]);

        DB::table('role_permissions')->insert([
            [
                'role' => 'purchase_executive',
                'permission_name' => 'inventory.manage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role' => 'warehouse_officer',
                'permission_name' => 'inventory.manage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($superAdmin)
            ->post(route('admin.permissions.roles.update'), [
                'submitted_roles' => ['purchase_executive'],
                'role_permissions' => [
                    'purchase_executive' => [],
                ],
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('role_permissions', [
            'role' => 'purchase_executive',
            'permission_name' => 'inventory.manage',
        ]);
        $this->assertDatabaseHas('role_permissions', [
            'role' => 'warehouse_officer',
            'permission_name' => 'inventory.manage',
        ]);
    }

    public function test_supplier_return_index_respects_warehouse_scope(): void
    {
        $user = User::create([
            'name' => 'Scoped Purchase',
            'email' => 'scoped-purchase@example.test',
            'password' => 'secret',
            'role' => 'purchase_executive',
        ]);

        DB::table('role_permissions')->insert([
            'role' => 'purchase_executive',
            'permission_name' => 'control.suppliers',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $allowedWarehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'Allowed Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $blockedWarehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'Blocked Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('user_warehouse_scopes')->insert([
            'user_id' => $user->id,
            'warehouse_id' => $allowedWarehouseId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'sku' => 'SKU-SUP-RETURN',
            'name' => 'Supplier Return Product',
            'product_type' => 'raw',
            'base_price' => 10,
            'is_active' => true,
        ]);

        $allowedEntryId = DB::table('stock_entries')->insertGetId([
            'warehouse_id' => $allowedWarehouseId,
            'product_id' => $product->id,
            'quantity' => 10,
            'status' => 'available',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $blockedEntryId = DB::table('stock_entries')->insertGetId([
            'warehouse_id' => $blockedWarehouseId,
            'product_id' => $product->id,
            'quantity' => 10,
            'status' => 'available',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $allowedMovementId = DB::table('stock_movements')->insertGetId([
            'stock_entry_id' => $allowedEntryId,
            'type' => 'supplier-return',
            'quantity' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('stock_movements')->insert([
            'stock_entry_id' => $blockedEntryId,
            'type' => 'supplier-return',
            'quantity' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('admin.returns.supplier.index'));

        $response->assertOk();
        $response->assertViewHas('returns', function ($returns) use ($allowedMovementId) {
            return $returns->count() === 1
                && $returns->getCollection()->pluck('id')->all() === [$allowedMovementId];
        });
    }

    public function test_delivery_create_lists_only_active_vehicles(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-active-vehicles@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Vehicle Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'picked',
            'total' => 0,
        ]);

        $activeVehicleId = DB::table('vehicles')->insertGetId([
            'name' => 'Truck Active',
            'type' => 'truck',
            'license_plate' => 'ACT-001',
            'driver' => 'Driver A',
            'capacity_crates' => 50,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $inactiveVehicleId = DB::table('vehicles')->insertGetId([
            'name' => 'Truck Inactive',
            'type' => 'truck',
            'license_plate' => 'INA-001',
            'driver' => 'Driver B',
            'capacity_crates' => 50,
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.deliveries.create'));

        $response->assertOk();
        $response->assertViewHas('vehicles', function ($vehicles) use ($activeVehicleId, $inactiveVehicleId) {
            $ids = $vehicles->pluck('id')->all();

            return in_array($activeVehicleId, $ids, true) && ! in_array($inactiveVehicleId, $ids, true);
        });
    }

    public function test_inventory_materials_counts_only_available_raw_stock(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-materials@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $warehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'Materials Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'sku' => 'SKU-RAW-AVAIL',
            'name' => 'Raw Material',
            'product_type' => 'raw',
            'base_price' => 0,
            'is_active' => true,
        ]);

        DB::table('stock_entries')->insert([
            [
                'warehouse_id' => $warehouseId,
                'product_id' => $product->id,
                'quantity' => 10,
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'warehouse_id' => $warehouseId,
                'product_id' => $product->id,
                'quantity' => 5,
                'status' => 'reserved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.inventory.materials'));

        $response->assertOk();
        $response->assertViewHas('rows', function ($rows) {
            return $rows->count() === 1 && (float) $rows->first()->quantity === 10.0;
        });
    }

    public function test_agent_deletion_is_blocked_when_crm_history_exists(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-agent-history@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'History Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        DB::table('customer_gifts')->insert([
            'agent_id' => $agent->id,
            'date' => now()->toDateString(),
            'status' => CustomerGift::STATUS_PLANNED,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.agents.index'))
            ->delete(route('admin.agents.destroy', $agent));

        $response->assertSessionHasErrors('agent');
        $this->assertDatabaseHas('agents', [
            'id' => $agent->id,
        ]);
    }

    public function test_posted_finance_documents_cannot_be_deleted(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-finance-delete@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $invoice = Invoice::create([
            'number' => 'INV-HISTORY-001',
            'issued_at' => now()->toDateString(),
            'net_total' => 100,
            'vat_amount' => 15,
            'withholding' => 0,
            'status' => 'issued',
        ]);

        $receipt = Receipt::create([
            'invoice_id' => $invoice->id,
            'amount' => 50,
            'received_at' => now()->toDateString(),
            'reconciled' => true,
        ]);

        $creditNote = CreditNote::create([
            'invoice_id' => $invoice->id,
            'number' => 'CN-HISTORY-001',
            'issued_at' => now()->toDateString(),
            'amount' => 10,
        ]);

        $receiptDeleteResponse = $this->actingAs($admin)
            ->from(route('admin.finance.show', $invoice))
            ->delete(route('admin.finance.receipts.destroy', $receipt));

        $receiptDeleteResponse->assertSessionHasErrors('receipt');
        $this->assertDatabaseHas('receipts', [
            'id' => $receipt->id,
        ]);

        $creditDeleteResponse = $this->actingAs($admin)
            ->from(route('admin.finance.show', $invoice))
            ->delete(route('admin.finance.credit-notes.destroy', $creditNote));

        $creditDeleteResponse->assertSessionHasErrors('creditNote');
        $this->assertDatabaseHas('credit_notes', [
            'id' => $creditNote->id,
        ]);

        $invoiceDeleteResponse = $this->actingAs($admin)
            ->from(route('admin.finance.index'))
            ->delete(route('admin.finance.destroy', $invoice));

        $invoiceDeleteResponse->assertSessionHasErrors('invoice');
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
        ]);
    }

    public function test_sales_users_need_accounting_permission_before_posting_campaign_or_gift_ledger_entries(): void
    {
        $salesUser = User::create([
            'name' => 'Sales User',
            'email' => 'sales-ledger@example.test',
            'password' => 'secret',
            'role' => 'sales_officer',
        ]);

        DB::table('role_permissions')->insert([
            'role' => 'sales_officer',
            'permission_name' => 'sales.manage',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $agent = Agent::create([
            'name' => 'Gift Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $plannedCampaignResponse = $this->actingAs($salesUser)
            ->from(route('admin.campaigns.index'))
            ->post(route('admin.campaigns.store'), [
                'name' => 'Planned Trade Promo',
                'platform' => 'field',
                'start_date' => now()->toDateString(),
                'cost' => 100,
                'status' => Campaign::STATUS_PLANNED,
            ]);

        $plannedCampaignResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('campaigns', [
            'name' => 'Planned Trade Promo',
            'status' => Campaign::STATUS_PLANNED,
        ]);
        $this->assertDatabaseCount('ledger_entries', 0);

        $postedCampaignResponse = $this->actingAs($salesUser)
            ->post(route('admin.campaigns.store'), [
                'name' => 'Running Trade Promo',
                'platform' => 'field',
                'start_date' => now()->toDateString(),
                'cost' => 100,
                'status' => Campaign::STATUS_RUNNING,
            ]);

        $postedCampaignResponse->assertForbidden();
        $this->assertDatabaseMissing('campaigns', [
            'name' => 'Running Trade Promo',
        ]);

        $plannedGiftResponse = $this->actingAs($salesUser)
            ->from(route('admin.gifts.index'))
            ->post(route('admin.gifts.store'), [
                'agent_id' => $agent->id,
                'date' => now()->toDateString(),
                'amount' => 50,
                'status' => CustomerGift::STATUS_PLANNED,
            ]);

        $plannedGiftResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('customer_gifts', [
            'agent_id' => $agent->id,
            'status' => CustomerGift::STATUS_PLANNED,
        ]);
        $this->assertDatabaseCount('ledger_entries', 0);

        $postedGiftResponse = $this->actingAs($salesUser)
            ->post(route('admin.gifts.store'), [
                'agent_id' => $agent->id,
                'date' => now()->toDateString(),
                'amount' => 50,
                'status' => CustomerGift::STATUS_GIVEN,
            ]);

        $postedGiftResponse->assertForbidden();
        $this->assertDatabaseCount('customer_gifts', 1);
        $this->assertDatabaseCount('ledger_entries', 0);
    }

    public function test_inventory_expiry_dashboard_and_quick_writeoff_only_use_available_stock(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-expiry@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $warehouseId = DB::table('warehouses')->insertGetId([
            'name' => 'Expiry Warehouse',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'sku' => 'SKU-EXP-1',
            'name' => 'Expiring Product',
            'product_type' => 'finished',
            'base_price' => 10,
            'is_active' => true,
        ]);

        $batchId = DB::table('batches')->insertGetId([
            'product_id' => $product->id,
            'batch_code' => 'EXP-001',
            'production_date' => now()->subDays(5)->toDateString(),
            'expiry_date' => now()->addDays(5)->toDateString(),
            'qc_status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $availableEntryId = DB::table('stock_entries')->insertGetId([
            'warehouse_id' => $warehouseId,
            'product_id' => $product->id,
            'batch_id' => $batchId,
            'quantity' => 5,
            'status' => 'available',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $reservedEntryId = DB::table('stock_entries')->insertGetId([
            'warehouse_id' => $warehouseId,
            'product_id' => $product->id,
            'batch_id' => $batchId,
            'quantity' => 4,
            'status' => 'reserved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.inventory.index'));

        $response->assertOk();
        $response->assertViewHas('expiringSoon', function ($entries) use ($availableEntryId, $reservedEntryId) {
            $ids = $entries->pluck('id')->all();

            return in_array($availableEntryId, $ids, true) && ! in_array($reservedEntryId, $ids, true);
        });

        $writeOffResponse = $this->actingAs($admin)
            ->from(route('admin.inventory.index'))
            ->post(route('admin.stock.entries.writeoff', $reservedEntryId));

        $writeOffResponse->assertSessionHasErrors('entry');
        $this->assertDatabaseHas('stock_entries', [
            'id' => $reservedEntryId,
            'quantity' => 4,
            'status' => 'reserved',
        ]);
        $this->assertDatabaseMissing('stock_movements', [
            'stock_entry_id' => $reservedEntryId,
            'type' => 'expired',
        ]);
    }

    public function test_vehicle_load_uses_packaging_master_data_instead_of_a_fixed_divisor(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-load@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Load Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $bottlePackagingId = DB::table('packaging_types')->insertGetId([
            'name' => 'Bottle 500ml',
            'unit' => 'bottle',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $cartonPackagingId = DB::table('packaging_types')->insertGetId([
            'name' => 'Carton 6 x 500ml',
            'unit' => 'carton',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('packaging_conversions')->insert([
            'from_packaging_type_id' => $bottlePackagingId,
            'to_packaging_type_id' => $cartonPackagingId,
            'factor' => 6,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'sku' => 'SKU-LOAD-1',
            'name' => 'Load Product',
            'product_type' => 'finished',
            'packaging_type_id' => $bottlePackagingId,
            'base_price' => 20,
            'is_active' => true,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'scheduled',
            'delivery_date' => '2026-03-20',
            'total' => 0,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 24,
            'unit_price' => 20,
            'order_type' => 'regular',
            'commission_amount' => 0,
        ]);

        $vehicleId = DB::table('vehicles')->insertGetId([
            'name' => 'Load Truck',
            'capacity_crates' => 50,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Delivery::create([
            'order_id' => $order->id,
            'vehicle_id' => $vehicleId,
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.vehicle-load.index', [
            'date' => '2026-03-20',
        ]));

        $response->assertOk();
        $response->assertViewHas('rows', function ($rows) use ($vehicleId) {
            return isset($rows[$vehicleId]) && (int) $rows[$vehicleId]['crateLoad'] === 4;
        });
    }

    public function test_packaging_conversion_rejects_self_conversion(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-packaging-conversion@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $packagingTypeId = DB::table('packaging_types')->insertGetId([
            'name' => 'Bottle 500ml',
            'unit' => 'bottle',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.packaging.index'))
            ->post(route('admin.packaging.conversions.store'), [
                'from_packaging_type_id' => $packagingTypeId,
                'to_packaging_type_id' => $packagingTypeId,
                'factor' => 1,
            ]);

        $response->assertSessionHasErrors('to_packaging_type_id');
        $this->assertDatabaseCount('packaging_conversions', 0);
    }

    public function test_tax_classes_reject_duplicate_names_and_codes_after_normalization(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-tax-class@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $firstResponse = $this->actingAs($admin)
            ->from(route('admin.tax-classes.index'))
            ->post(route('admin.tax-classes.store'), [
                'name' => 'Standard VAT 15%',
                'hsn_code' => 'HSN-001',
                'local_tax_code' => 'VAT-001',
                'rate' => 15,
            ]);

        $firstResponse->assertSessionHasNoErrors();

        $duplicateResponse = $this->actingAs($admin)
            ->from(route('admin.tax-classes.index'))
            ->post(route('admin.tax-classes.store'), [
                'name' => ' Standard VAT 15% ',
                'hsn_code' => ' HSN-001 ',
                'local_tax_code' => ' VAT-001 ',
                'rate' => 10,
            ]);

        $duplicateResponse->assertSessionHasErrors(['name', 'hsn_code', 'local_tax_code']);
        $this->assertDatabaseCount('tax_classes', 1);
    }

    public function test_admin_dashboard_counts_only_active_agents(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-dashboard-agents@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        Agent::create([
            'name' => 'Active Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);
        Agent::create([
            'name' => 'Inactive Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('metrics', function ($metrics) {
            $metric = collect($metrics)->firstWhere('label', 'Active agents');

            return $metric !== null && ($metric['value'] ?? null) === '1';
        });
    }

    public function test_admin_dashboard_outstanding_uses_receipts_credits_and_advances(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-dashboard-outstanding@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $agent = Agent::create([
            'name' => 'Advance Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $advance = AgentAdvance::create([
            'agent_id' => $agent->id,
            'amount' => 20,
            'applied_amount' => 20,
            'advanced_at' => now()->toDateString(),
            'status' => 'applied',
        ]);

        $invoice = Invoice::create([
            'number' => 'INV-DASH-001',
            'issued_at' => now()->toDateString(),
            'net_total' => 100,
            'vat_amount' => 15,
            'withholding' => 0,
            'status' => 'issued',
        ]);

        Receipt::create([
            'invoice_id' => $invoice->id,
            'amount' => 10,
            'received_at' => now()->toDateString(),
        ]);

        CreditNote::create([
            'invoice_id' => $invoice->id,
            'number' => 'CN-DASH-001',
            'issued_at' => now()->toDateString(),
            'amount' => 15,
        ]);

        DB::table('agent_advance_applications')->insert([
            'agent_advance_id' => $advance->id,
            'invoice_id' => $invoice->id,
            'amount' => 20,
            'applied_at' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('outstandingReceivables', 70.0);
    }

    public function test_admin_dashboard_current_month_metrics_exclude_future_records(): void
    {
        Carbon::setTestNow('2026-04-06 12:00:00');

        try {
            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin-dashboard-current-month@example.test',
                'password' => 'secret',
                'role' => 'admin',
            ]);

            $agent = Agent::create([
                'name' => 'Dashboard Current Month Agent',
                'credit_limit' => 10000,
                'withholding_rate' => 0,
                'is_active' => true,
            ]);

            $advance = AgentAdvance::create([
                'agent_id' => $agent->id,
                'amount' => 15,
                'applied_amount' => 15,
                'advanced_at' => '2026-04-05',
                'status' => 'applied',
            ]);

            $invoice = Invoice::create([
                'number' => 'INV-ADMIN-DASH-001',
                'issued_at' => '2026-04-03',
                'net_total' => 100,
                'vat_amount' => 15,
                'withholding' => 0,
                'status' => 'issued',
            ]);

            CreditNote::create([
                'invoice_id' => $invoice->id,
                'number' => 'CN-ADMIN-DASH-001',
                'issued_at' => '2026-04-05',
                'amount' => 57.5,
            ]);

            Receipt::create([
                'invoice_id' => $invoice->id,
                'amount' => 20,
                'received_at' => '2026-04-04',
            ]);

            Receipt::create([
                'invoice_id' => $invoice->id,
                'amount' => 40,
                'received_at' => '2026-04-20',
            ]);

            DB::table('agent_advance_applications')->insert([
                [
                    'agent_advance_id' => $advance->id,
                    'invoice_id' => $invoice->id,
                    'amount' => 10,
                    'applied_at' => '2026-04-05',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'agent_advance_id' => $advance->id,
                    'invoice_id' => $invoice->id,
                    'amount' => 5,
                    'applied_at' => '2026-04-22',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            Invoice::create([
                'number' => 'INV-ADMIN-DASH-FUTURE',
                'issued_at' => '2026-04-20',
                'net_total' => 200,
                'vat_amount' => 30,
                'withholding' => 0,
                'status' => 'issued',
            ]);

            $response = $this->actingAs($admin)->get(route('admin.dashboard', [
                'sales_range' => 3,
            ]));

            $response->assertOk();
            $response->assertViewHas('monthlyAchieved', 50.0);
            $response->assertViewHas('outstandingReceivables', 27.5);
            $response->assertViewHas('monthlyRevenue', function (array $monthlyRevenue) {
                $values = array_map('floatval', $monthlyRevenue);

                return $values === [0.0, 0.0, 50.0];
            });
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_agent_performance_outstanding_subtracts_advances(): void
    {
        $agent = Agent::create([
            'name' => 'Agent Performance Advance',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 0,
        ]);

        $invoice = Invoice::create([
            'order_id' => $order->id,
            'number' => 'INV-AGENT-PERF-001',
            'issued_at' => now()->toDateString(),
            'net_total' => 100,
            'vat_amount' => 15,
            'withholding' => 0,
            'status' => 'issued',
        ]);

        Receipt::create([
            'invoice_id' => $invoice->id,
            'amount' => 20,
            'received_at' => now()->toDateString(),
        ]);

        $advance = AgentAdvance::create([
            'agent_id' => $agent->id,
            'amount' => 30,
            'applied_amount' => 30,
            'advanced_at' => now()->toDateString(),
            'status' => 'applied',
        ]);

        DB::table('agent_advance_applications')->insert([
            'agent_advance_id' => $advance->id,
            'invoice_id' => $invoice->id,
            'amount' => 30,
            'applied_at' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $view = app(ReportController::class)->agentPerformance(
            Request::create('/admin/reports/agents', 'GET', [
                'from' => now()->startOfMonth()->toDateString(),
                'to' => now()->endOfMonth()->toDateString(),
            ])
        );

        $row = $view->getData()['rows']->first();

        $this->assertEquals(65.0, (float) $row['outstanding']);
    }

    public function test_agent_performance_defaults_to_month_to_date_and_excludes_future_advances(): void
    {
        Carbon::setTestNow('2026-04-06 12:00:00');

        try {
            $agent = Agent::create([
                'name' => 'Agent Performance Current Month',
                'credit_limit' => 10000,
                'withholding_rate' => 0,
                'is_active' => true,
            ]);

            $currentOrder = Order::create([
                'agent_id' => $agent->id,
                'order_type' => 'regular',
                'status' => 'delivered',
                'total' => 0,
            ]);

            $currentInvoice = Invoice::create([
                'order_id' => $currentOrder->id,
                'number' => 'INV-AGENT-PERF-CURRENT',
                'issued_at' => '2026-04-03',
                'net_total' => 100,
                'vat_amount' => 15,
                'withholding' => 0,
                'status' => 'issued',
            ]);

            $futureOrder = Order::create([
                'agent_id' => $agent->id,
                'order_type' => 'regular',
                'status' => 'delivered',
                'total' => 0,
            ]);

            Invoice::create([
                'order_id' => $futureOrder->id,
                'number' => 'INV-AGENT-PERF-FUTURE',
                'issued_at' => '2026-04-20',
                'net_total' => 200,
                'vat_amount' => 30,
                'withholding' => 0,
                'status' => 'issued',
            ]);

            Receipt::create([
                'invoice_id' => $currentInvoice->id,
                'amount' => 20,
                'received_at' => '2026-04-04',
            ]);

            Receipt::create([
                'invoice_id' => $currentInvoice->id,
                'amount' => 40,
                'received_at' => '2026-04-20',
            ]);

            $advance = AgentAdvance::create([
                'agent_id' => $agent->id,
                'amount' => 35,
                'applied_amount' => 35,
                'advanced_at' => '2026-04-05',
                'status' => 'applied',
            ]);

            DB::table('agent_advance_applications')->insert([
                [
                    'agent_advance_id' => $advance->id,
                    'invoice_id' => $currentInvoice->id,
                    'amount' => 30,
                    'applied_at' => '2026-04-05',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'agent_advance_id' => $advance->id,
                    'invoice_id' => $currentInvoice->id,
                    'amount' => 5,
                    'applied_at' => '2026-04-22',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);

            $view = app(ReportController::class)->agentPerformance(
                Request::create('/admin/reports/agents', 'GET')
            );

            $row = $view->getData()['rows']->first();

            $this->assertEquals(1, (int) $row['invoice_count']);
            $this->assertEquals(115.0, (float) $row['invoiced']);
            $this->assertEquals(20.0, (float) $row['receipts']);
            $this->assertEquals(30.0, (float) $row['advances']);
            $this->assertEquals(65.0, (float) $row['outstanding']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_production_summary_sales_exclude_vat_and_credit_notes(): void
    {
        $invoice = Invoice::create([
            'number' => 'INV-PROD-001',
            'issued_at' => now()->toDateString(),
            'net_total' => 100,
            'vat_amount' => 15,
            'withholding' => 0,
            'status' => 'issued',
        ]);

        CreditNote::create([
            'invoice_id' => $invoice->id,
            'number' => 'CN-PROD-001',
            'issued_at' => now()->toDateString(),
            'amount' => 57.5,
        ]);

        $view = app(ReportController::class)->productionSummary(
            Request::create('/admin/reports/production', 'GET', [
                'from' => now()->startOfMonth()->toDateString(),
                'to' => now()->endOfMonth()->toDateString(),
            ])
        );

        $this->assertEquals(50.0, (float) $view->getData()['salesTotal']);
    }

    public function test_settlement_status_update_requires_accounting_permission(): void
    {
        $user = User::create([
            'name' => 'Sales Agent Admin',
            'email' => 'sales-agent-admin@example.test',
            'password' => 'secret',
            'role' => 'sales_manager',
        ]);

        DB::table('role_permissions')->insert([
            'role' => 'sales_manager',
            'permission_name' => 'control.agents',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $agent = Agent::create([
            'name' => 'Settlement Agent',
            'credit_limit' => 10000,
            'withholding_rate' => 0,
            'is_active' => true,
        ]);

        $settlement = AgentCommissionSettlement::create([
            'agent_id' => $agent->id,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'sales_total' => 1000,
            'commission_total' => 100,
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)
            ->patch(route('admin.settlements.update-status', $settlement), [
                'status' => 'approved',
            ]);

        $response->assertForbidden();
    }

    public function test_purchase_bill_with_finance_posting_cannot_be_deleted(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-bill-delete@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $supplierId = DB::table('suppliers')->insertGetId([
            'name' => 'Posted Supplier',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $billId = DB::table('purchase_bills')->insertGetId([
            'supplier_id' => $supplierId,
            'number' => 'PB-000001',
            'bill_date' => now()->toDateString(),
            'net_total' => 500,
            'vat_amount' => 0,
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ledger_entries')->insert([
            [
                'account' => 'Purchases',
                'description' => 'Purchase bill PB-000001',
                'debit' => 500,
                'credit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account' => 'Accounts Payable',
                'description' => 'Purchase bill PB-000001',
                'debit' => 0,
                'credit' => 500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('bill_payments')->insert([
            'purchase_bill_id' => $billId,
            'amount' => 100,
            'paid_at' => now()->toDateString(),
            'method' => 'bank',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.bills.index'))
            ->delete(route('admin.bills.destroy', $billId));

        $response->assertSessionHasErrors('bill');
        $this->assertDatabaseHas('purchase_bills', [
            'id' => $billId,
        ]);
    }

    public function test_posted_expenses_cannot_be_deleted(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-expense-delete@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $expense = Expense::create([
            'date' => now()->toDateString(),
            'category' => 'Utilities',
            'description' => 'Electricity bill',
            'amount' => 250,
            'status' => Expense::STATUS_RECORDED,
        ]);

        DB::table('ledger_entries')->insert([
            [
                'account' => 'Utilities Expense',
                'description' => 'Expense #' . $expense->id,
                'debit' => 250,
                'credit' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account' => 'Bank',
                'description' => 'Expense #' . $expense->id,
                'debit' => 0,
                'credit' => 250,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.expenses.index'))
            ->delete(route('admin.expenses.destroy', $expense));

        $response->assertSessionHasErrors('expense');
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
        ]);
    }

    public function test_salary_distributions_cannot_be_deleted(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-salary-delete@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $employeeId = DB::table('employees')->insertGetId([
            'name' => 'Payroll Employee',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $distributionId = DB::table('salary_distributions')->insertGetId([
            'employee_id' => $employeeId,
            'period_start' => '2026-03-01',
            'period_end' => '2026-03-31',
            'base_salary' => 1000,
            'bonus' => 50,
            'ta_allowances' => 20,
            'da_allowances' => 30,
            'commission' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.salary-distributions.index'))
            ->delete(route('admin.salary-distributions.destroy', $distributionId));

        $response->assertSessionHasErrors('salaryDistribution');
        $this->assertDatabaseHas('salary_distributions', [
            'id' => $distributionId,
        ]);
    }

    public function test_hr_history_records_cannot_be_deleted(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-hr-history@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $employeeId = DB::table('employees')->insertGetId([
            'name' => 'History Employee',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $contractId = DB::table('employee_contracts')->insertGetId([
            'employee_id' => $employeeId,
            'reference' => 'CT-001',
            'start_date' => '2026-01-01',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $allowanceId = DB::table('employee_allowances')->insertGetId([
            'employee_id' => $employeeId,
            'date' => now()->toDateString(),
            'type' => 'travel',
            'amount' => 20,
            'status' => 'submitted',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $equipmentId = DB::table('employee_equipment')->insertGetId([
            'employee_id' => $employeeId,
            'effective_date' => now()->toDateString(),
            'product_name' => 'Tablet',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $leaveId = DB::table('employee_leaves')->insertGetId([
            'employee_id' => $employeeId,
            'start_date' => '2026-03-10',
            'end_date' => '2026-03-11',
            'type' => 'casual',
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $locationId = DB::table('employee_location_logs')->insertGetId([
            'employee_id' => $employeeId,
            'logged_at' => now(),
            'location_label' => 'Dhaka Office',
            'source' => 'manual',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->from(route('admin.employees.contracts.index', $employeeId))
            ->delete(route('admin.employees.contracts.destroy', [$employeeId, $contractId]))
            ->assertSessionHasErrors('contract');

        $this->actingAs($admin)
            ->from(route('admin.employees.allowances.index', $employeeId))
            ->delete(route('admin.employees.allowances.destroy', [$employeeId, $allowanceId]))
            ->assertSessionHasErrors('allowance');

        $this->actingAs($admin)
            ->from(route('admin.employees.equipment.index', $employeeId))
            ->delete(route('admin.employees.equipment.destroy', [$employeeId, $equipmentId]))
            ->assertSessionHasErrors('equipment');

        $this->actingAs($admin)
            ->from(route('admin.employees.leaves.index', $employeeId))
            ->delete(route('admin.employees.leaves.destroy', [$employeeId, $leaveId]))
            ->assertSessionHasErrors('leave');

        $this->actingAs($admin)
            ->from(route('admin.employees.locations.index', $employeeId))
            ->delete(route('admin.employees.locations.destroy', [$employeeId, $locationId]))
            ->assertSessionHasErrors('location');

        $this->assertDatabaseHas('employee_contracts', ['id' => $contractId]);
        $this->assertDatabaseHas('employee_allowances', ['id' => $allowanceId]);
        $this->assertDatabaseHas('employee_equipment', ['id' => $equipmentId]);
        $this->assertDatabaseHas('employee_leaves', ['id' => $leaveId]);
        $this->assertDatabaseHas('employee_location_logs', ['id' => $locationId]);
    }

    public function test_menu_management_follows_permission_matrix(): void
    {
        $deniedUser = User::create([
            'name' => 'Denied User',
            'email' => 'menu-denied@example.test',
            'password' => 'secret',
            'role' => 'sales_officer',
        ]);
        $allowedUser = User::create([
            'name' => 'Allowed User',
            'email' => 'menu-allowed@example.test',
            'password' => 'secret',
            'role' => 'accounts_officer',
        ]);

        DB::table('role_permissions')->insert([
            'role' => 'accounts_officer',
            'permission_name' => 'permissions.manage',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($deniedUser)
            ->get(route('admin.menu.index'))
            ->assertForbidden();

        $this->actingAs($allowedUser)
            ->get(route('admin.menu.index'))
            ->assertOk();
    }

    public function test_batch_notifications_include_users_with_secondary_qc_roles(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-batch-notify@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $secondaryQcUser = User::create([
            'name' => 'Secondary QC',
            'email' => 'secondary-qc@example.test',
            'password' => 'secret',
            'role' => 'sales_officer',
        ]);

        DB::table('user_roles')->insert([
            'user_id' => $secondaryQcUser->id,
            'role_key' => 'qc_officer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $product = Product::create([
            'sku' => 'SKU-BATCH-SEC',
            'name' => 'Secondary Role Product',
            'product_type' => 'finished',
            'base_price' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.batches.index'))
            ->post(route('admin.batches.store'), [
                'product_id' => $product->id,
                'batch_code' => 'BATCH-SEC-001',
                'production_date' => now()->toDateString(),
                'expiry_date' => now()->addDays(30)->toDateString(),
                'qc_status' => 'pending',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $secondaryQcUser->id,
            'type' => \App\Notifications\NewBatchCreated::class,
        ]);
    }

    public function test_profile_avatar_update_requires_employee_control_permission(): void
    {
        Storage::fake('public');

        $employeeId = DB::table('employees')->insertGetId([
            'name' => 'Profile Employee',
            'photo_path' => 'employees/photos/original.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Regular User',
            'email' => 'profile-user@example.test',
            'password' => 'secret',
            'role' => 'sales_officer',
            'employee_id' => $employeeId,
        ]);

        $response = $this->actingAs($user)
            ->from(route('admin.profile.edit'))
            ->patch(route('admin.profile.update'), [
                'name' => 'Regular User',
                'email' => 'profile-user@example.test',
                'avatar' => UploadedFile::fake()->image('avatar.jpg'),
            ]);

        $response->assertSessionHasErrors('avatar');
        $this->assertDatabaseHas('employees', [
            'id' => $employeeId,
            'photo_path' => 'employees/photos/original.jpg',
        ]);
    }

    public function test_delivery_route_creation_rejects_inactive_vehicles_and_duplicate_definitions(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'route-admin@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $inactiveVehicleId = DB::table('vehicles')->insertGetId([
            'name' => 'Inactive Route Truck',
            'license_plate' => 'ROUTE-INACTIVE',
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $activeVehicleId = DB::table('vehicles')->insertGetId([
            'name' => 'Active Route Truck',
            'license_plate' => 'ROUTE-ACTIVE',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $inactiveResponse = $this->actingAs($admin)
            ->from(route('admin.delivery-routes.index'))
            ->post(route('admin.delivery-routes.store'), [
                'name' => 'North Route',
                'zone' => 'Zone 1',
                'day' => 'Monday',
                'vehicle_id' => $inactiveVehicleId,
            ]);

        $inactiveResponse->assertSessionHasErrors('vehicle_id');

        $createResponse = $this->actingAs($admin)
            ->from(route('admin.delivery-routes.index'))
            ->post(route('admin.delivery-routes.store'), [
                'name' => 'North Route',
                'zone' => 'Zone 1',
                'day' => 'Monday',
                'vehicle_id' => $activeVehicleId,
            ]);

        $createResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('delivery_routes', [
            'name' => 'North Route',
            'zone' => 'Zone 1',
            'day' => 'Monday',
            'vehicle_id' => $activeVehicleId,
        ]);

        $duplicateResponse = $this->actingAs($admin)
            ->from(route('admin.delivery-routes.index'))
            ->post(route('admin.delivery-routes.store'), [
                'name' => ' North Route ',
                'zone' => ' zone 1 ',
                'day' => ' monday ',
                'vehicle_id' => $activeVehicleId,
            ]);

        $duplicateResponse->assertSessionHasErrors('name');
        $this->assertDatabaseCount('delivery_routes', 1);
    }

    public function test_vehicle_creation_rejects_duplicate_name_and_license_plate(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'vehicle-admin@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        DB::table('vehicles')->insert([
            'name' => 'Delivery Van 1',
            'license_plate' => 'DHK-1234',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $duplicateNameResponse = $this->actingAs($admin)
            ->from(route('admin.vehicles.index'))
            ->post(route('admin.vehicles.store'), [
                'name' => 'Delivery Van 1',
                'license_plate' => 'DHK-9999',
            ]);

        $duplicateNameResponse->assertSessionHasErrors('name');

        $duplicatePlateResponse = $this->actingAs($admin)
            ->from(route('admin.vehicles.index'))
            ->post(route('admin.vehicles.store'), [
                'name' => 'Delivery Van 2',
                'license_plate' => ' dhk-1234 ',
            ]);

        $duplicatePlateResponse->assertSessionHasErrors('license_plate');
        $this->assertDatabaseCount('vehicles', 1);
    }

    public function test_employee_location_logs_validate_coordinate_bounds_and_source_values(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'location-admin@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $employeeId = DB::table('employees')->insertGetId([
            'name' => 'Field Employee',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.employees.locations.index', $employeeId))
            ->post(route('admin.employees.locations.store', $employeeId), [
                'logged_at' => now()->format('Y-m-d H:i:s'),
                'latitude' => 120.25,
                'longitude' => 181.5,
                'source' => 'spoofed',
                'location_label' => 'Invalid Point',
            ]);

        $response->assertSessionHasErrors(['latitude', 'longitude', 'source']);
        $this->assertDatabaseCount('employee_location_logs', 0);
    }

    public function test_menu_path_validation_rejects_non_get_admin_endpoints(): void
    {
        $user = User::create([
            'name' => 'Menu Manager',
            'email' => 'menu-manager@example.test',
            'password' => 'secret',
            'role' => 'accounts_officer',
        ]);

        DB::table('role_permissions')->insert([
            'role' => 'accounts_officer',
            'permission_name' => 'permissions.manage',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $groupId = DB::table('menu_groups')->insertGetId([
            'title' => 'System',
            'key' => 'system',
            'position' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $invalidResponse = $this->actingAs($user)
            ->from(route('admin.menu.index'))
            ->post(route('admin.menu.items.store'), [
                'menu_group_id' => $groupId,
                'name' => 'Restore Backup',
                'path' => '/admin/settings/backups/restore',
            ]);

        $invalidResponse->assertSessionHasErrors('path');
        $this->assertDatabaseCount('menu_items', 0);

        $validResponse = $this->actingAs($user)
            ->from(route('admin.menu.index'))
            ->post(route('admin.menu.items.store'), [
                'menu_group_id' => $groupId,
                'name' => 'Settings',
                'path' => '/admin/settings',
            ]);

        $validResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('menu_items', [
            'menu_group_id' => $groupId,
            'name' => 'Settings',
            'path' => '/admin/settings',
        ]);
    }

    protected function bootInMemorySqlite(): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('database.connections.sqlite.foreign_key_constraints', false);

        DB::purge('sqlite');
        DB::reconnect('sqlite');
    }

    protected function createMinimalSchema(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('zone')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('withholding_rate', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('admin');
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('work_email')->nullable();
            $table->string('work_phone')->nullable();
            $table->string('work_mobile')->nullable();
            $table->string('department')->nullable();
            $table->string('job_position')->nullable();
            $table->string('work_zone')->nullable();
            $table->text('tags')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('cv_path')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('reference');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('working_schedule')->nullable();
            $table->decimal('salary_amount', 14, 2)->default(0);
            $table->decimal('travel_allowance', 14, 2)->default(0);
            $table->decimal('dearness_allowance', 14, 2)->default(0);
            $table->decimal('bonus', 14, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('employee_allowances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->string('type');
            $table->string('reference')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->text('description')->nullable();
            $table->string('status')->default('submitted');
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_equipment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('effective_date');
            $table->string('product_name');
            $table->string('device_identifier')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_leaves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('type');
            $table->string('reason')->nullable();
            $table->string('status')->default('pending');
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_location_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('event_type')->nullable();
            $table->timestamp('logged_at');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_label')->nullable();
            $table->string('source')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employee_badges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('badge_id');
            $table->date('granted_at')->nullable();
            $table->string('granted_by')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['employee_id', 'badge_id', 'granted_at'], 'employee_badges_unique_grant');
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->string('permission_name');
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->boolean('is_system')->default(true);
            $table->timestamps();
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('role_key');
            $table->timestamps();
            $table->unique(['user_id', 'role_key']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_dispatch_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('dedupe_key');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'dedupe_key']);
        });

        Schema::create('menu_groups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('key')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_group_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->string('key')->nullable();
            $table->string('icon')->nullable();
            $table->string('path')->nullable();
            $table->string('permission')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('user_warehouse_scopes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->timestamps();
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('truck');
            $table->string('license_plate')->nullable();
            $table->string('driver')->nullable();
            $table->integer('capacity_crates')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('delivery_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('zone')->nullable();
            $table->string('day')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->string('driver')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('route_id')->nullable();
            $table->date('scheduled_date');
            $table->string('driver')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('packaging_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('unit')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('packaging_conversions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_packaging_type_id');
            $table->unsignedBigInteger('to_packaging_type_id');
            $table->decimal('factor', 12, 4)->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['from_packaging_type_id', 'to_packaging_type_id'], 'packaging_conversion_unique');
        });

        Schema::create('tax_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('hsn_code')->nullable()->unique();
            $table->string('local_tax_code')->nullable()->unique();
            $table->decimal('rate', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('product_type')->nullable();
            $table->unsignedBigInteger('packaging_type_id')->nullable();
            $table->unsignedBigInteger('tax_class_id')->nullable();
            $table->decimal('base_price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('batch_code');
            $table->date('production_date');
            $table->date('expiry_date')->nullable();
            $table->string('qc_status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('warehouse_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id');
            $table->string('code');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('goods_receipt_id')->nullable();
            $table->unsignedBigInteger('purchase_order_item_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('warehouse_location_id')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('line_total', 14, 2)->nullable();
            $table->string('qc_status')->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->decimal('material_unit_cost', 14, 4)->nullable();
            $table->timestamps();
        });

        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_of_materials_id');
            $table->unsignedBigInteger('component_product_id');
            $table->decimal('quantity', 12, 4)->default(0);
            $table->decimal('unit_cost', 12, 4)->nullable();
            $table->string('unit')->nullable();
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_id')->nullable();
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('platform');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('reach')->nullable();
            $table->unsignedInteger('impressions')->nullable();
            $table->decimal('cost', 14, 2)->nullable();
            $table->string('status')->default('planned');
            $table->string('campaign_code')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_gifts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->date('date');
            $table->string('occasion')->nullable();
            $table->string('gift_type')->nullable();
            $table->string('description')->nullable();
            $table->decimal('amount', 14, 2)->nullable();
            $table->string('campaign_code')->nullable();
            $table->string('status')->default('planned');
            $table->timestamps();
        });

        Schema::create('visit_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('agent_id');
            $table->date('date');
            $table->string('status')->default('planned');
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('category');
            $table->string('description')->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('reference')->nullable();
            $table->string('status')->default('recorded');
            $table->timestamps();
        });

        Schema::create('salary_distributions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->date('period_start');
            $table->date('period_end')->nullable();
            $table->decimal('base_salary', 14, 2)->default(0);
            $table->decimal('bonus', 14, 2)->default(0);
            $table->decimal('ta_allowances', 14, 2)->default(0);
            $table->decimal('da_allowances', 14, 2)->default(0);
            $table->decimal('commission', 14, 2)->default(0);
            $table->timestamps();
            $table->unique(['employee_id', 'period_start', 'period_end']);
        });

        Schema::create('production_runs', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->string('line')->nullable();
            $table->string('shift')->nullable();
            $table->decimal('quantity', 14, 2)->default(0);
            $table->decimal('material_unit_cost', 14, 2)->nullable();
            $table->decimal('material_total_cost', 14, 2)->nullable();
            $table->string('status')->nullable();
            $table->string('qc_status')->nullable();
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('stock_confirmed_at')->nullable();
            $table->unsignedBigInteger('stock_confirmed_by')->nullable();
            $table->text('notes')->nullable();
            $table->text('materials_reserved')->nullable();
            $table->timestamps();
        });

        Schema::create('production_material_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_run_id');
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('production_material_issue_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_material_issue_id');
            $table->unsignedBigInteger('component_product_id');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->decimal('quantity', 12, 4)->default(0);
            $table->decimal('unit_cost', 12, 4)->nullable();
            $table->decimal('line_total', 14, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->string('number')->unique();
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->decimal('net_total', 14, 2)->default(0);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('purchase_bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_bill_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('description');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_bill_id');
            $table->decimal('amount', 14, 2)->default(0);
            $table->date('paid_at');
            $table->string('method')->nullable();
            $table->timestamps();
        });

        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->unsignedBigInteger('purchase_bill_id')->nullable();
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('grn_number')->unique();
            $table->timestamp('received_at');
            $table->string('status')->default('posted');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id');
            $table->string('number')->unique();
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('description');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('line_total', 14, 2)->nullable();
            $table->decimal('received_quantity', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('agent_price_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('price', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_commission_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->string('sku')->nullable();
            $table->string('type')->default('percentage');
            $table->decimal('value', 10, 2)->default(0);
            $table->string('order_type')->nullable();
            $table->string('frequency')->default('per_order');
            $table->decimal('threshold_min', 14, 2)->nullable();
            $table->decimal('threshold_max', 14, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->string('order_type')->default('regular');
            $table->string('agent_reference')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('delivery_contact_name')->nullable();
            $table->string('delivery_contact_phone')->nullable();
            $table->text('delivery_address')->nullable();
            $table->string('status')->default('draft');
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('commission_total', 14, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_credit_used')->default(false);
            $table->string('payment_mode')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->string('order_type')->nullable();
            $table->decimal('commission_rate', 6, 2)->nullable();
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('status');
            $table->timestamp('changed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_commission_settlements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('sales_total', 14, 2);
            $table->decimal('commission_total', 14, 2);
            $table->string('status')->default('open');
            $table->timestamp('accrued_at')->nullable();
            $table->date('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('target_value', 14, 2);
            $table->timestamps();
            $table->unique(['employee_id', 'period_start', 'period_end'], 'sales_targets_employee_period_unique');
            $table->unique(['agent_id', 'period_start', 'period_end'], 'sales_targets_agent_period_unique');
        });

        Schema::create('agent_advances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->decimal('amount', 14, 2);
            $table->decimal('applied_amount', 14, 2)->default(0);
            $table->date('advanced_at');
            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('agent_advance_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_advance_id');
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('amount', 14, 2);
            $table->date('applied_at');
            $table->timestamps();
        });

        Schema::create('stock_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_bill_id')->nullable();
            $table->unsignedBigInteger('goods_receipt_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('warehouse_location_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->string('status')->default('available');
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_entry_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('type');
            $table->decimal('quantity', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->decimal('system_quantity', 12, 2)->default(0);
            $table->decimal('counted_quantity', 12, 2)->default(0);
            $table->decimal('variance', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('number')->unique();
            $table->date('issued_at');
            $table->date('due_at')->nullable();
            $table->decimal('net_total', 14, 2);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('withholding', 14, 2)->default(0);
            $table->string('status')->default('issued');
            $table->timestamps();
        });

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();
            $table->unsignedBigInteger('route_id')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->string('status')->default('scheduled');
            $table->integer('sequence')->nullable();
            $table->string('pod_photo')->nullable();
            $table->text('exception_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_id');
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->decimal('qty_dispatched', 12, 2)->default(0);
            $table->decimal('qty_delivered', 12, 2)->default(0);
            $table->decimal('qty_short', 12, 2)->default(0);
            $table->decimal('qty_damaged', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_pods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_id');
            $table->string('signed_by')->nullable();
            $table->string('signature_path')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 14, 2);
            $table->timestamps();
        });

        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('amount', 14, 2);
            $table->string('payment_method')->nullable();
            $table->date('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('reconciled')->default(false);
            $table->timestamps();
        });

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('number')->unique();
            $table->date('issued_at');
            $table->decimal('amount', 14, 2);
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->string('account');
            $table->text('description')->nullable();
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->timestamps();
        });
    }
}
