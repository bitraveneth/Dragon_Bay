<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained();
            $table->decimal('amount', 14, 2);
            $table->decimal('applied_amount', 14, 2)->default(0);
            $table->date('advanced_at');
            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'partial', 'applied'])->default('open');
            $table->timestamps();
        });

        Schema::create('agent_advance_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_advance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('applied_at');
            $table->timestamps();
        });

        Schema::table('agent_commission_rules', function (Blueprint $table) {
            $table->decimal('threshold_min', 14, 2)->nullable()->after('frequency');
            $table->decimal('threshold_max', 14, 2)->nullable()->after('threshold_min');
        });

        Schema::table('purchase_bill_items', function (Blueprint $table) {
            $table->decimal('vat_rate', 5, 2)->default(0)->after('unit_price');
            $table->decimal('vat_amount', 14, 2)->default(0)->after('line_total');
        });

        Schema::table('agent_commission_settlements', function (Blueprint $table) {
            $table->timestamp('accrued_at')->nullable()->after('status');
            $table->date('paid_at')->nullable()->after('accrued_at');
            $table->string('payment_method')->nullable()->after('paid_at');
            $table->string('payment_reference')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('agent_commission_settlements', function (Blueprint $table) {
            $table->dropColumn(['accrued_at', 'paid_at', 'payment_method', 'payment_reference']);
        });

        Schema::table('purchase_bill_items', function (Blueprint $table) {
            $table->dropColumn(['vat_rate', 'vat_amount']);
        });

        Schema::table('agent_commission_rules', function (Blueprint $table) {
            $table->dropColumn(['threshold_min', 'threshold_max']);
        });

        Schema::dropIfExists('agent_advance_applications');
        Schema::dropIfExists('agent_advances');
    }
};
