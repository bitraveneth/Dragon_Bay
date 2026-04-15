<?php

namespace Tests\Feature;

use App\Helpers\SystemSettings;
use App\Models\Agent;
use App\Models\AgentCommissionSettlement;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\Shipment;
use App\Models\ShipmentExpense;
use App\Models\ShipmentPackage;
use App\Models\ShipmentRateCard;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LogisticsShipmentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Logistics shipment tests require pdo_sqlite.');
        }

        $this->bootInMemorySqlite();
        $this->createMinimalSchema();
        SystemSettings::flush();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    public function test_package_calculates_cbm_volumetric_and_chargeable_weight(): void
    {
        $agent = Agent::create(['name' => 'Client One', 'is_active' => true]);
        $shipment = Shipment::create([
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-1',
            'mode' => 'air',
            'status' => 'draft',
            'estimated_unit_rate' => 10,
        ]);

        ShipmentPackage::create([
            'shipment_id' => $shipment->id,
            'pieces' => 2,
            'actual_weight_kg' => 20,
            'length_cm' => 50,
            'width_cm' => 40,
            'height_cm' => 30,
        ]);

        $package = ShipmentPackage::firstOrFail();
        $this->assertEquals(0.12, (float) $package->cbm);
        $this->assertEquals(24.0, (float) $package->volumetric_weight_kg);
        $this->assertEquals(24.0, (float) $package->chargeable_weight_kg);
        $this->assertEquals(240.0, (float) $shipment->fresh()->estimated_price);
    }

    public function test_admin_can_lock_final_pricing_and_issue_final_invoice_with_ledger(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'secret',
            'role' => 'super_admin',
        ]);
        $agent = Agent::create(['name' => 'Client One', 'is_active' => true]);
        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'confirmed',
            'total' => 0,
        ]);
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-2',
            'mode' => 'ddp',
            'status' => 'delivered',
            'estimated_unit_rate' => 8,
        ]);
        ShipmentPackage::create([
            'shipment_id' => $shipment->id,
            'pieces' => 1,
            'actual_weight_kg' => 30,
            'length_cm' => 50,
            'width_cm' => 50,
            'height_cm' => 50,
        ]);

        $this->actingAs($user)
            ->post(route('admin.shipments.pricing.lock', $shipment), [
                'final_unit_rate' => 12,
            ])
            ->assertRedirect();

        $shipment = $shipment->fresh();
        $this->assertTrue((bool) $shipment->pricing_locked);
        $this->assertEquals(360.0, (float) $shipment->final_price);

        $this->actingAs($user)
            ->post(route('admin.shipments.invoice', $shipment), [
                'invoice_type' => 'final',
            ])
            ->assertRedirect();

        $invoice = Invoice::where('shipment_id', $shipment->id)->firstOrFail();
        $this->assertEquals('final', $invoice->invoice_type);
        $this->assertEquals(360.0, (float) $invoice->net_total);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Accounts Receivable',
            'debit' => 360,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Freight Revenue',
            'debit' => 0,
            'credit' => 360,
        ]);
        $this->assertEquals('final_invoiced', $shipment->fresh()->status);
        $this->assertGreaterThanOrEqual(2, AuditLog::count());
    }

    public function test_foreign_currency_shipment_invoice_preserves_source_amount_and_posts_base_ledger(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-currency@example.test',
            'password' => 'secret',
            'role' => 'super_admin',
        ]);
        $agent = Agent::create(['name' => 'Agent One', 'is_active' => true]);
        $client = Client::create([
            'name' => 'USD Client',
            'agent_id' => $agent->id,
            'currency' => 'USD',
            'is_active' => true,
        ]);
        $order = Order::create([
            'agent_id' => $agent->id,
            'client_id' => $client->id,
            'order_type' => 'regular',
            'status' => 'confirmed',
            'total' => 0,
        ]);
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'client_id' => $client->id,
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-USD',
            'mode' => 'air',
            'status' => 'delivered',
            'estimated_unit_rate' => 8,
        ]);
        ShipmentPackage::create([
            'shipment_id' => $shipment->id,
            'pieces' => 1,
            'actual_weight_kg' => 30,
            'length_cm' => 50,
            'width_cm' => 50,
            'height_cm' => 50,
        ]);

        $this->actingAs($user)
            ->post(route('admin.shipments.pricing.lock', $shipment), [
                'final_unit_rate' => 12,
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('admin.shipments.invoice', $shipment), [
                'invoice_type' => 'final',
                'exchange_rate' => 110,
            ])
            ->assertRedirect();

        $invoice = Invoice::where('shipment_id', $shipment->id)->firstOrFail();
        $this->assertEquals('USD', $invoice->currency_code);
        $this->assertEquals(110.0, (float) $invoice->exchange_rate);
        $this->assertEquals(360.0, (float) $invoice->net_total);

        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Accounts Receivable',
            'debit' => 39600,
            'credit' => 0,
            'currency_code' => 'BDT',
            'source_currency_code' => 'USD',
            'source_debit' => 360,
            'source_credit' => 0,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Freight Revenue',
            'debit' => 0,
            'credit' => 39600,
            'currency_code' => 'BDT',
            'source_currency_code' => 'USD',
            'source_debit' => 0,
            'source_credit' => 360,
        ]);
    }

    public function test_configured_exchange_rate_is_used_for_foreign_currency_shipment_invoice(): void
    {
        SystemSettings::putMany([
            'currency_code' => 'BDT',
            'exchange_rate_USD_BDT' => 112.5,
        ]);

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-configured-currency@example.test',
            'password' => 'secret',
            'role' => 'super_admin',
        ]);
        $agent = Agent::create(['name' => 'Agent One', 'is_active' => true]);
        $client = Client::create([
            'name' => 'USD Client',
            'agent_id' => $agent->id,
            'currency' => 'USD',
            'is_active' => true,
        ]);
        $order = Order::create([
            'agent_id' => $agent->id,
            'client_id' => $client->id,
            'order_type' => 'regular',
            'status' => 'confirmed',
            'total' => 0,
        ]);
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'client_id' => $client->id,
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-USD-SET',
            'mode' => 'air',
            'status' => 'delivered',
            'estimated_unit_rate' => 8,
        ]);
        ShipmentPackage::create([
            'shipment_id' => $shipment->id,
            'pieces' => 1,
            'actual_weight_kg' => 30,
            'length_cm' => 50,
            'width_cm' => 50,
            'height_cm' => 50,
        ]);

        $this->actingAs($user)
            ->post(route('admin.shipments.pricing.lock', $shipment), [
                'final_unit_rate' => 12,
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('admin.shipments.invoice', $shipment), [
                'invoice_type' => 'final',
            ])
            ->assertRedirect();

        $invoice = Invoice::where('shipment_id', $shipment->id)->firstOrFail();
        $this->assertEquals('USD', $invoice->currency_code);
        $this->assertEquals(112.5, (float) $invoice->exchange_rate);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Accounts Receivable',
            'debit' => 40500,
            'source_debit' => 360,
            'source_currency_code' => 'USD',
        ]);
    }

    public function test_shipment_status_change_notifies_operations_staff(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-status@example.test',
            'password' => 'secret',
            'role' => 'super_admin',
        ]);
        $staff = User::create([
            'name' => 'Ops',
            'email' => 'ops-status@example.test',
            'password' => 'secret',
            'role' => 'delivery_coordinator',
        ]);
        $agent = Agent::create(['name' => 'Client One', 'is_active' => true]);
        $shipment = Shipment::create([
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-3',
            'mode' => 'air',
            'status' => 'draft',
            'estimated_unit_rate' => 10,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.shipments.status.update', $shipment), [
                'status' => 'confirmed',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'type' => SystemAlertNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $staff->id,
        ]);
    }

    public function test_admin_can_upload_shipment_pod_document(): void
    {
        Storage::fake('public');

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-pod@example.test',
            'password' => 'secret',
            'role' => 'super_admin',
        ]);
        $agent = Agent::create(['name' => 'Client One', 'is_active' => true]);
        $shipment = Shipment::create([
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-POD',
            'mode' => 'courier',
            'status' => 'delivered',
            'estimated_unit_rate' => 10,
        ]);

        $this->actingAs($user)
            ->post('/admin/shipments/' . $shipment->id . '/pod', [
                'document' => UploadedFile::fake()->image('pod.jpg'),
                'received_by' => 'Warehouse Receiver',
                'receiver_phone' => '0123456789',
                'notes' => 'Delivered in good condition',
                'delivered_at' => '2026-04-15 10:30:00',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('shipment_pods', [
            'shipment_id' => $shipment->id,
            'received_by' => 'Warehouse Receiver',
            'receiver_phone' => '0123456789',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'shipment.pod_saved',
        ]);
    }

    public function test_shipment_status_workflow_blocks_skips_and_requires_pod_for_delivery(): void
    {
        Storage::fake('public');

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-workflow@example.test',
            'password' => 'secret',
            'role' => 'super_admin',
        ]);
        $agent = Agent::create(['name' => 'Client One', 'is_active' => true]);
        $shipment = Shipment::create([
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-FLOW',
            'mode' => 'air',
            'status' => 'draft',
            'estimated_unit_rate' => 10,
        ]);

        $this->actingAs($user)
            ->from(route('admin.shipments.show', $shipment))
            ->patch(route('admin.shipments.status.update', $shipment), [
                'status' => 'in_transit',
            ])
            ->assertSessionHasErrors('status');
        $this->assertEquals('draft', $shipment->fresh()->status);

        foreach (['confirmed', 'received_at_origin', 'in_transit', 'customs_clearance', 'out_for_delivery'] as $status) {
            $this->actingAs($user)
                ->patch(route('admin.shipments.status.update', $shipment), ['status' => $status])
                ->assertRedirect();
            $this->assertEquals($status, $shipment->fresh()->status);
        }

        $this->actingAs($user)
            ->from(route('admin.shipments.show', $shipment))
            ->patch(route('admin.shipments.status.update', $shipment), [
                'status' => 'delivered',
            ])
            ->assertSessionHasErrors('status');
        $this->assertEquals('out_for_delivery', $shipment->fresh()->status);

        $this->actingAs($user)
            ->post(route('admin.shipments.pod.store', $shipment), [
                'document' => UploadedFile::fake()->image('pod.jpg'),
                'received_by' => 'Receiver',
                'delivered_at' => now()->toDateTimeString(),
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->patch(route('admin.shipments.status.update', $shipment), [
                'status' => 'delivered',
            ])
            ->assertRedirect();
        $this->assertEquals('delivered', $shipment->fresh()->status);
    }

    public function test_approved_shipment_expense_posts_landed_cost_ledger_once(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-expense-ledger@example.test',
            'password' => 'secret',
            'role' => 'super_admin',
        ]);
        $agent = Agent::create(['name' => 'Agent One', 'is_active' => true]);
        $client = Client::create([
            'name' => 'Client One',
            'agent_id' => $agent->id,
            'is_active' => true,
        ]);
        $order = Order::create([
            'agent_id' => $agent->id,
            'client_id' => $client->id,
            'order_type' => 'regular',
            'status' => 'confirmed',
            'total' => 0,
        ]);
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'client_id' => $client->id,
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-COST',
            'mode' => 'ddp',
            'status' => 'draft',
            'estimated_unit_rate' => 10,
        ]);
        $expense = ShipmentExpense::create([
            'shipment_id' => $shipment->id,
            'expense_type' => 'customs_duty',
            'estimated_amount' => 120,
            'actual_amount' => 150,
            'status' => 'submitted',
        ]);

        $this->actingAs($user)
            ->post(route('admin.shipments.expenses.approve', [$shipment, $expense]))
            ->assertRedirect();

        $this->assertDatabaseHas('ledger_entries', [
            'shipment_expense_id' => $expense->id,
            'shipment_id' => $shipment->id,
            'client_id' => $client->id,
            'account' => 'Customs & Duty Expense',
            'debit' => 150,
            'credit' => 0,
        ]);
        $this->assertDatabaseHas('ledger_entries', [
            'shipment_expense_id' => $expense->id,
            'shipment_id' => $shipment->id,
            'client_id' => $client->id,
            'account' => 'Accrued Logistics Payable',
            'debit' => 0,
            'credit' => 150,
        ]);

        $this->actingAs($user)
            ->post(route('admin.shipments.expenses.approve', [$shipment, $expense]))
            ->assertRedirect();

        $this->assertEquals(2, LedgerEntry::where('shipment_expense_id', $expense->id)->count());
    }

    public function test_shipment_uses_best_matching_rate_card_when_manual_rate_is_blank(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-rate-card@example.test',
            'password' => 'secret',
            'role' => 'super_admin',
        ]);
        $agent = Agent::create(['name' => 'Agent One', 'is_active' => true]);
        $client = Client::create([
            'name' => 'Client One',
            'agent_id' => $agent->id,
            'is_active' => true,
        ]);

        ShipmentRateCard::create([
            'mode' => 'sea_lcl',
            'origin_country' => 'China',
            'destination_country' => 'Bangladesh',
            'billing_unit' => 'cbm',
            'rate' => 100,
            'minimum_charge' => 300,
            'volumetric_divisor' => 6000,
            'is_active' => true,
        ]);

        $clientRateCard = ShipmentRateCard::create([
            'client_id' => $client->id,
            'mode' => 'sea_lcl',
            'origin_country' => 'China',
            'destination_country' => 'Bangladesh',
            'billing_unit' => 'cbm',
            'rate' => 150,
            'minimum_charge' => 500,
            'volumetric_divisor' => 6000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('admin.shipments.store'), [
                'client_id' => $client->id,
                'mode' => 'sea_lcl',
                'origin_country' => 'China',
                'destination_country' => 'Bangladesh',
                'estimated_unit_rate' => '',
            ]);

        $response->assertRedirect();
        $shipment = Shipment::where('shipment_no', 'like', 'SHP-%')->latest('id')->firstOrFail();
        $this->assertEquals($agent->id, $shipment->agent_id);
        $this->assertEquals($clientRateCard->id, $shipment->estimated_rate_card_id);
        $this->assertEquals('cbm', $shipment->pricing_basis);
        $this->assertEquals(6000, $shipment->volumetric_divisor);
        $this->assertEquals(150.0, (float) $shipment->estimated_unit_rate);

        ShipmentPackage::create([
            'shipment_id' => $shipment->id,
            'pieces' => 1,
            'actual_weight_kg' => 20,
            'length_cm' => 100,
            'width_cm' => 100,
            'height_cm' => 100,
        ]);

        $shipment = $shipment->fresh();
        $package = $shipment->packages()->firstOrFail();
        $this->assertEquals(166.667, (float) $package->volumetric_weight_kg);
        $this->assertEquals(500.0, (float) $shipment->estimated_price);
    }

    public function test_logistics_report_can_export_excel_compatible_csv(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-report@example.test',
            'password' => 'secret',
            'role' => 'super_admin',
        ]);
        $agent = Agent::create(['name' => 'Client One', 'is_active' => true]);
        $shipment = Shipment::create([
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-EXPORT',
            'mode' => 'air',
            'status' => 'delivered',
            'estimated_unit_rate' => 8,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        ShipmentPackage::create([
            'shipment_id' => $shipment->id,
            'pieces' => 1,
            'actual_weight_kg' => 10,
            'length_cm' => 20,
            'width_cm' => 20,
            'height_cm' => 20,
        ]);

        $response = $this->actingAs($user)->get('/admin/reports/shipments/weight/export/excel?from='
            . now()->startOfMonth()->format('Y-m-d')
            . '&to='
            . now()->endOfMonth()->format('Y-m-d'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('SHP-EXPORT', $response->streamedContent());
    }

    public function test_logistics_profitability_export_uses_approved_costs_and_date_range(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-profitability-report@example.test',
            'password' => 'secret',
            'role' => 'super_admin',
        ]);
        $agent = Agent::create(['name' => 'Client One', 'is_active' => true]);

        $inRangeShipment = Shipment::create([
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-PROFIT-IN',
            'mode' => 'air',
            'status' => 'delivered',
            'estimated_unit_rate' => 5,
        ]);
        DB::table('shipments')->where('id', $inRangeShipment->id)->update([
            'created_at' => '2026-04-10 10:00:00',
            'updated_at' => '2026-04-10 10:00:00',
        ]);
        ShipmentPackage::create([
            'shipment_id' => $inRangeShipment->id,
            'pieces' => 1,
            'actual_weight_kg' => 10,
            'length_cm' => 10,
            'width_cm' => 10,
            'height_cm' => 10,
        ]);
        ShipmentExpense::create([
            'shipment_id' => $inRangeShipment->id,
            'expense_type' => 'handling',
            'estimated_amount' => 12,
            'actual_amount' => 15,
            'status' => 'approved',
        ]);
        ShipmentExpense::create([
            'shipment_id' => $inRangeShipment->id,
            'expense_type' => 'draft-fee',
            'estimated_amount' => 99,
            'status' => 'draft',
        ]);

        $outOfRangeShipment = Shipment::create([
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-PROFIT-OUT',
            'mode' => 'sea_lcl',
            'status' => 'delivered',
            'estimated_unit_rate' => 7,
        ]);
        DB::table('shipments')->where('id', $outOfRangeShipment->id)->update([
            'created_at' => '2026-03-25 10:00:00',
            'updated_at' => '2026-03-25 10:00:00',
        ]);
        ShipmentPackage::create([
            'shipment_id' => $outOfRangeShipment->id,
            'pieces' => 1,
            'actual_weight_kg' => 8,
            'length_cm' => 10,
            'width_cm' => 10,
            'height_cm' => 10,
        ]);
        ShipmentExpense::create([
            'shipment_id' => $outOfRangeShipment->id,
            'expense_type' => 'freight',
            'estimated_amount' => 20,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->get('/admin/reports/shipments/profitability/export/excel?from=2026-04-01&to=2026-04-30');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('SHP-PROFIT-IN', $csv);
        $this->assertStringContainsString('50.00,15.00,35.00', $csv);
        $this->assertStringNotContainsString('SHP-PROFIT-OUT', $csv);
        $this->assertStringNotContainsString('99.00', $csv);
    }

    public function test_logistics_mode_export_aggregates_revenue_cost_and_weight_by_mode(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-mode-report@example.test',
            'password' => 'secret',
            'role' => 'super_admin',
        ]);
        $agent = Agent::create(['name' => 'Client One', 'is_active' => true]);

        $airShipmentA = Shipment::create([
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-AIR-1',
            'mode' => 'air',
            'status' => 'delivered',
            'estimated_unit_rate' => 10,
        ]);
        DB::table('shipments')->where('id', $airShipmentA->id)->update([
            'created_at' => '2026-04-11 09:00:00',
            'updated_at' => '2026-04-11 09:00:00',
        ]);
        ShipmentPackage::create([
            'shipment_id' => $airShipmentA->id,
            'pieces' => 1,
            'actual_weight_kg' => 10,
            'length_cm' => 10,
            'width_cm' => 10,
            'height_cm' => 10,
        ]);
        ShipmentExpense::create([
            'shipment_id' => $airShipmentA->id,
            'expense_type' => 'air-fee',
            'estimated_amount' => 20,
            'status' => 'approved',
        ]);

        $airShipmentB = Shipment::create([
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-AIR-2',
            'mode' => 'air',
            'status' => 'delivered',
            'estimated_unit_rate' => 8,
        ]);
        DB::table('shipments')->where('id', $airShipmentB->id)->update([
            'created_at' => '2026-04-12 09:00:00',
            'updated_at' => '2026-04-12 09:00:00',
        ]);
        ShipmentPackage::create([
            'shipment_id' => $airShipmentB->id,
            'pieces' => 1,
            'actual_weight_kg' => 5,
            'length_cm' => 10,
            'width_cm' => 10,
            'height_cm' => 10,
        ]);
        ShipmentExpense::create([
            'shipment_id' => $airShipmentB->id,
            'expense_type' => 'air-fee',
            'estimated_amount' => 5,
            'status' => 'approved',
        ]);

        $seaShipment = Shipment::create([
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-SEA-1',
            'mode' => 'sea_lcl',
            'status' => 'delivered',
            'estimated_unit_rate' => 6,
        ]);
        DB::table('shipments')->where('id', $seaShipment->id)->update([
            'created_at' => '2026-04-13 09:00:00',
            'updated_at' => '2026-04-13 09:00:00',
        ]);
        ShipmentPackage::create([
            'shipment_id' => $seaShipment->id,
            'pieces' => 1,
            'actual_weight_kg' => 20,
            'length_cm' => 10,
            'width_cm' => 10,
            'height_cm' => 10,
        ]);
        ShipmentExpense::create([
            'shipment_id' => $seaShipment->id,
            'expense_type' => 'sea-fee',
            'estimated_amount' => 25,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->get('/admin/reports/shipments/mode/export/excel?from=2026-04-01&to=2026-04-30');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Mode,Shipments,"Chargeable KG",Revenue,Cost,Profit', $csv);
        $this->assertStringContainsString("AIR,2,15.000,140.00,25.00,115.00", $csv);
        $this->assertStringContainsString("\"SEA LCL\",1,20.000,120.00,25.00,95.00", $csv);
    }

    public function test_settlement_becomes_payable_after_client_payment_is_recorded(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin-commission@example.test',
            'password' => 'secret',
            'role' => 'super_admin',
        ]);
        $agent = Agent::create(['name' => 'Client One', 'is_active' => true]);
        $order = Order::create([
            'agent_id' => $agent->id,
            'order_type' => 'regular',
            'status' => 'delivered',
            'total' => 500,
        ]);
        $invoice = Invoice::create([
            'order_id' => $order->id,
            'number' => 'INV-SETTLE',
            'invoice_type' => 'final',
            'issued_at' => now()->startOfMonth(),
            'due_at' => now()->startOfMonth()->addDays(7),
            'net_total' => 500,
            'vat_amount' => 0,
            'withholding' => 0,
            'status' => 'issued',
        ]);
        $settlement = AgentCommissionSettlement::create([
            'agent_id' => $agent->id,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'sales_total' => 500,
            'commission_total' => 50,
            'status' => 'open',
        ]);

        $this->actingAs($user)
            ->post(route('admin.finance.receipts.store', $invoice), [
                'amount' => 500,
                'payment_method' => 'bank_transfer',
                'received_at' => now()->toDateString(),
            ])
            ->assertRedirect();

        $this->assertEquals('paid', $invoice->fresh()->status);
        $this->assertEquals('approved', $settlement->fresh()->status);
        $this->assertDatabaseHas('ledger_entries', [
            'invoice_id' => $invoice->id,
            'account' => 'Accounts Receivable',
            'credit' => 500,
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
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->string('currency', 10)->default('BDT');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('admin');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('order_type')->default('regular');
            $table->string('status')->default('draft');
            $table->decimal('total', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('shipment_no')->unique();
            $table->string('mode');
            $table->string('status')->default('draft');
            $table->string('origin_country')->default('China');
            $table->string('destination_country')->default('Bangladesh');
            $table->unsignedBigInteger('origin_warehouse_id')->nullable();
            $table->unsignedBigInteger('destination_warehouse_id')->nullable();
            $table->date('estimated_departure')->nullable();
            $table->date('estimated_arrival')->nullable();
            $table->decimal('estimated_unit_rate', 14, 2)->default(0);
            $table->unsignedBigInteger('estimated_rate_card_id')->nullable();
            $table->string('pricing_basis')->default('chargeable_kg');
            $table->unsignedInteger('volumetric_divisor')->default(5000);
            $table->decimal('final_unit_rate', 14, 2)->nullable();
            $table->decimal('estimated_price', 14, 2)->default(0);
            $table->decimal('final_price', 14, 2)->nullable();
            $table->boolean('pricing_locked')->default(false);
            $table->timestamp('pricing_locked_at')->nullable();
            $table->unsignedBigInteger('pricing_locked_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('shipment_rate_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->string('mode');
            $table->string('origin_country')->nullable();
            $table->string('destination_country')->nullable();
            $table->string('billing_unit')->default('chargeable_kg');
            $table->decimal('rate', 14, 2);
            $table->decimal('minimum_charge', 14, 2)->default(0);
            $table->unsignedInteger('volumetric_divisor')->default(5000);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('shipment_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->string('description')->nullable();
            $table->integer('pieces')->default(1);
            $table->decimal('actual_weight_kg', 12, 3)->default(0);
            $table->decimal('length_cm', 12, 2)->default(0);
            $table->decimal('width_cm', 12, 2)->default(0);
            $table->decimal('height_cm', 12, 2)->default(0);
            $table->decimal('cbm', 12, 4)->default(0);
            $table->decimal('volumetric_weight_kg', 12, 3)->default(0);
            $table->decimal('chargeable_weight_kg', 12, 3)->default(0);
            $table->timestamps();
        });

        Schema::create('shipment_legs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->integer('sequence')->default(1);
            $table->string('leg_type');
            $table->string('status')->default('pending');
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('shipment_expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->unsignedBigInteger('shipment_leg_id')->nullable();
            $table->string('expense_type');
            $table->decimal('estimated_amount', 14, 2)->default(0);
            $table->decimal('actual_amount', 14, 2)->nullable();
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('shipment_pods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id')->unique();
            $table->string('document_path')->nullable();
            $table->string('received_by')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('shipment_id')->nullable();
            $table->string('number')->unique();
            $table->string('invoice_type')->default('standard');
            $table->string('currency_code', 10)->default('BDT');
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->date('issued_at');
            $table->date('due_at')->nullable();
            $table->decimal('net_total', 14, 2);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('withholding', 14, 2)->default(0);
            $table->string('status')->default('draft');
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('amount', 14, 2);
            $table->string('currency_code', 10)->default('BDT');
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->string('payment_method')->nullable();
            $table->date('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('number')->nullable();
            $table->date('issued_at')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('currency_code', 10)->default('BDT');
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_advance_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_advance_id')->nullable();
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('amount', 14, 2)->default(0);
            $table->date('applied_at')->nullable();
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

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->string('account');
            $table->text('description')->nullable();
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->string('currency_code', 10)->default('BDT');
            $table->string('source_currency_code', 10)->nullable();
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->decimal('source_debit', 14, 2)->default(0);
            $table->decimal('source_credit', 14, 2)->default(0);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('shipment_id')->nullable();
            $table->unsignedBigInteger('shipment_expense_id')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }
}
