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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('car_park_id')->constrained('car_parks', 'id')->cascadeOnDelete();
            $table->string('customer_email')->index();
            $table->string('vehicle_reg')->index();
            $table->date('from_date')->index();
            $table->date('to_date')->index();
            $table->enum('status', ['confirmed', 'cancelled', 'pending'])->default('confirmed')->index();
            $table->decimal('total_price', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
