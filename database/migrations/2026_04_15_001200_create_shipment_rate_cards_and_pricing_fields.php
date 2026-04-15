<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_rate_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('agents')->nullOnDelete();
            $table->enum('mode', ['courier', 'air', 'sea_lcl', 'sea_fcl', 'ddp']);
            $table->string('origin_country')->nullable();
            $table->string('destination_country')->nullable();
            $table->enum('billing_unit', ['chargeable_kg', 'cbm', 'shipment'])->default('chargeable_kg');
            $table->decimal('rate', 14, 2);
            $table->decimal('minimum_charge', 14, 2)->default(0);
            $table->unsignedInteger('volumetric_divisor')->default(5000);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['mode', 'is_active']);
            $table->index(['client_id', 'agent_id']);
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('estimated_rate_card_id')->nullable()->after('estimated_unit_rate')->constrained('shipment_rate_cards')->nullOnDelete();
            $table->enum('pricing_basis', ['chargeable_kg', 'cbm', 'shipment'])->default('chargeable_kg')->after('estimated_rate_card_id');
            $table->unsignedInteger('volumetric_divisor')->default(5000)->after('pricing_basis');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('estimated_rate_card_id');
            $table->dropColumn(['pricing_basis', 'volumetric_divisor']);
        });

        Schema::dropIfExists('shipment_rate_cards');
    }
};
