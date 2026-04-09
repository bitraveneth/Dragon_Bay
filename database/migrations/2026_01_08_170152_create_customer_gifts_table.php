<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->string('occasion')->nullable(); // birthday, yearly, festival, bonus
            $table->string('gift_type')->nullable(); // fridge, banner, gift item, etc.
            $table->string('description')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('campaign_code')->nullable();
            $table->string('status')->default('given'); // planned, given
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_gifts');
    }
};
