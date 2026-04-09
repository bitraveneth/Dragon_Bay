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
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('reference');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('working_schedule')->nullable();
            $table->decimal('salary_amount', 12, 2)->nullable();
            $table->decimal('travel_allowance', 12, 2)->nullable();
            $table->decimal('dearness_allowance', 12, 2)->nullable();
            $table->decimal('bonus', 12, 2)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};
