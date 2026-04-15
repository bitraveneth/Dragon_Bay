<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('currency_code', 10)->default('BDT')->after('invoice_type');
            $table->decimal('exchange_rate', 18, 6)->default(1)->after('currency_code');
        });

        Schema::table('receipts', function (Blueprint $table) {
            $table->string('currency_code', 10)->default('BDT')->after('amount');
            $table->decimal('exchange_rate', 18, 6)->default(1)->after('currency_code');
        });

        Schema::table('credit_notes', function (Blueprint $table) {
            $table->string('currency_code', 10)->default('BDT')->after('amount');
            $table->decimal('exchange_rate', 18, 6)->default(1)->after('currency_code');
        });

        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->string('currency_code', 10)->default('BDT')->after('credit');
            $table->string('source_currency_code', 10)->nullable()->after('currency_code');
            $table->decimal('exchange_rate', 18, 6)->default(1)->after('source_currency_code');
            $table->decimal('source_debit', 14, 2)->default(0)->after('exchange_rate');
            $table->decimal('source_credit', 14, 2)->default(0)->after('source_debit');
        });
    }

    public function down(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropColumn([
                'currency_code',
                'source_currency_code',
                'exchange_rate',
                'source_debit',
                'source_credit',
            ]);
        });

        Schema::table('credit_notes', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'exchange_rate']);
        });

        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'exchange_rate']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'exchange_rate']);
        });
    }
};
