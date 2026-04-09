<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packaging_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_packaging_type_id')->constrained('packaging_types');
            $table->foreignId('to_packaging_type_id')->constrained('packaging_types');
            $table->decimal('factor', 12, 6)->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['from_packaging_type_id', 'to_packaging_type_id'], 'packaging_conversion_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packaging_conversions');
    }
};
