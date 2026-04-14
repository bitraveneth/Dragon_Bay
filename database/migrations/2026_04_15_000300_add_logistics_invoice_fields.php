<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('shipment_id')->nullable()->after('order_id')->constrained('shipments')->nullOnDelete();
            $table->enum('invoice_type', ['standard', 'proforma', 'final'])->default('standard')->after('number');
            $table->timestamp('locked_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipment_id');
            $table->dropColumn(['invoice_type', 'locked_at']);
        });
    }
};
