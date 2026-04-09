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
        Schema::create('employee_allowances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('type'); // e.g. TA, DA, BONUS, OTHER
            $table->string('reference')->nullable();
            $table->decimal('amount', 12, 2);
            $table->text('description')->nullable();
            $table->string('status')->default('submitted'); // submitted, approved, rejected
            $table->string('attachment_path')->nullable(); // uploaded slip
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_allowances');
    }
};
