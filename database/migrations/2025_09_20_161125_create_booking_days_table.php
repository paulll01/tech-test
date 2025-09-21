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
        Schema::create('booking_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings', 'id')->cascadeOnDelete();
            $table->foreignId('car_park_id')->constrained('car_parks', 'id')->cascadeOnDelete();
            $table->date('date');
            $table->timestamps();
            $table->index(['car_park_id', 'date']);
            $table->unique(['booking_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_days');
    }
};
