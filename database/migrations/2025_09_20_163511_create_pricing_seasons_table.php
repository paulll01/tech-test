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
        Schema::create('pricing_seasons', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('weekday_price', 8, 2);
            $table->decimal('weekend_price', 8, 2);
            $table->foreignId('car_park_id')->constrained('car_parks', 'id')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['car_park_id', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_seasons');
    }
};
