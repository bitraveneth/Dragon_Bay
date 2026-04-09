<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained();
            $table->enum('order_type', ['regular', 'bulk', 'sample', 'return'])->default('regular');
            $table->date('delivery_date')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'packed', 'dispatched', 'delivered'])->default('draft');
            $table->decimal('total', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_credit_used')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
