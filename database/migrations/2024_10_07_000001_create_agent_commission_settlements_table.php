<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_commission_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('sales_total', 14, 2);
            $table->decimal('commission_total', 14, 2);
            $table->enum('status', ['open', 'approved', 'paid'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_commission_settlements');
    }
};

