<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('agent_id')->constrained('agents');
            $table->string('shipment_no')->unique();
            $table->enum('mode', ['courier', 'air', 'sea_lcl', 'sea_fcl', 'ddp']);
            $table->enum('status', [
                'draft',
                'confirmed',
                'received_at_origin',
                'in_transit',
                'customs_clearance',
                'out_for_delivery',
                'delivered',
                'final_invoiced',
                'closed',
            ])->default('draft');
            $table->string('origin_country')->default('China');
            $table->string('destination_country')->default('Bangladesh');
            $table->foreignId('origin_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->foreignId('destination_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->date('estimated_departure')->nullable();
            $table->date('estimated_arrival')->nullable();
            $table->decimal('estimated_unit_rate', 14, 2)->default(0);
            $table->decimal('final_unit_rate', 14, 2)->nullable();
            $table->decimal('estimated_price', 14, 2)->default(0);
            $table->decimal('final_price', 14, 2)->nullable();
            $table->boolean('pricing_locked')->default(false);
            $table->timestamp('pricing_locked_at')->nullable();
            $table->foreignId('pricing_locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('shipment_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
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
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sequence')->default(1);
            $table->enum('leg_type', ['origin', 'international', 'customs', 'last_mile']);
            $table->enum('status', ['pending', 'received', 'in_transit', 'customs', 'out_for_delivery', 'delivered', 'exception'])->default('pending');
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['shipment_id', 'sequence']);
        });

        Schema::create('shipment_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipment_leg_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('expense_type', ['air_freight', 'sea_freight', 'customs_duty', 'port_warehouse', 'last_mile', 'other']);
            $table->decimal('estimated_amount', 14, 2)->default(0);
            $table->decimal('actual_amount', 14, 2)->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_expenses');
        Schema::dropIfExists('shipment_legs');
        Schema::dropIfExists('shipment_packages');
        Schema::dropIfExists('shipments');
    }
};
