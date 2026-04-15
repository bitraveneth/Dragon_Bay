<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('invoice_id')->constrained('clients')->nullOnDelete();
            $table->foreignId('shipment_id')->nullable()->after('client_id')->constrained('shipments')->nullOnDelete();
            $table->foreignId('shipment_expense_id')->nullable()->after('shipment_id')->constrained('shipment_expenses')->nullOnDelete();

            $table->index(['client_id', 'created_at']);
            $table->index(['shipment_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropIndex(['client_id', 'created_at']);
            $table->dropIndex(['shipment_id', 'created_at']);
            $table->dropConstrainedForeignId('shipment_expense_id');
            $table->dropConstrainedForeignId('shipment_id');
            $table->dropConstrainedForeignId('client_id');
        });
    }
};
