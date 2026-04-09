<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If a prior failed run created this table partially, recreate cleanly.
        Schema::dropIfExists('production_material_issue_items');

        Schema::create('production_material_issue_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_material_issue_id');
            $table->foreignId('component_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantity', 12, 4)->default(0);
            $table->decimal('unit_cost', 12, 4)->nullable();
            $table->decimal('line_total', 14, 2)->nullable();
            $table->timestamps();

            // Keep FK names short to satisfy MySQL 64-char identifier limit.
            $table->foreign('production_material_issue_id', 'pmii_issue_fk')
                ->references('id')
                ->on('production_material_issues')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_material_issue_items');
    }
};
