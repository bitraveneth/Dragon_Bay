<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('agent_reference')->nullable()->after('order_type');
            $table->string('delivery_contact_name')->nullable()->after('delivery_date');
            $table->string('delivery_contact_phone')->nullable()->after('delivery_contact_name');
            $table->text('delivery_address')->nullable()->after('delivery_contact_phone');
            $table->enum('payment_mode', ['cash', 'credit', 'bkash', 'bank_transfer'])->nullable()->after('is_credit_used');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'agent_reference',
                'delivery_contact_name',
                'delivery_contact_phone',
                'delivery_address',
                'payment_mode',
            ]);
        });
    }
};

