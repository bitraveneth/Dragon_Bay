<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->integer('year');
            $table->string('type', 50); // annual, sick, casual, etc.
            $table->integer('entitled_days');
            $table->timestamps();

            $table->unique(['employee_id', 'year', 'type'], 'leave_balance_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leave_balances');
    }
};

