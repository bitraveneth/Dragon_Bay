<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Agent;
use App\Models\Client;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Receipt;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClientPortalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('Client portal tests require pdo_sqlite.');
        }

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('database.connections.sqlite.foreign_key_constraints', false);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->createSchema();
    }

    public function test_client_user_is_redirected_to_portal_after_login(): void
    {
        $client = Client::create(['name' => 'Client One', 'is_active' => true]);
        User::create([
            'name' => 'Client User',
            'email' => 'client@example.test',
            'password' => 'secret',
            'role' => 'client',
            'client_id' => $client->id,
        ]);

        $this->post(route('login'), [
            'email' => 'client@example.test',
            'password' => 'secret',
        ])->assertRedirect(route('portal.dashboard'));
    }

    public function test_client_user_can_access_portal_dashboard(): void
    {
        $client = Client::create(['name' => 'Client One', 'is_active' => true]);
        $user = User::create([
            'name' => 'Client User',
            'email' => 'client-dashboard@example.test',
            'password' => 'secret',
            'role' => 'client',
            'client_id' => $client->id,
        ]);

        $this->actingAs($user)
            ->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('Overview');
    }

    public function test_non_client_user_cannot_access_portal(): void
    {
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.test',
            'password' => 'secret',
            'role' => 'admin',
        ]);

        $this->actingAs($user)
            ->get(route('portal.dashboard'))
            ->assertForbidden();
    }

    public function test_client_user_only_sees_their_own_invoices_orders_and_shipments(): void
    {
        $agent = Agent::create(['name' => 'Portal Agent', 'is_active' => true]);

        $clientA = Client::create(['name' => 'Client A', 'currency' => 'USD', 'agent_id' => $agent->id, 'is_active' => true]);
        $clientB = Client::create(['name' => 'Client B', 'currency' => 'BDT', 'agent_id' => $agent->id, 'is_active' => true]);

        $userA = User::create([
            'name' => 'Client A User',
            'email' => 'client-a@example.test',
            'password' => 'secret',
            'role' => 'client',
            'client_id' => $clientA->id,
        ]);

        $orderA = Order::create([
            'agent_id' => $agent->id,
            'client_id' => $clientA->id,
            'order_type' => 'regular',
            'status' => 'confirmed',
            'total' => 500,
        ]);
        $orderB = Order::create([
            'agent_id' => $agent->id,
            'client_id' => $clientB->id,
            'order_type' => 'regular',
            'status' => 'confirmed',
            'total' => 900,
        ]);

        $shipmentA = Shipment::create([
            'order_id' => $orderA->id,
            'client_id' => $clientA->id,
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-A-001',
            'mode' => 'air',
            'status' => 'in_transit',
        ]);
        $shipmentB = Shipment::create([
            'order_id' => $orderB->id,
            'client_id' => $clientB->id,
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-B-001',
            'mode' => 'sea_lcl',
            'status' => 'in_transit',
        ]);

        $invoiceA = Invoice::create([
            'order_id' => $orderA->id,
            'shipment_id' => $shipmentA->id,
            'number' => 'INV-A-001',
            'invoice_type' => 'final',
            'currency_code' => 'USD',
            'exchange_rate' => 110,
            'issued_at' => now()->toDateString(),
            'net_total' => 500,
            'status' => 'issued',
        ]);
        $invoiceB = Invoice::create([
            'order_id' => $orderB->id,
            'shipment_id' => $shipmentB->id,
            'number' => 'INV-B-001',
            'invoice_type' => 'final',
            'currency_code' => 'BDT',
            'exchange_rate' => 1,
            'issued_at' => now()->toDateString(),
            'net_total' => 900,
            'status' => 'issued',
        ]);

        $this->actingAs($userA)
            ->get(route('portal.invoices.index'))
            ->assertOk()
            ->assertSee('INV-A-001')
            ->assertDontSee('INV-B-001');

        $this->actingAs($userA)
            ->get(route('portal.orders.index'))
            ->assertOk()
            ->assertSee('Order #' . $orderA->id)
            ->assertDontSee('Order #' . $orderB->id);

        $this->actingAs($userA)
            ->get(route('portal.shipments.index'))
            ->assertOk()
            ->assertSee('SHP-A-001')
            ->assertDontSee('SHP-B-001');

        $this->actingAs($userA)
            ->get(route('portal.invoices.show', $invoiceA))
            ->assertOk()
            ->assertSee('INV-A-001');

        $this->actingAs($userA)
            ->get(route('portal.invoices.show', $invoiceB))
            ->assertNotFound();

        $this->actingAs($userA)
            ->get(route('portal.orders.show', $orderB))
            ->assertNotFound();

        $this->actingAs($userA)
            ->get(route('portal.shipments.show', $shipmentB))
            ->assertNotFound();
    }

    public function test_client_portal_invoice_and_statement_use_invoice_currency_and_client_scope(): void
    {
        $agent = Agent::create(['name' => 'Portal Agent', 'is_active' => true]);

        $clientA = Client::create(['name' => 'Client USD', 'currency' => 'USD', 'agent_id' => $agent->id, 'is_active' => true]);
        $clientB = Client::create(['name' => 'Client BDT', 'currency' => 'BDT', 'agent_id' => $agent->id, 'is_active' => true]);

        $userA = User::create([
            'name' => 'Client USD User',
            'email' => 'client-usd@example.test',
            'password' => 'secret',
            'role' => 'client',
            'client_id' => $clientA->id,
        ]);

        $orderA = Order::create([
            'agent_id' => $agent->id,
            'client_id' => $clientA->id,
            'order_type' => 'regular',
            'status' => 'confirmed',
            'total' => 1200,
        ]);
        $shipmentA = Shipment::create([
            'order_id' => $orderA->id,
            'client_id' => $clientA->id,
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-USD-001',
            'mode' => 'air',
            'status' => 'delivered',
            'final_price' => 1200,
        ]);

        $invoiceA = Invoice::create([
            'order_id' => $orderA->id,
            'shipment_id' => $shipmentA->id,
            'number' => 'INV-USD-001',
            'invoice_type' => 'final',
            'currency_code' => 'USD',
            'exchange_rate' => 110,
            'issued_at' => '2026-04-10',
            'due_at' => '2026-04-20',
            'net_total' => 1000,
            'vat_amount' => 200,
            'withholding' => 0,
            'status' => 'issued',
        ]);

        Receipt::create([
            'invoice_id' => $invoiceA->id,
            'amount' => 200,
            'currency_code' => 'USD',
            'exchange_rate' => 110,
            'received_at' => '2026-04-12',
            'payment_method' => 'bank_transfer',
        ]);

        CreditNote::create([
            'invoice_id' => $invoiceA->id,
            'order_id' => $orderA->id,
            'number' => 'CN-USD-001',
            'issued_at' => '2026-04-13',
            'amount' => 50,
            'currency_code' => 'USD',
            'exchange_rate' => 110,
            'reason' => 'Rate adjustment',
        ]);

        $orderB = Order::create([
            'agent_id' => $agent->id,
            'client_id' => $clientB->id,
            'order_type' => 'regular',
            'status' => 'confirmed',
            'total' => 400,
        ]);
        $shipmentB = Shipment::create([
            'order_id' => $orderB->id,
            'client_id' => $clientB->id,
            'agent_id' => $agent->id,
            'shipment_no' => 'SHP-BDT-001',
            'mode' => 'courier',
            'status' => 'delivered',
        ]);
        Invoice::create([
            'order_id' => $orderB->id,
            'shipment_id' => $shipmentB->id,
            'number' => 'INV-BDT-001',
            'invoice_type' => 'final',
            'currency_code' => 'BDT',
            'exchange_rate' => 1,
            'issued_at' => '2026-04-11',
            'net_total' => 400,
            'status' => 'issued',
        ]);

        $this->actingAs($userA)
            ->get(route('portal.invoices.show', $invoiceA))
            ->assertOk()
            ->assertSee('USD 1,200.00')
            ->assertSee('USD 200.00')
            ->assertSee('USD 50.00')
            ->assertSee('USD 950.00')
            ->assertDontSee('BDT 1,200.00');

        $this->actingAs($userA)
            ->get(route('portal.statement', [
                'from' => '2026-04-01',
                'to' => '2026-04-30',
            ]))
            ->assertOk()
            ->assertSee('INV-USD-001')
            ->assertSee('CN-USD-001')
            ->assertSee('USD 1,200.00')
            ->assertSee('USD -200.00')
            ->assertSee('USD -50.00')
            ->assertSee('USD 950.00')
            ->assertDontSee('INV-BDT-001');
    }

    protected function createSchema(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('area')->nullable();
            $table->string('zone')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('currency')->default('BDT');
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('withholding_rate', 5, 2)->default(0);
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('order_type')->default('regular');
            $table->date('delivery_date')->nullable();
            $table->string('status')->default('draft');
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('commission_total', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_credit_used')->default(false);
            $table->string('payment_mode')->nullable();
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('agent_id');
            $table->unsignedBigInteger('client_id')->nullable();
            $table->string('shipment_no');
            $table->string('mode');
            $table->string('status')->default('draft');
            $table->string('origin_country')->nullable();
            $table->string('destination_country')->nullable();
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

        Schema::create('shipment_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shipment_id');
            $table->decimal('actual_weight_kg', 12, 3)->default(0);
            $table->decimal('cbm', 12, 4)->default(0);
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
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
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

        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('route_id')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->string('status')->default('scheduled');
            $table->integer('sequence')->nullable();
            $table->string('pod_photo')->nullable();
            $table->text('exception_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_pods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_id')->nullable();
            $table->string('signed_by')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('shipment_id')->nullable();
            $table->string('number');
            $table->string('invoice_type')->nullable();
            $table->string('currency_code')->default('BDT');
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->date('issued_at')->nullable();
            $table->date('due_at')->nullable();
            $table->decimal('net_total', 14, 2)->default(0);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('withholding', 14, 2)->default(0);
            $table->string('status')->default('issued');
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('description')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('currency_code')->default('BDT');
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
            $table->string('currency_code')->default('BDT');
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

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->string('order_type')->nullable();
            $table->decimal('commission_rate', 14, 2)->nullable();
            $table->decimal('commission_amount', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('status');
            $table->timestamp('changed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('menu_groups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_group_id');
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
