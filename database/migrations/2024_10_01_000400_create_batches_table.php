<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->string('batch_code');
            $table->date('production_date');
            $table->date('expiry_date')->nullable();
            $table->enum('qc_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'batch_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
