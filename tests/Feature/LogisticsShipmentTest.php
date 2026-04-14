<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\AgentCommissionSettlement;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\Shipment;
use App\Models\ShipmentPackage;
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
            $table->string('order_type')->default('regular');
            $table->string('status')->default('draft');
            $table->decimal('total', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('agent_id');
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
            $table->decimal('final_unit_rate', 14, 2)->nullable();
            $table->decimal('estimated_price', 14, 2)->default(0);
            $table->decimal('final_price', 14, 2)->nullable();
            $table->boolean('pricing_locked')->default(false);
            $table->timestamp('pricing_locked_at')->nullable();
            $table->unsignedBigInteger('pricing_locked_by')->nullable();
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
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
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
