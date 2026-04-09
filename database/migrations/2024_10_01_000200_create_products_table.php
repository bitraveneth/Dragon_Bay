<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('size')->nullable();
            $table->decimal('volume_ml', 8, 2)->nullable();
            $table->string('sku_code')->nullable();
            $table->foreignId('packaging_type_id')->nullable()->constrained();
            $table->foreignId('tax_class_id')->nullable()->constrained();
            $table->string('mineral_source')->nullable();
            $table->decimal('ph', 4, 2)->nullable();
            $table->integer('tds')->nullable();
            $table->string('certifications')->nullable();
            $table->string('barcode')->nullable();
            $table->string('qr_code')->nullable();
            $table->decimal('base_price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
