<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_price_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->decimal('price', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['agent_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_price_lists');
    }
};
