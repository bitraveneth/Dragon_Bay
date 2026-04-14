<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Agent;
use App\Models\Client;
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
            ->assertSee('Overview')
            ->assertSee('Client One');
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
    }
}
