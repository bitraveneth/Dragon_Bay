<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_commission_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained();
            $table->string('sku')->nullable();
            $table->enum('type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('value', 10, 2)->default(0);
            $table->string('order_type')->nullable();
            $table->enum('frequency', ['per_order', 'monthly'])->default('per_order');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_commission_rules');
    }
};
