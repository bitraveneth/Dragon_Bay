<?php

namespace Tests\Feature;

use App\Models\Agent;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShipmentPackage;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
