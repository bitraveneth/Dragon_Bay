<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('route_id')->nullable()->constrained('delivery_routes');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles');
            $table->enum('status', ['scheduled', 'in_transit', 'delivered', 'exception'])->default('scheduled');
            $table->string('pod_photo')->nullable();
            $table->text('exception_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
